<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'name' => fake()->name(),
            'identifier' => fake()->unique()->numerify('S03260####'),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'mahasiswa',
            'kuota_bimbingan_maks' => 0,
        ];
    }

    public function mahasiswa(): static
    {
        return $this->state(fn () => ['role' => 'mahasiswa', 'kuota_bimbingan_maks' => 0]);
    }

    public function dosen(): static
    {
        return $this->state(fn () => ['role' => 'dosen', 'kuota_bimbingan_maks' => 8]);
    }

    public function komisiTesis(): static
    {
        return $this->state(fn () => ['role' => 'komisi_tesis']);
    }
}
