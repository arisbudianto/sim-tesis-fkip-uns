<?php

namespace App\Services;

use App\Models\PengajuanTesis;
use Exception;

class LifecycleStateMachine
{
    public static function canTransitionTo(PengajuanTesis $tesis, string $targetTahap): bool
    {
        $current = $tesis->status_tahap;

        switch ($targetTahap) {
            case 'tahap_2_sempro':
                return $current === 'tahap_1_bimbingan' 
                    && $tesis->pembimbing_1_id 
                    && $tesis->pembimbing_2_id;

            case 'tahap_3_semhas':
                $semproSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'sempro')->first();
                if (!$semproSidang || !$semproSidang->revisiDokumen) return false;
                return $current === 'tahap_2_sempro' && $semproSidang->revisiDokumen->pengesahan_kaprodi;

            case 'tahap_4_ujian':
                $semhasSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'semhas')->first();
                if (!$semhasSidang || !$semhasSidang->revisiDokumen) return false;
                return $current === 'tahap_3_semhas' && $semhasSidang->revisiDokumen->pengesahan_kaprodi;

            case 'selesai_yudisium':
                $ujianSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'ujian')->first();
                if (!$ujianSidang || !$ujianSidang->revisiDokumen) return false;
                return $current === 'tahap_4_ujian' && $ujianSidang->revisiDokumen->pengesahan_kaprodi;

            default:
                return false;
        }
    }

    public static function transition(PengajuanTesis $tesis, string $targetTahap): void
    {
        if (!self::canTransitionTo($tesis, $targetTahap)) {
            throw new Exception("Transisi tidak diizinkan: Mahasiswa belum memenuhi prasyarat untuk tahap {$targetTahap}");
        }

        $tesis->update(['status_tahap' => $targetTahap]);
    }
}
