<?php

namespace App\Policies;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Models\User;

class SidangPolicy
{
    protected function pengendali(User $user): bool
    {
        return $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
    }

    /**
     * Plotting jadwal & dewan penguji (FR-04, FR-06, FR-08) — HANYA
     * Komisi Tesis/Kaprodi/Admin Prodi. Model instance opsional (dipakai
     * juga sebagai gate untuk aksi "buat baru", belum ada instance-nya).
     */
    public function plot(User $user, ?AktivitasSidang $sidang = null): bool
    {
        return $this->pengendali($user);
    }

    /**
     * Input rubrik nilai (FR-09) — HANYA Dewan Penguji yang benar-benar
     * ditugaskan (tercatat di penguji_sidangs) untuk sidang ini, atau
     * pengendali akademik untuk keperluan override/rekap.
     */
    public function nilai(User $user, AktivitasSidang $sidang): bool
    {
        if ($this->pengendali($user)) {
            return true;
        }

        return $sidang->pengujiSidangs()->where('dosen_id', $user->id)->exists();
    }

    /** Rekapitulasi nilai & BAP resmi — wewenang pengendali akademik. */
    public function rekap(User $user, AktivitasSidang $sidang): bool
    {
        return $this->pengendali($user);
    }

    /** Mengunduh naskah/dokumen sidang: penguji yang ditugaskan atau pengendali. */
    public function lihatNaskah(User $user, AktivitasSidang $sidang): bool
    {
        if ($this->pengendali($user)) {
            return true;
        }

        return $sidang->pengujiSidangs()->where('dosen_id', $user->id)->exists();
    }
}
