<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PenggunaStaffSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('user123');

        $staff = [
            ['198003242005011002', 'Abdul Haris Setiawan, S.Pd., M.Pd., Ph.D.', 'kaprodi', ['kaprodi', 'dosen']],
            ['1989022720161001', 'Dr. Kundari Rahmawati, S.Pd., M.Eng.', 'dosen', ['dosen']],
            ['197606182000031001', 'Dr. Taufiq Lilo Adi Sucipto, S.T., M.T.', 'dosen', ['dosen']],
            ['197605122005012001', 'Dr. Ernawati Sri Sunarsih, S.T., M.Eng', 'dosen', ['dosen']],
            ['197904262002122001', 'Dr. Anis Rahmawati, S.T., M.T.', 'dosen', ['dosen']],
            ['197709022005011001', 'Dr. Ir. Ida Nugroho Saputro, S.T., M.Eng.', 'dosen', ['dosen']],
            ['196401031992031003', 'Prof. Dr. Sukatiman, S.T., M.Si.', 'dosen', ['dosen']],
            ['1957041420220501', 'Dr. Ir. Chundakus Habsya, M.S., Ars.', 'dosen', ['dosen']],
            ['196708191993031002', 'Prof. Dr. Agus Efendi, M.Pd.', 'dosen', ['dosen']],
            ['198012172005011001', 'Dr. Aris Budianto, S.T., M.Eng.', 'komisi_tesis', ['komisi_tesis', 'dosen']],
            ['198008082005011003', 'Dr. Dwi Maryono, S.Si., M.Kom.', 'dosen', ['dosen']],
            ['1978032520161001', 'Cucuk Wawan Budiyanto, ST., Ph.D.', 'dosen', ['dosen']],
            ['196107291991031001', 'Prof. Dr. Muhammad Akhyar, M.Pd', 'dosen', ['dosen']],
            ['197805142005012002', 'Prof. Dr. Indah Widiastuti, S.T., M.Eng.', 'dosen', ['dosen']],
            ['198208112006041001', 'Prof. Dr. Eng. Ir. Herman Saputro, S.Pd., M.Pd., M.T.', 'dosen', ['dosen']],
            ['197801132002121009', 'Dr. Yuyun Estriyanto, S.T., M.T.', 'dosen', ['dosen']],
            ['197901242002121002', 'Dr. Danar Susilo Wijayanto, S.T., M.Eng.', 'dosen', ['dosen']],
            ['198112302012121002', 'Prof. Dr.Eng. Nugroho Agung Pambudi, M.Eng.', 'dosen', ['dosen']],
            ['197303151995122001', 'Dr. Eng. Nyenyep Sriwardani, S.T., M.T.', 'dosen', ['dosen']],
            ['1991022220161001', 'Dr. Valiant Lukad Perdana Sutrisno, S.Pd., M.Pd.', 'dosen', ['dosen']],
            ['195902011985032002', 'Prof. Dr. Siswandari, M.Stats', 'dosen', ['dosen']],
            ['1983082720061201', 'Eko Budi Susanto, S.E.', 'admin_prodi', ['admin_prodi']],
        ];

        foreach ($staff as [$nip, $nama, $roleUtama, $semuaRole]) {
            $email = strtolower($nip) . '@fkip.uns.ac.id';

            $user = User::where('identifier', $nip)->orWhere('email', $email)->first();

            $payload = [
                'name' => $nama,
                'identifier' => $nip,
                'email' => $email,
                'password' => $password,
                'role' => $roleUtama,
            ];

            if (in_array('dosen', $semuaRole, true) && Schema::hasColumn('users', 'kuota_bimbingan_maks')) {
                $payload['kuota_bimbingan_maks'] = $user->kuota_bimbingan_maks ?? 8;
            }

            if ($roleUtama === 'komisi_tesis' && Schema::hasColumn('users', 'is_komisi_tesis')) {
                $payload['is_komisi_tesis'] = true;
            }

            if ($user) {
                $user->update($payload);
            } else {
                $payload['id'] = (string) Str::uuid();
                $user = User::create($payload);
            }

            foreach ($semuaRole as $r) {
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole($r);
                } elseif (Schema::hasTable('user_roles')) {
                    DB::table('user_roles')->updateOrInsert(
                        ['user_id' => $user->id, 'role' => $r],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }
}
