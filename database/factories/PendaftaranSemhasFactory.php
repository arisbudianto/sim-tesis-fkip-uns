<?php

namespace Database\Factories;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PendaftaranSemhasFactory extends Factory
{
    protected $model = PendaftaranSemhas::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => PengajuanTesis::factory(),
            'jadwal_usulan_sidang' => now()->addDays(15),
            'naskah_bab_1_5_url' => '/storage/semhas/naskah/dummy.pdf',
            'draf_artikel_ilmiah_urls' => ['/storage/semhas/artikel/1.pdf', '/storage/semhas/artikel/2.pdf'],
            'bukti_status_under_review_url' => '/storage/semhas/review/dummy.pdf',
            'bukti_spp_url' => '/storage/semhas/spp/dummy.pdf',
            'approval_pembimbing_1' => false,
            'approval_pembimbing_2' => false,
            'status_verifikasi_admin' => 'pending',
        ];
    }
}
