<?php

namespace App\Services;

use App\Models\DokumenCetak;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class DocumentGeneratorService
{
    /**
     * Generate dokumen resmi (PDF) untuk kode dokumen & id record sumber
     * tertentu, sekaligus:
     *  1. Mencatat log cetak ke tabel dokumen_cetaks (jejak audit)
     *  2. Membuat QR code TTE yang mengarah ke halaman publik /verifikasi/{hash}
     *  3. Menyuntikkan QR + nomor dokumen ke view sebelum dirender jadi PDF
     */
    public function generate(string $kodeDokumen, string $recordId, ?User $dicetakOleh = null): Response
    {
        $config = config("dokumen.{$kodeDokumen}");

        if (! $config) {
            throw new InvalidArgumentException("Kode dokumen '{$kodeDokumen}' tidak dikenali di registry config/dokumen.php");
        }

        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $config['model']::findOrFail($recordId);

        $hash = hash('sha256', $kodeDokumen.'|'.$recordId.'|'.Str::uuid());
        $nomorDokumen = $config['nomor'] ? ($config['nomor'])($record) : null;

        $urlVerifikasi = URL::to("/verifikasi/{$hash}");

        // QR TTE: format PNG lewat backend Imagick/GD (dipasang otomatis oleh
        // simplesoftwareio/simple-qrcode), disuntik ke view sebagai base64
        // supaya dompdf tidak perlu request jaringan tambahan saat render.
        $qrBase64 = base64_encode(
            QrCode::format('png')->size(140)->margin(1)->generate($urlVerifikasi)
        );

        $dokumenCetak = DokumenCetak::create([
            'kode_dokumen' => $kodeDokumen,
            'dokumentable_type' => $config['model'],
            'dokumentable_id' => $record->id,
            'nomor_dokumen' => $nomorDokumen,
            'dicetak_oleh_id' => $dicetakOleh?->id,
            'hash_verifikasi' => $hash,
            'payload_snapshot' => $record->toArray(),
            'dicetak_at' => now(),
        ]);

        $pdf = Pdf::loadView("pdf.{$config['view']}", [
            'record' => $record,
            'judul' => $config['judul'],
            'kodeDokumen' => $kodeDokumen,
            'nomorDokumen' => $nomorDokumen,
            'qrBase64' => $qrBase64,
            'hashVerifikasi' => $hash,
            'dicetakAt' => $dokumenCetak->dicetak_at,
        ])->setPaper('a4');

        return $pdf->stream("{$kodeDokumen}-{$record->id}.pdf");
    }
}
