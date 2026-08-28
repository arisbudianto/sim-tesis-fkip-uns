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

    /**
     * Label ramah-baca untuk tiap status_tahap, dipakai pada pesan blokir.
     */
    protected static function label(string $tahap): string
    {
        return match ($tahap) {
            'tahap_1_bimbingan' => 'Tahap 1 (Penentuan Pembimbing)',
            'tahap_2_sempro'    => 'Tahap 2 (Seminar Proposal)',
            'tahap_3_semhas'    => 'Tahap 3 (Seminar Hasil)',
            'tahap_4_ujian'     => 'Tahap 4 (Ujian Tesis)',
            'selesai_yudisium'  => 'Yudisium',
            default             => $tahap,
        };
    }

    /**
     * GATE PENDAFTARAN: dipakai oleh controller pendaftaran (Semhas, Ujian, dst)
     * untuk memastikan mahasiswa memang SUDAH berada di tahap yang didaftar
     * (artinya tahap sebelumnya sudah selesai & disahkan Kaprodi via
     * RevisiDokumenController::pengesahanKaprodi()).
     *
     * Beda dengan canTransitionTo() yang memvalidasi SAAT transisi terjadi,
     * method ini memvalidasi SAAT mahasiswa mencoba mendaftar ke tahap
     * tersebut (status_tahap seharusnya sudah persis sama).
     *
     * Return null jika boleh lanjut, atau string alasan penolakan jika tidak.
     */
    public static function blockReason(PengajuanTesis $tesis, string $requiredTahap): ?string
    {
        if ($tesis->status_tahap === $requiredTahap) {
            return null;
        }

        // Jika mahasiswa sudah lewat dari tahap ini juga tetap ditolak
        // (mencegah pendaftaran ganda / mundur ke tahap yang sudah lewat).
        $currentLabel = self::label($tesis->status_tahap);
        $requiredLabel = self::label($requiredTahap);

        return "Pendaftaran ditolak: status akademik mahasiswa saat ini adalah {$currentLabel}, "
             . "belum memenuhi syarat untuk mendaftar {$requiredLabel}. "
             . "Pastikan tahap sebelumnya sudah dinyatakan lulus dan revisinya sudah disahkan Kaprodi.";
    }

    /**
     * Sama seperti blockReason(), tapi khusus dipakai Komisi Tesis saat
     * plotting jadwal sidang (KomisiTesisController) — memastikan jenis
     * sidang yang mau dijadwalkan cocok dengan tahap_sidang yang sedang
     * dijalani mahasiswa saat ini.
     */
    public static function blockReasonForSidang(PengajuanTesis $tesis, string $tahapSidang): ?string
    {
        $expectedStatus = match ($tahapSidang) {
            'sempro' => 'tahap_2_sempro',
            'semhas' => 'tahap_3_semhas',
            'ujian'  => 'tahap_4_ujian',
            default  => null,
        };

        if ($expectedStatus === null) {
            return "Jenis sidang '{$tahapSidang}' tidak dikenali oleh state machine.";
        }

        return self::blockReason($tesis, $expectedStatus);
    }
}
