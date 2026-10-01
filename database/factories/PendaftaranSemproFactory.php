<?php

namespace Database\Factories;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sempro\Models\PendaftaranSempro;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PendaftaranSemproFactory extends Factory
{
    protected $model = PendaftaranSempro::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => PengajuanTesis::factory(),
            'jadwal_usulan_sidang' => now()->addDays(15),
            'naskah_proposal_url' => '/storage/sempro/naskah/dummy.pdf',
            'bukti_spp_url' => '/storage/sempro/spp/dummy.pdf',
            'khs_url' => '/storage/sempro/khs/dummy.pdf',
            'form_fpt_ti_01_url' => '/storage/sempro/fpt-ti-01/dummy.pdf',
            'status_verifikasi_admin' => 'pending',
        ];
    }
}
