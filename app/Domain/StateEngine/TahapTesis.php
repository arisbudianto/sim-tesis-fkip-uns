<?php

namespace App\Domain\StateEngine;

/**
 * Enum resmi status siklus tesis mahasiswa (Modul 4).
 *
 * STATE 1: Belum Ada Pembimbing     -> TAHAP_1_BIMBINGAN
 * STATE 2: Pembimbing Ditetapkan    -> (masih TAHAP_1_BIMBINGAN, ditandai pembimbing_1_id/2_id terisi)
 * STATE 3: Sempro Terdaftar/ACC     -> TAHAP_2_SEMPRO
 * STATE 4: Semhas Terdaftar/ACC     -> TAHAP_3_SEMHAS
 * STATE 5: Ujian Terdaftar/Lulus    -> TAHAP_4_UJIAN
 * STATE 6: Revisi ACC -> Yudisium   -> SELESAI_YUDISIUM
 *
 * Value string PERSIS sama dengan kolom `pengajuan_tesis.status_tahap`
 * (enum DB) supaya tidak perlu migration konversi data.
 */
enum TahapTesis: string
{
    case TAHAP_1_BIMBINGAN = 'tahap_1_bimbingan';
    case TAHAP_2_SEMPRO = 'tahap_2_sempro';
    case TAHAP_3_SEMHAS = 'tahap_3_semhas';
    case TAHAP_4_UJIAN = 'tahap_4_ujian';
    case SELESAI_YUDISIUM = 'selesai_yudisium';

    public function label(): string
    {
        return match ($this) {
            self::TAHAP_1_BIMBINGAN => 'Tahap 1 (Penentuan Pembimbing)',
            self::TAHAP_2_SEMPRO => 'Tahap 2 (Seminar Proposal)',
            self::TAHAP_3_SEMHAS => 'Tahap 3 (Seminar Hasil)',
            self::TAHAP_4_UJIAN => 'Tahap 4 (Ujian Tesis)',
            self::SELESAI_YUDISIUM => 'Yudisium',
        };
    }

    /** Urutan linier tahap (dipakai untuk mendeteksi percobaan "lompat tahap" atau mundur). */
    public function urutan(): int
    {
        return match ($this) {
            self::TAHAP_1_BIMBINGAN => 1,
            self::TAHAP_2_SEMPRO => 2,
            self::TAHAP_3_SEMHAS => 3,
            self::TAHAP_4_UJIAN => 4,
            self::SELESAI_YUDISIUM => 5,
        };
    }

    /** Satu-satunya tahap berikutnya yang sah dari tahap ini (linear, tidak boleh lompat). */
    public function tahapBerikutnya(): ?self
    {
        return match ($this) {
            self::TAHAP_1_BIMBINGAN => self::TAHAP_2_SEMPRO,
            self::TAHAP_2_SEMPRO => self::TAHAP_3_SEMHAS,
            self::TAHAP_3_SEMHAS => self::TAHAP_4_UJIAN,
            self::TAHAP_4_UJIAN => self::SELESAI_YUDISIUM,
            self::SELESAI_YUDISIUM => null,
        };
    }
}
