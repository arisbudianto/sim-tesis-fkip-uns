<?php

namespace App\Services;

use App\Models\User;
use App\Models\PengajuanTesis;
use Exception;

class AdvisorQuotaEngine
{
    /**
     * @param string $dosenId
     * @param string|null $excludePengajuanId  Kecualikan pengajuan ini dari hitungan —
     *        dipakai saat MENGEDIT alokasi dosen yang sama, supaya pengajuan yang
     *        sedang diedit tidak ikut dihitung sebagai beban baru untuk dirinya sendiri.
     */
    public static function checkQuota(string $dosenId, ?string $excludePengajuanId = null): bool
    {
        $dosen = User::findOrFail($dosenId);

        $query = PengajuanTesis::where(function ($q) use ($dosenId) {
            $q->where('pembimbing_1_id', $dosenId)
              ->orWhere('pembimbing_2_id', $dosenId);
        })->where('status_tahap', '!=', 'selesai_yudisium');

        if ($excludePengajuanId) {
            $query->where('id', '!=', $excludePengajuanId);
        }

        return $query->count() < $dosen->kuota_bimbingan_maks;
    }
}
