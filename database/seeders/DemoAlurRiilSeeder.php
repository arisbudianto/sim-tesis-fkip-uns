<?php

namespace Database\Seeders;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\ManajemenNilaiSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\StateEngine\Models\StateTransitionLog;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Data dummy alur nyata: mahasiswa + pembimbing dari daftar aktif PGV.
 * Sebar ke Tahap 1–4 dan Yudisium agar demo bisa diklik.
 */
class DemoAlurRiilSeeder extends Seeder
{
    public function run(): void
    {
        $komisi = $this->findUser(['198012172005011001'], ['Aris Budianto'])
            ?? User::where('role', 'komisi_tesis')->first();
        $kaprodi = $this->findUser(['198003242005011002'], ['Abdul Haris']);

        $poolPenguji = User::where('role', 'dosen')->orderBy('name')->get();

        $rows = [
            [
                'nim' => 'S162502001', 'nama' => 'Arika Prihastanti Sutami',
                'judul' => 'Pengembangan Media Pembelajaran Vokasi Berbasis Proyek',
                'p1' => ['198012172005011001', 'Aris Budianto'],
                'p2' => ['197709022005011001', 'Ida Nugroho'],
                'tahap' => 'tahap_1_bimbingan', 'skenario' => 'bimbingan',
            ],
            [
                'nim' => 'S162502002', 'nama' => 'Arini Silmi Kaffa',
                'judul' => 'Model Beasiswa dan Motivasi Riset Mahasiswa Pendidikan Vokasi',
                'p1' => ['197904262002122001', 'Anis Rahmawati'],
                'p2' => ['197901242002121002', 'Danar Susilo'],
                'tahap' => 'tahap_2_sempro', 'skenario' => 'sempro_pending',
            ],
            [
                'nim' => 'S162502003', 'nama' => 'Nur Fitri Wahyanti',
                'judul' => 'Kesiapan Guru Vokasi dalam Implementasi Kurikulum Merdeka',
                'p1' => ['198003242005011002', 'Abdul Haris'],
                'p2' => ['196708191993031002', 'Agus Efendi'],
                'tahap' => 'tahap_2_sempro', 'skenario' => 'sempro_jadwal',
            ],
            [
                'nim' => 'S162508001', 'nama' => 'Alfiyah Aini Hanifah',
                'judul' => 'Literasi Digital Siswa SMK pada Praktik Kerja Industri',
                'p1' => ['1978032520161001', 'Cucuk Wawan'],
                'p2' => ['198012172005011001', 'Aris Budianto'],
                'tahap' => 'tahap_3_semhas', 'skenario' => 'semhas_baru',
            ],
            [
                'nim' => 'S162508002', 'nama' => "Irba' Rizka Putri",
                'judul' => 'Desain Pembelajaran Kolaboratif pada Program Keahlian Teknik',
                'p1' => ['197805142005012002', 'Indah Widiastuti'],
                'p2' => ['1991022220161001', 'Valiant Lukad'],
                'tahap' => 'tahap_3_semhas', 'skenario' => 'semhas_jadwal',
            ],
            [
                'nim' => 'S162508003', 'nama' => 'Jovanka Ananda Putra',
                'judul' => 'Evaluasi Kompetensi Soft Skill Lulusan Pendidikan Vokasi',
                'p1' => ['tamrin', 'A.G. Tamrin'],
                'p2' => ['197709022005011001', 'Ida Nugroho'],
                'tahap' => 'tahap_4_ujian', 'skenario' => 'ujian_baru',
            ],
            [
                'nim' => 'S162508004', 'nama' => 'Waras Dwi Yhoga',
                'judul' => 'Integrasi Teaching Factory pada Pendidikan Guru Vokasi',
                'p1' => ['198208112006041001', 'Herman Saputro'],
                'p2' => ['196107291991031001', 'Muhammad Akhyar'],
                'tahap' => 'tahap_4_ujian', 'skenario' => 'ujian_jadwal',
            ],
            [
                'nim' => 'S162508005', 'nama' => 'Ma Anwar Yasin, S.Pd.',
                'judul' => 'Analisis Capaian Pembelajaran Lulusan S2 Pendidikan Guru Vokasi',
                'p1' => ['195902011985032002', 'Siswandari'],
                'p2' => ['197606182000031001', 'Taufiq Lilo'],
                'tahap' => 'selesai_yudisium', 'skenario' => 'yudisium',
            ],
            [
                'nim' => 'S162508006', 'nama' => 'Tiara Kusuma Dewi',
                'judul' => 'Pengembangan Instrumen Penilaian Praktik Mengajar Vokasi',
                'p1' => ['197805142005012002', 'Indah Widiastuti'],
                'p2' => ['196401031992031003', 'Sukatiman'],
                'tahap' => 'tahap_1_bimbingan', 'skenario' => 'usulan',
            ],
        ];

        foreach ($rows as $i => $row) {
            $mhs = $this->findUser([$row['nim']], [$row['nama']]);
            if (!$mhs) {
                $this->command?->warn("Mahasiswa {$row['nim']} belum ada. Jalankan MahasiswaBatchSeeder dulu.");
                continue;
            }

            $p1 = $this->resolveDosen($row['p1']);
            $p2 = $this->resolveDosen($row['p2']);
            if (!$p1 || !$p2) {
                $this->command?->warn("Pembimbing {$row['nama']} tidak ditemukan.");
                continue;
            }

            $tesis = PengajuanTesis::updateOrCreate(
                ['mahasiswa_id' => $mhs->id],
                [
                    'judul_tesis' => $row['judul'],
                    'bidang_fokus' => 'Pendidikan Vokasi',
                    'abstrak_rencana' => 'Data dummy demo alur tesis PGV.',
                    'usulan_pembimbing_1_id' => $p1->id,
                    'usulan_pembimbing_2_id' => $p2->id,
                    'pembimbing_1_id' => $row['skenario'] === 'usulan' ? null : $p1->id,
                    'pembimbing_2_id' => $row['skenario'] === 'usulan' ? null : $p2->id,
                    'status_usulan_pembimbing' => $row['skenario'] === 'usulan' ? 'pending' : 'approved',
                    'nomor_sk_pembimbing' => $row['skenario'] === 'usulan' ? null : 'SK/PEM/2026/'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'tanggal_sk_pembimbing' => $row['skenario'] === 'usulan' ? null : Carbon::now()->subMonths(8 - $i),
                    'status_tahap' => $row['tahap'],
                ]
            );

            $this->bersihkanAnak($tesis);

            $ketua = $poolPenguji->first(fn ($d) => $d->id !== $p1->id && $d->id !== $p2->id) ?? $p1;
            $sekre = $poolPenguji->first(fn ($d) => $d->id !== $p1->id && $d->id !== $p2->id && $d->id !== $ketua->id) ?? $p2;
            $aktor = ['komisi' => $komisi, 'kaprodi' => $kaprodi, 'ketua' => $ketua, 'sekretaris' => $sekre];

            $this->bangunSkenario($tesis, $row['skenario'], $aktor);
        }
    }

