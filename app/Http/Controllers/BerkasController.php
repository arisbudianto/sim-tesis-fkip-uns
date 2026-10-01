<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduh berkas unggahan tanpa bergantung pada symlink public/storage
 * (sering pecah di cPanel).
 */
class BerkasController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse|\Illuminate\Http\Response
    {
        $path = ltrim($path, '/');
        $path = str_replace('\\', '/', $path);

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if (str_contains($path, '..')) {
            abort(404);
        }

        $allowed = ['sempro/', 'semhas/', 'ujian/', 'pengajuan/', 'revisi/', 'fpt-'];
        $ok = false;
        foreach ($allowed as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $ok = true;
                break;
            }
        }
        if (!$ok) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            $alt = storage_path('app/public/'.$path);
            if (is_file($alt)) {
                $download = $request->boolean('download');
                $filename = basename($path);
                return $download
                    ? response()->download($alt, $filename)
                    : response()->file($alt);
            }
            abort(404, 'Berkas tidak ditemukan di storage.');
        }

        $download = $request->boolean('download');
        $filename = basename($path);

        return $download
            ? $disk->download($path, $filename)
            : $disk->response($path, $filename);
    }

    public static function url(?string $stored, bool $download = false): ?string
    {
        if (!$stored) {
            return null;
        }

        $path = $stored;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH) ?: $path;
            $path = $parsed;
        }

        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return route('berkas.show', ['path' => $path]) . ($download ? '?download=1' : '');
    }
}
