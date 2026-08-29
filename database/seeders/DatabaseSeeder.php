<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Mahasiswa
        User::create([
            'id' => Str::uuid(),
            'name' => 'Budi Santoso (Mahasiswa)',
            'identifier' => 'S032608001',
            'email' => 'mhs.budi@student.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'mahasiswa'
        ]);

        // 2. Dosen Pembimbing 1 (Bidang Studi)
        User::create([
            'id' => Str::uuid(),
            'name' => 'Dr. Eng. Herman Wijaya, M.T.',
            'identifier' => '197505102001121001',
            'email' => 'herman@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'studi',
            'kuota_bimbingan_maks' => 8
        ]);

        // 3. Dosen Pembimbing 2 (Bidang Pendidikan)
        User::create([
            'id' => Str::uuid(),
            'name' => 'Dr. Siti Rahmawati, M.Pd.',
            'identifier' => '198003152005012002',
            'email' => 'siti.rahma@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'pendidikan',
            'kuota_bimbingan_maks' => 8
        ]);

        // 4. Komisi Tesis
        User::create([
            'id' => Str::uuid(),
            'name' => 'Prof. Dr. Ir. Joko Susilo, M.T.',
            'identifier' => '196811201994031003',
            'email' => 'komisi.tesis@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'komisi_tesis',
            'is_komisi_tesis' => true
        ]);

        // 5. Kaprodi
        User::create([
            'id' => Str::uuid(),
            'name' => 'Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.',
            'identifier' => '197908222006041001',
            'email' => 'kaprodi.pgv@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'kaprodi'
        ]);

        // 6. Admin Program Studi
        User::create([
            'id' => Str::uuid(),
            'name' => 'Staf Tata Usaha Pascasarjana',
            'identifier' => '199201012018011005',
            'email' => 'admin.pasca@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'admin_prodi'
        ]);

        // 7-10. Dewan Penguji — dosen tambahan di luar Pembimbing 1 & 2,
        // dipakai untuk mengisi peran ketua_penguji, sekretaris_penguji,
        // penguji_studi, penguji_pendidikan di tabel penguji_sidangs.
        User::create([
            'id' => Str::uuid(),
            'name' => 'Prof. Dr. Bambang Kusumo, M.Pd.',
            'identifier' => '196502101990031004',
            'email' => 'ketua.penguji@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'pendidikan',
            'kuota_bimbingan_maks' => 8
        ]);

        User::create([
            'id' => Str::uuid(),
            'name' => 'Dr. Ratna Puspitasari, S.Pd., M.T.',
            'identifier' => '197711052003122001',
            'email' => 'sekretaris.penguji@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'studi',
            'kuota_bimbingan_maks' => 8
        ]);

        User::create([
            'id' => Str::uuid(),
            'name' => 'Dr. Agus Setiabudi, M.T.',
            'identifier' => '197203201999031002',
            'email' => 'penguji.studi@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'studi',
            'kuota_bimbingan_maks' => 8
        ]);

        User::create([
            'id' => Str::uuid(),
            'name' => 'Dr. Wulan Handayani, M.Pd.',
            'identifier' => '198106252006042001',
            'email' => 'penguji.pendidikan@fkip.uns.ac.id',
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'bidang_keahlian' => 'pendidikan',
            'kuota_bimbingan_maks' => 8
        ]);
    }
}