    private function bangunSkenario(PengajuanTesis $tesis, string $skenario, array $aktor): void
    {
        if ($skenario === 'usulan' || $skenario === 'bimbingan') {
            return;
        }

        $verified = $skenario !== 'sempro_pending';
        $this->buatSempro($tesis, $verified, Carbon::now()->addDays(20)->setTime(9, 0));

        if ($skenario === 'sempro_pending') {
            return;
        }

        if (in_array($skenario, ['sempro_jadwal'], true)) {
            $sidang = $this->buatSidang($tesis, 'sempro', Carbon::now()->addDays(21)->setTime(9, 0), $aktor);
            $this->assignPenguji($sidang, $tesis, $aktor);
            return;
        }

        // lulus sempro
        $sidangSp = $this->buatSidang($tesis, 'sempro', Carbon::now()->subMonths(4)->setTime(9, 0), $aktor);
        $this->assignPenguji($sidangSp, $tesis, $aktor);
        $this->isiNilai($sidangSp, $aktor, 84, 'A-', 'lulus_revisi_ringan');
        $this->buatRevisi($sidangSp, true);

        if ($skenario === 'semhas_baru') {
            $this->buatSemhas($tesis, true, Carbon::now()->addDays(18)->setTime(10, 0));
            return;
        }

        $this->buatSemhas($tesis, true, Carbon::now()->addDays(18)->setTime(10, 0));

        if ($skenario === 'semhas_jadwal') {
            $sidangSh = $this->buatSidang($tesis, 'semhas', Carbon::now()->addDays(19)->setTime(10, 0), $aktor);
            $this->assignPenguji($sidangSh, $tesis, $aktor);
            return;
        }

        $sidangSh = $this->buatSidang($tesis, 'semhas', Carbon::now()->subMonths(2)->setTime(10, 0), $aktor);
        $this->assignPenguji($sidangSh, $tesis, $aktor);
        $this->isiNilai($sidangSh, $aktor, 86, 'A', 'lulus_revisi_ringan');
        $this->buatRevisi($sidangSh, true);

        $this->buatUjian($tesis, true, Carbon::now()->addDays(16)->setTime(8, 0));

        if ($skenario === 'ujian_baru') {
            return;
        }

        $sidangU = $this->buatSidang($tesis, 'ujian', Carbon::now()->addDays($skenario === 'yudisium' ? -20 : 17)->setTime(8, 0), $aktor);
        $this->assignPenguji($sidangU, $tesis, $aktor);

        if ($skenario === 'ujian_jadwal') {
            return;
        }

        $this->isiNilai($sidangU, $aktor, 88, 'A', 'lulus_tanpa_revisi');
        $this->buatRevisi($sidangU, true);
    }

