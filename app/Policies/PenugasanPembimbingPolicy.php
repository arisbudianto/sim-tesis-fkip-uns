<?php

namespace App\Policies;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Models\User;

/**
 * Policy untuk model PengajuanTesis — mencakup wewenang alokasi pembimbing
 * (FR-01) sekaligus hak mendaftar sidang (Sempro/Semhas/Ujian) yang secara
 * data berakar pada satu PengajuanTesis yang sama.
 */
class PenugasanPembimbingPolicy
{
    protected function pengendali(User $user): bool
    {
        return $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
    }

    /** Melihat detail pengajuan: pemilik (mahasiswa ybs), pembimbingnya, atau pengendali akademik. */
    public function view(User $user, PengajuanTesis $tesis): bool
    {
        return $user->id === $tesis->mahasiswa_id
            || $user->id === $tesis->pembimbing_1_id
            || $user->id === $tesis->pembimbing_2_id
            || $this->pengendali($user);
    }

    /** Menetapkan/mengubah Pembimbing 1 & 2 — HANYA Komisi Tesis/Kaprodi/Admin Prodi (FR-01). */
    public function allocate(User $user, PengajuanTesis $tesis): bool
    {
        return $this->pengendali($user);
    }

    /** Mengubah judul/fokus/abstrak setelah tersimpan — wewenang pengendali akademik. */
    public function editJudul(User $user, PengajuanTesis $tesis): bool
    {
        return $this->pengendali($user);
    }

    public function delete(User $user, PengajuanTesis $tesis): bool
    {
        return $this->pengendali($user);
    }

    /**
     * Mendaftar sidang (Sempro/Semhas/Ujian) atas nama PengajuanTesis ini —
     * HANYA mahasiswa pemilik pengajuan tesis tersebut. Ini menutup celah
     * di mana mahasiswa bisa mendaftar sidang atas nama mahasiswa lain
     * hanya dengan menebak/mengetahui ID pengajuan.
     */
    public function daftarSidang(User $user, PengajuanTesis $tesis): bool
    {
        return $user->id === $tesis->mahasiswa_id;
    }

    /** Memverifikasi/menyetujui-menolak pendaftaran sidang — pengendali akademik. */
    public function verifikasiPendaftaran(User $user, PengajuanTesis $tesis): bool
    {
        return $this->pengendali($user);
    }

    /**
     * Approval digital naskah oleh Pembimbing Utama/Pendamping (Modul 7,
     * syarat Semhas) — HANYA pembimbing 1 atau pembimbing 2 tesis ybs yang
     * boleh memberi approval untuk slotnya masing-masing, atau pengendali
     * akademik untuk override administratif.
     */
    public function approveNaskah(User $user, PengajuanTesis $tesis): bool
    {
        return $user->id === $tesis->pembimbing_1_id
            || $user->id === $tesis->pembimbing_2_id
            || $this->pengendali($user);
    }
}
