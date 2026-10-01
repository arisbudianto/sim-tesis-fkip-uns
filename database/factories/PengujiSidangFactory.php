<?php

namespace Database\Factories;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PengujiSidangFactory extends Factory
{
    protected $model = PengujiSidang::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'sidang_id' => AktivitasSidang::factory(),
            'dosen_id' => User::factory()->dosen(),
            'peran_penguji' => 'ketua_penguji',
            'nilai_total_angka' => null,
        ];
    }

    public function sudahDinilai(float $nilai = 85): static
    {
        return $this->state(fn () => ['nilai_total_angka' => $nilai]);
    }
}