    private function buatSempro(PengajuanTesis $tesis, bool $verified, Carbon $jadwal): void
    {
        PendaftaranSempro::create([
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => $tesis->id,
            'jadwal_usulan_sidang' => $jadwal,
            'naskah_proposal_url' => 'sempro/naskah/demo-'.$tesis->id.'.pdf',
            'bukti_spp_url' => 'sempro/spp/demo-'.$tesis->id.'.pdf',
            'khs_url' => 'sempro/khs/demo-'.$tesis->id.'.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => $verified ? 'verified' : 'pending',
        ]);
    }

    private function buatSemhas(PengajuanTesis $tesis, bool $verified, Carbon $jadwal): void
    {
        PendaftaranSemhas::create([
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => $tesis->id,
            'jadwal_usulan_sidang' => $jadwal,
            'naskah_bab_1_5_url' => 'semhas/naskah/demo-'.$tesis->id.'.pdf',
            'draf_artikel_ilmiah_urls' => ['semhas/artikel/demo-a-'.$tesis->id.'.pdf', 'semhas/artikel/demo-b-'.$tesis->id.'.pdf'],
            'bukti_status_under_review_url' => 'semhas/review/demo-'.$tesis->id.'.pdf',
            'bukti_spp_url' => 'semhas/spp/demo-'.$tesis->id.'.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => $verified ? 'verified' : 'pending',
        ]);
    }

    private function buatUjian(PengajuanTesis $tesis, bool $verified, Carbon $jadwal): void
    {
        $data = [
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => $tesis->id,
            'jadwal_usulan_sidang' => $jadwal,
            'naskah_tesis_lengkap_url' => 'ujian/naskah/demo-'.$tesis->id.'.pdf',
            'artikel_jurnal_url' => 'ujian/jurnal/demo-'.$tesis->id.'.pdf',
            'prosiding_seminar_url' => 'ujian/prosiding/demo-'.$tesis->id.'.pdf',
            'sertifikat_bahasa_url' => 'ujian/bahasa/demo-'.$tesis->id.'.pdf',
            'skor_bahasa' => 500,
            'bukti_spp_terakhir_url' => 'ujian/spp/demo-'.$tesis->id.'.pdf',
            'khs_kumulatif_url' => 'ujian/khs/demo-'.$tesis->id.'.pdf',
            'surat_bebas_plagiasi_url' => 'ujian/plagiasi/demo-'.$tesis->id.'.pdf',
            'similarity_score' => 12.5,
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => true,
            'status_verifikasi_admin' => $verified ? 'verified' : 'pending',
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('pendaftaran_ujians', 'jenis_skor_bahasa')) {
            $data['jenis_skor_bahasa'] = 'TOEFL';
        }
        PendaftaranUjian::create($data);
    }

    private function buatSidang(PengajuanTesis $tesis, string $tahap, Carbon $mulai, array $aktor): AktivitasSidang
    {
        $offsetMenit = (crc32($tesis->id.$tahap) % 40) * 15;
        $mulaiUnik = $mulai->copy()->addMinutes($offsetMenit);
        $ruangList = ['Ruang Seminar FKIP 1', 'Ruang Seminar FKIP 2', 'Ruang Seminar FKIP 3', 'Ruang Sidang Pascasarjana A', 'Ruang Sidang Pascasarjana B'];
        $ruang = $ruangList[crc32($tesis->id.$tahap.$mulaiUnik->timestamp) % count($ruangList)];

        return AktivitasSidang::create([
            'id' => (string) Str::uuid(),
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => $tahap,
            'waktu_mulai' => $mulaiUnik,
            'waktu_selesai' => $mulaiUnik->copy()->addHours(2),
            'ruangan' => $ruang,
            'link_zoom' => 'https://zoom.us/j/pgv'.substr(md5($tesis->id.$tahap), 0, 8),
            'komisi_tesis_id' => $aktor['komisi']?->id,
            'nomor_surat_tugas' => 'ST/'.strtoupper($tahap).'/2026/'.rand(100, 999),
            'nomor_undangan' => 'UND/'.strtoupper($tahap).'/2026/'.rand(100, 999),
            'is_locked' => true,
        ]);
    }

