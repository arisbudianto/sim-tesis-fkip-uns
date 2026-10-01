<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function resetPassword(Request $request, User $user)
    {
        $aktor = $request->user();
        abort_unless($aktor->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']), 403);

        $user->update(['password' => Hash::make('user123')]);

        AuditLogger::log(
            $aktor,
            'pengguna.resetPassword',
            'User',
            $user->id,
            "Password {$user->name} ({$user->identifier}) direset ke default user123"
        );

        return back()->with('success', 'Password '.$user->name.' direset ke user123.');
    }
}
