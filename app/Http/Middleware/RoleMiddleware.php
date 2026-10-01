<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // hasAnyRole() mengecek tabel pivot user_roles (mendukung multi-peran
        // per user), dengan fallback otomatis ke kolom `role` lama kalau
        // baris pivot belum ada — lihat App\Models\User::roleList().
        if (!Auth::check() || !Auth::user()->hasAnyRole($roles)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki otoritas pada modul akademik ini.');
        }
        return $next($request);
    }
}