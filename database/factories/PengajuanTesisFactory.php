<?php

namespace Database\Factories;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PengajuanTesisFactory extends Factory
{
    protected $model = PengajuanTesis::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'mahasiswa_id' => User::factory()->mahasiswa(),
            'judul_tesis' => fake()->sentence(8),
            'bidang_fokus' => fake()->words(3, true),
            'status_tahap' => 'tahap_1_bimbingan',
        ];
    }

    /** State: sudah punya 2 pembimbing (syarat transisi ke Sempro). */
    public function denganPembimbingLengkap(): static
    {
        return $this->state(fn () => [
            'pembimbing_1_id' => User::factory()->dosen(),
            'pembimbing_2_id' => User::factory()->dosen(),
            'nomor_sk_pembimbing' => 'SK/TEST/001',
            'tanggal_sk_pembimbing' => now(),
        ]);
    }
}
