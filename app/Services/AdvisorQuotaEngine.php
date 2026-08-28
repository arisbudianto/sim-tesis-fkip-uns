<?php

namespace App\Services;

use App\Models\User;
use App\Models\PengajuanTesis;
use Exception;

class AdvisorQuotaEngine
{
    public static function checkQuota(string $dosenId): bool
    {
        $dosen = User::findOrFail($dosenId);
        
        $activeBimbinganCount = PengajuanTesis::where(function ($q) use ($dosenId) {
            $q->where('pembimbing_1_id', $dosenId)
              ->orWhere('pembimbing_2_id', $dosenId);
        })->where('status_tahap', '!=', 'selesai_yudisium')->count();

        return $activeBimbinganCount < $dosen->kuota_bimbingan_maks;
    }
}
