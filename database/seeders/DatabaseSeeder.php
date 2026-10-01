<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed UUIDs agar DemoDataSeeder dan referensi lintas seeder stabil
        $users = [
            [
                'id' => '5987018e-25fe-4ab0-9002-30cf9f5e3ffd',
                'name' => 'Budi Santoso',
                'identifier' => 'S032608001',
                'email' => 'mhs.budi@student.uns.ac.id',
                'role' => 'mahasiswa',
            ],
            [
                'id' => '96cb63eb-33ef-437f-bd7d-24e1ad7dd78e',
                'name' => 'Dr. Eng. Herman Wijaya, M.T.',
                'identifier' => '197505102001121001',
                'email' => 'herman@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'studi',
                'kuota_bimbingan_maks' => 8,
            ],
            [
                'id' => '9ece8429-28d5-4eb8-9d9e-e5c80204fc30',
                'name' => 'Dr. Siti Rahmawati, M.Pd.',
                'identifier' => '198003152005012002',
                'email' => 'siti.rahma@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'pendidikan',
                'kuota_bimbingan_maks' => 8,
            ],
            [
                'id' => '25d1d45b-4c1c-4fb5-9a19-67f9e6e5cb04',
                'name' => 'Prof. Dr. Ir. Joko Susilo, M.T.',
                'identifier' => '196811201994031003',
                'email' => 'komisi.tesis@fkip.uns.ac.id',
                'role' => 'komisi_tesis',
                'is_komisi_tesis' => true,
            ],
            [
                'id' => 'a5818cef-09be-4390-9cbd-b40cb5502233',
                'name' => 'Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.',
                'identifier' => '198003242005011002',
                'email' => 'kaprodi.pgv@fkip.uns.ac.id',
                'role' => 'kaprodi',
            ],
            [
                'id' => '7fe7dd64-4ae3-4f71-9bb9-5ebbf9b39bd1',
                'name' => 'Staf Tata Usaha Pascasarjana',
                'identifier' => '199201012018011005',
                'email' => 'admin.pasca@fkip.uns.ac.id',
                'role' => 'admin_prodi',
            ],
            [
                'id' => 'c209fe36-d50c-400b-857e-f7941b67ca94',
                'name' => 'Prof. Dr. Bambang Kusumo, M.Pd.',
                'identifier' => '196502101990031004',
                'email' => 'ketua.penguji@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'pendidikan',
                'kuota_bimbingan_maks' => 8,
            ],
            [
                'id' => '74247f3f-b234-42c1-b04f-493058902d66',
                'name' => 'Dr. Ratna Puspitasari, S.Pd., M.T.',
                'identifier' => '197711052003122001',
                'email' => 'sekretaris.penguji@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'studi',
                'kuota_bimbingan_maks' => 8,
            ],
            [
                'id' => '98f8d207-acad-49d7-9920-192726337ebb',
                'name' => 'Dr. Agus Setiabudi, M.T.',
                'identifier' => '197203201999031002',
                'email' => 'penguji.studi@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'studi',
                'kuota_bimbingan_maks' => 8,
            ],
            [
                'id' => 'c6929db0-1b0a-4d9a-8422-25e0969a3353',
                'name' => 'Dr. Wulan Handayani, M.Pd.',
                'identifier' => '198106252006042001',
                'email' => 'penguji.pendidikan@fkip.uns.ac.id',
                'role' => 'dosen',
                'bidang_keahlian' => 'pendidikan',
                'kuota_bimbingan_maks' => 8,
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('password'),
                    'role' => $role,
                ])
            );

            // Pastikan pivot user_roles terisi (idempotent)
            UserRole::firstOrCreate(
                ['user_id' => $user->id, 'role' => $role],
                ['user_id' => $user->id, 'role' => $role]
            );
        }

        $this->call([
            NotifikasiTemplateSeeder::class,
            DemoDataSeeder::class,
            MahasiswaBatchSeeder::class,
            PenggunaStaffSeeder::class,
            DemoAlurRiilSeeder::class,
        ]);
    }
}
