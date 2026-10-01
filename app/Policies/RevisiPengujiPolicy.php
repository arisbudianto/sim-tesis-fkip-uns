<?php

namespace App\Policies;

use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Models\User;

class RevisiPengujiPolicy
{
    /**
     * ACC/tolak matriks revisi (FR-10) — HANYA dosen penguji yang
     * bersangkutan (baris ini memang miliknya), atau pengendali akademik
     * untuk keperluan override administratif.
     */
    public function acc(User $user, RevisiPenguji $item): bool
    {
        if ($user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            return true;
        }

        return $item->dosen_penguji_id === $user->id;
    }

    /** Pengesahan final gateway yudisium — HANYA Kaprodi. */
    public function pengesahan(User $user): bool
    {
        return $user->hasRole('kaprodi');
    }
}
