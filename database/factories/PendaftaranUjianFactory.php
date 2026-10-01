<?php

namespace Database\Factories;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PendaftaranUjianFactory extends Factory
{
    protected $model = PendaftaranUjian::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => PengajuanTesis::factory(),
            'jadwal_usulan_sidang' => now()->addDays(15),
            'naskah_tesis_lengkap_url' => '/storage/ujian/naskah/dummy.pdf',
            'artikel_jurnal_url' => '/storage/ujian/artikel/dummy.pdf',
            'prosiding_seminar_url' => '/storage/ujian/prosiding/dummy.pdf',
            'sertifikat_bahasa_url' => '/storage/ujian/bahasa/dummy.pdf',
            'jenis_skor_bahasa' => 'TOEFL',
            'skor_bahasa' => 500,
            'bukti_spp_terakhir_url' => '/storage/ujian/spp/dummy.pdf',
            'khs_kumulatif_url' => '/storage/ujian/khs/dummy.pdf',
            'surat_bebas_plagiasi_url' => '/storage/ujian/plagiasi/dummy.pdf',
            'similarity_score' => 18.5,
            'acc_tertulis_pembimbing_1' => false,
            'acc_tertulis_pembimbing_2' => false,
            'status_verifikasi_admin' => 'pending',
        ];
    }
}
