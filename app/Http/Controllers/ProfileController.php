<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Menu "Edit Profil Saya" — berlaku untuk SEMUA peran (mahasiswa, dosen,
 * komisi_tesis, kaprodi, admin_prodi). Berbeda dari PenggunaController
 * (yang dipakai Komisi Tesis/Kaprodi/Admin Prodi untuk mengelola akun ORANG
 * LAIN), controller ini khusus untuk user mengubah datanya SENDIRI —
 * makanya tidak ada parameter {user}, selalu Auth::user().
 *
 * Kolom `identifier` (NIM/NIP) dan `role` SENGAJA tidak bisa diubah di sini
 * supaya tidak disalahgunakan untuk mengubah identitas resmi/kewenangan
 * sendiri — perubahan itu tetap lewat Data Master oleh Komisi Tesis/Kaprodi/
 * Admin Prodi (PenggunaController::update()).
 */
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'nomor_wa' => 'nullable|string|max:20',
        ];

        // Bidang keahlian & pangkat/golongan hanya relevan untuk peran
        // non-mahasiswa (dosen/komisi_tesis/kaprodi/admin_prodi) — sama
        // seperti pola di PenggunaController, supaya field yang muncul di
        // form konsisten dengan yang benar-benar disimpan.
        $isStaf = $user->role !== 'mahasiswa';
        if ($isStaf) {
            $rules['bidang_keahlian'] = 'nullable|in:studi,pendidikan';
            $rules['pangkat_golongan'] = 'nullable|string|max:100';
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nomor_wa = $validated['nomor_wa'] ?? null;

        if ($isStaf) {
            $user->bidang_keahlian = $user->role === 'dosen' ? ($validated['bidang_keahlian'] ?? $user->bidang_keahlian) : null;
            $user->pangkat_golongan = $validated['pangkat_golongan'] ?? null;
        }

        $user->save();

        AuditLogger::log($user, 'profile.update', 'User', $user->id, 'Memperbarui data profil sendiri.');

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password sendiri — form TERPISAH dari update() di atas supaya
     * salah ketik di form data diri tidak sampai mengosongkan/mengganti
     * password tanpa sengaja. Wajib masukkan password lama (current_password
     * rule bawaan Laravel, divalidasi terhadap guard yang sedang login).
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [
            'current_password.current_password' => 'Password lama yang Anda masukkan salah.',
        ]);

        $user->forceFill(['password' => Hash::make($validated['password'])])->save();

        AuditLogger::log($user, 'profile.updatePassword', 'User', $user->id, 'Mengganti password sendiri.');

        return back()->with('success', 'Password berhasil diganti. Gunakan password baru pada login berikutnya.');
    }
}
