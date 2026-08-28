<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        // 1. Kaprodi
        $kaprodiId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $kaprodiId,
            'name' => 'Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.',
            'email' => 'kaprodi_pgv@fkip.uns.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'kaprodi',
            'phone_number' => '081234567890',
            'created_at' => now(),
        ]);
        DB::table('dosen_profiles')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $kaprodiId,
            'nip' => '198003242005011002',
            'pangkat_golongan' => 'Pembina / IV a',
            'bidang_keahlian' => 'bidang_studi',
            'is_komisi_tesis' => true,
            'created_at' => now(),
        ]);

        // 2. Wadek 1 Dekanat
        $wadekId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $wadekId,
            'name' => 'Prof. Dr.paed. Nurma Yunita Indriyanti, M.Si., M.Sc.',
            'email' => 'wadek1@fkip.uns.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'dekanat',
            'phone_number' => '081234567891',
            'created_at' => now(),
        ]);
        DB::table('dosen_profiles')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $wadekId,
            'nip' => '198306262006042002',
            'pangkat_golongan' => 'Pembina Utama Madya / IV d',
            'bidang_keahlian' => 'bidang_pendidikan',
            'is_komisi_tesis' => false,
            'created_at' => now(),
        ]);

        // 3. Komisi Tesis
        $komisiId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $komisiId,
            'name' => 'Dr. Leny Noviani, M.Pd.',
            'email' => 'komisitesis_pgv@fkip.uns.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'komisi_tesis',
            'phone_number' => '081234567892',
            'created_at' => now(),
        ]);
        DB::table('dosen_profiles')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $komisiId,
            'nip' => '197903112005012001',
            'pangkat_golongan' => 'Pembina / IV a',
            'bidang_keahlian' => 'bidang_pendidikan',
            'is_komisi_tesis' => true,
            'created_at' => now(),
        ]);

        // 4. Mahasiswa Contoh
        $mhsId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $mhsId,
            'name' => 'Budi Santoso',
            'email' => 'mhs_tesis@student.uns.ac.id',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'phone_number' => '081234567893',
            'created_at' => now(),
        ]);
        DB::table('mahasiswa_profiles')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $mhsId,
            'nim' => 'S812308001',
            'program_studi' => 'Magister Pendidikan Guru Vokasi',
            'angkatan' => '2025',
            'created_at' => now(),
        ]);
    }
}