    private function assignPenguji(AktivitasSidang $sidang, PengajuanTesis $tesis, array $aktor): void
    {
        $map = [
            [$aktor['ketua'], 'ketua_penguji'],
            [$aktor['sekretaris'], 'sekretaris_penguji'],
            [$tesis->pembimbing1 ?? $aktor['ketua'], 'pembimbing_1'],
            [$tesis->pembimbing2 ?? $aktor['sekretaris'], 'pembimbing_2'],
        ];
        foreach ($map as [$dosen, $peran]) {
            if (!$dosen) {
                continue;
            }
            PengujiSidang::create([
                'id' => (string) Str::uuid(),
                'sidang_id' => $sidang->id,
                'dosen_id' => $dosen->id,
                'peran_penguji' => $peran,
                'presensi_kehadiran' => true,
            ]);
        }
    }

    private function isiNilai(AktivitasSidang $sidang, array $aktor, float $rata, string $grade, string $keputusan): void
    {
        $sidang->load('pengujiSidangs');
        foreach ($sidang->pengujiSidangs as $p) {
            $data = ['nilai_total_angka' => $rata, 'catatan_revisi' => 'Perkuat bab metodologi.'];
            for ($i = 1; $i <= 10; $i++) {
                $data["nilai_indikator_$i"] = min(100, max(70, $rata + rand(-3, 3)));
            }
            $data['nilai_dimensi_1_naskah'] = $rata;
            $data['nilai_dimensi_2_publikasi'] = $rata;
            $data['nilai_dimensi_3_presentasi'] = $rata;
            $data['nilai_dimensi_4_tanyajawab'] = $rata;
            $p->update($data);
        }

        ManajemenNilaiSidang::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidang->id,
            'komisi_tesis_validator_id' => $aktor['komisi']?->id,
            'nilai_rata_rata' => $rata,
            'grade_kelulusan' => $grade,
            'keputusan_sidang' => $keputusan,
            'batas_waktu_revisi' => Carbon::now()->addWeeks(2),
        ]);
    }

    private function buatRevisi(AktivitasSidang $sidang, bool $sah): void
    {
        RevisiDokumen::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidang->id,
            'naskah_revisi_final_url' => 'revisi/demo-'.$sidang->id.'.pdf',
            'status_approval_semua' => true,
            'pengesahan_kaprodi' => $sah,
            'disahkan_kaprodi_at' => $sah ? Carbon::now()->subDays(3) : null,
        ]);
    }

    private function bersihkanAnak(PengajuanTesis $tesis): void
    {
        $tesis->load('aktivitasSidangs');
        foreach ($tesis->aktivitasSidangs as $s) {
            $s->pengujiSidangs()->delete();
            optional($s->manajemenNilai())->delete();
            optional($s->revisiDokumen)->delete();
            $s->delete();
        }
        optional($tesis->pendaftaranSempro)->delete();
        optional($tesis->pendaftaranSemhas)->delete();
        optional($tesis->pendaftaranUjian)->delete();
    }

    private function resolveDosen(array $hints): ?User
    {
        $user = $this->findUser([$hints[0]], [$hints[1]]);
        if ($user) {
            return $user;
        }
        if ($hints[0] === 'tamrin' || str_contains(strtolower($hints[1]), 'tamrin')) {
            return User::firstOrCreate(
                ['identifier' => '196000001991031001'],
                [
                    'id' => (string) Str::uuid(),
                    'name' => 'Prof. Dr. A.G. Tamrin, M.Pd., M.Si.',
                    'email' => 'tamrin@fkip.uns.ac.id',
                    'password' => Hash::make('user123'),
                    'role' => 'dosen',
                    'kuota_bimbingan_maks' => 8,
                ]
            );
        }
        return User::where('role', 'dosen')->first();
    }

    private function findUser(array $ids, array $names): ?User
    {
        foreach ($ids as $id) {
            if ($id === 'tamrin') {
                continue;
            }
            $u = User::where('identifier', $id)->first();
            if ($u) {
                return $u;
            }
        }
        foreach ($names as $n) {
            $u = User::where('name', 'like', '%'.$n.'%')->first();
            if ($u) {
                return $u;
            }
        }
        return null;
    }
}
