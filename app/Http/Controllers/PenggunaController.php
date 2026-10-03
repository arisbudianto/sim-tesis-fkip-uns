<?php

namespace App\Http\Controllers;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

    /**
     * Tambah akun mahasiswa baru (Master Data Mahasiswa). Password default
     * user123, sama seperti alur reset password supaya konsisten.
     */
    public function storeMahasiswa(Request $request)
    {
        $aktor = $request->user();
        abort_unless($aktor->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:50|unique:users,identifier',
            'email' => 'required|string|email|max:255|unique:users,email',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'identifier' => $validated['identifier'],
            'email' => $validated['email'],
            'password' => Hash::make('user123'),
            'role' => 'mahasiswa',
            'kuota_bimbingan_maks' => 0,
        ]);
        $user->assignRole('mahasiswa');

        AuditLogger::log(
            $aktor, 'pengguna.tambahMahasiswa', 'User', $user->id,
            "Menambahkan akun mahasiswa {$user->name} ({$user->identifier})."
        );

        return back()->with('success', "Akun mahasiswa {$user->name} berhasil dibuat. Password default: user123.");
    }

    /**
     * Tambah akun dosen/staf (dosen, komisi_tesis, admin_prodi, kaprodi).
     * Terpisah dari storeMahasiswa karena field-nya beda (bidang keahlian
     * & kuota bimbingan cuma relevan untuk role dosen).
     */
    public function storeDosen(Request $request)
    {
        $aktor = $request->user();
        abort_unless($aktor->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:50|unique:users,identifier',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|in:dosen,komisi_tesis,admin_prodi,kaprodi',
            'bidang_keahlian' => 'nullable|in:studi,pendidikan',
            'kuota_bimbingan_maks' => 'nullable|integer|min:0|max:20',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'identifier' => $validated['identifier'],
            'email' => $validated['email'],
            'password' => Hash::make('user123'),
            'role' => $validated['role'],
            'bidang_keahlian' => $validated['role'] === 'dosen' ? ($validated['bidang_keahlian'] ?? 'studi') : null,
            'kuota_bimbingan_maks' => $validated['role'] === 'dosen' ? ($validated['kuota_bimbingan_maks'] ?? 8) : 0,
        ]);
        $user->assignRole($validated['role']);

        AuditLogger::log(
            $aktor, 'pengguna.tambahDosen', 'User', $user->id,
            "Menambahkan akun {$validated['role']} {$user->name} ({$user->identifier})."
        );

        return back()->with('success', "Akun {$user->name} berhasil dibuat. Password default: user123.");
    }

    /**
     * Edit data akun (mahasiswa ATAU dosen/staf, dibedakan dari $user->role
     * yang sudah ada). Password TIDAK diubah lewat sini — tetap lewat
     * tombol Reset PW yang sudah ada, supaya alurnya jelas dan terpisah.
     */
    public function update(Request $request, User $user)
    {
        $aktor = $request->user();
        abort_unless($aktor->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']), 403);

        $rules = [
            'name' => 'required|string|max:255',
            'identifier' => 'required|string|max:50|unique:users,identifier,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ];

        $isStaf = $user->role !== 'mahasiswa';
        if ($isStaf) {
            $rules['role'] = 'required|in:dosen,komisi_tesis,admin_prodi,kaprodi';
            $rules['bidang_keahlian'] = 'nullable|in:studi,pendidikan';
            $rules['kuota_bimbingan_maks'] = 'nullable|integer|min:0|max:20';
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->identifier = $validated['identifier'];
        $user->email = $validated['email'];

        if ($isStaf) {
            $user->role = $validated['role'];
            $user->bidang_keahlian = $validated['role'] === 'dosen' ? ($validated['bidang_keahlian'] ?? 'studi') : null;
            $user->kuota_bimbingan_maks = $validated['role'] === 'dosen' ? ($validated['kuota_bimbingan_maks'] ?? 8) : 0;
            $user->assignRole($validated['role']);
        }

        $user->save();

        AuditLogger::log(
            $aktor, 'pengguna.edit', 'User', $user->id,
            "Mengubah data akun {$user->name} ({$user->identifier})."
        );

        return back()->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus akun. Diblokir kalau akun masih punya data terkait (mahasiswa
     * yang sudah punya pengajuan tesis, atau dosen yang masih tercatat
     * sebagai pembimbing/penguji/komisi) supaya tidak meninggalkan data
     * yatim atau kena error FK dari database.
     */
    public function destroy(Request $request, User $user)
    {
        $aktor = $request->user();
        abort_unless($aktor->hasAnyRole(['admin_prodi', 'kaprodi', 'komisi_tesis']), 403);

        if ($user->id === $aktor->id) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus akun yang sedang Anda pakai sendiri.']);
        }

        if ($user->role === 'mahasiswa') {
            if (PengajuanTesis::where('mahasiswa_id', $user->id)->exists()) {
                return back()->withErrors([
                    'error' => "Akun {$user->name} tidak bisa dihapus karena sudah memiliki data pengajuan tesis. Hapus/alihkan dulu pengajuannya lewat tab Pengajuan.",
                ]);
            }
        } else {
            $terpakai = PengajuanTesis::where('pembimbing_1_id', $user->id)->orWhere('pembimbing_2_id', $user->id)->exists()
                || PengujiSidang::where('dosen_id', $user->id)->exists()
                || AktivitasSidang::where('komisi_tesis_id', $user->id)->exists();

            if ($terpakai) {
                return back()->withErrors([
                    'error' => "Akun {$user->name} tidak bisa dihapus karena masih tercatat sebagai pembimbing, penguji, atau penanggung jawab sidang pada data yang ada.",
                ]);
            }
        }

        $nama = $user->name;
        $identifier = $user->identifier;
        $userId = $user->id;
        $user->delete();

        AuditLogger::log($aktor, 'pengguna.hapus', 'User', $userId, "Menghapus akun {$nama} ({$identifier}).");

        return back()->with('success', "Akun {$nama} berhasil dihapus.");
    }
}
