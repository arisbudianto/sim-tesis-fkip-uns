<?php

namespace Database\Factories;

use App\Domain\Dokumen\Models\DokumenCetak;
use App\Domain\Sidang\Models\AktivitasSidang;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DokumenCetakFactory extends Factory
{
    protected $model = DokumenCetak::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'kode_dokumen' => 'FPT-TI-02',
            'dokumentable_type' => AktivitasSidang::class,
            'dokumentable_id' => AktivitasSidang::factory(),
            'nomor_dokumen' => fake()->bothify('ST/####/2026'),
            'hash_verifikasi' => hash('sha256', (string) Str::uuid()),
            'file_path' => null,
            'versi' => 1,
            'dicetak_at' => now(),
        ];
    }
}
