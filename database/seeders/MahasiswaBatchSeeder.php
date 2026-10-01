<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MahasiswaBatchSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('user123');

        $mahasiswa = [
            ['S1106102610001', 'Amelia Syahbani Yuki Arisandi'],
            ['S1106102610002', 'Arya Pramudya Pramundana'],
            ['S162402001', "Alhaura' Nabighatul Ula"],
            ['S162402002', 'Muhamad Nur Azmi Wahyudi'],
            ['S162402003', 'Cahyo Widodo'],
            ['S162408001', 'Sarjoko'],
            ['S162408002', 'Arif Budiyarno'],
            ['S162502001', 'Arika Prihastanti Sutami'],
            ['S162502002', 'Arini Silmi Kaffa'],
            ['S162502003', 'Nur Fitri Wahyanti'],
            ['S162508001', 'Alfiyah Aini Hanifah'],
            ['S162508002', "Irba' Rizka Putri"],
            ['S162508003', 'Jovanka Ananda Putra'],
            ['S162508004', 'Waras Dwi Yhoga'],
            ['S162508005', 'Ma Anwar Yasin, S.Pd.'],
            ['S162508006', 'Tiara Kusuma Dewi'],
            ['S162602001', 'Ilham Robbul Khairi'],
            ['S162602002', 'Radeca Anggadewa'],
            ['S162602003', 'Yoana Lukita Sari'],
        ];

        foreach ($mahasiswa as [$nim, $nama]) {
            $email = strtolower($nim) . '@student.uns.ac.id';

            $existing = User::where('identifier', $nim)->orWhere('email', $email)->first();

            if ($existing) {
                $existing->update([
                    'name' => $nama,
                    'identifier' => $nim,
                    'email' => $email,
                    'password' => $password,
                    'role' => $existing->role ?: 'mahasiswa',
                ]);
                continue;
            }

            User::create([
                'id' => (string) Str::uuid(),
                'name' => $nama,
                'identifier' => $nim,
                'email' => $email,
                'password' => $password,
                'role' => 'mahasiswa',
            ]);
        }
    }
}
