<?php

namespace Database\Factories;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AktivitasSidangFactory extends Factory
{
    protected $model = AktivitasSidang::class;

    public function definition(): array
    {
        $mulai = now()->addDays(15)->addMinutes(fake()->numberBetween(0, 100000));

        return [
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => PengajuanTesis::factory(),
            'tahap_sidang' => 'sempro',
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $mulai->copy()->addHours(2),
            'ruangan' => 'Ruang Sidang Test ' . fake()->unique()->numberBetween(1, 100000),
            'komisi_tesis_id' => User::factory()->komisiTesis(),
            'is_locked' => true,
        ];
    }
}
