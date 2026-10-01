<?php

namespace Database\Seeders;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\Sidang\Models\ManajemenNilaiSidang;
use App\Domain\StateEngine\Models\StateTransitionLog;
use App\Domain\StateEngine\TahapTesis;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * DemoDataSeeder — menghasilkan data bisnis realistis untuk demo end-to-end
 * 4 tahap tesis (Critical fix dari review QA).
 *
 * Asumsi: DatabaseSeeder sudah jalan (users + notifikasi template sudah ada).
 * Seeder ini idempotent: menghapus data bisnis lama milik mahasiswa demo
 * sebelum menulis ulang, sehingga aman dijalankan berulang.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = $this->resolveUsers();

        // Bersihkan data bisnis lama agar idempotent
        $this->cleanupDemoData($users);

        // ─── Mahasiswa 1: Tahap 1 Bimbingan ────────────────────────────────
        $mhs1 = $this->createMahasiswa('S032608002', 'Andi Pratama', 'mhs.andi@student.uns.ac.id');
        $tesis1 = PengajuanTesis::create([
            'id' => Str::uuid(),
            'mahasiswa_id' => $mhs1->id,
            'judul_tesis' => 'Pengembangan Model Pembelajaran Berbasis Proyek untuk Meningkatkan Kompetensi Vokasi',
            'bidang_fokus' => 'Pendidikan Vokasi',
            'abstrak_rencana' => 'Penelitian ini bertujuan mengembangkan model PjBL yang relevan dengan industri.',
            'pembimbing_1_id' => $users['pembimbing1']->id,
            'pembimbing_2_id' => $users['pembimbing2']->id,
            'nomor_sk_pembimbing' => 'SK/PEM/001/2026',
            'tanggal_sk_pembimbing' => Carbon::now()->subMonths(3),
            'status_tahap' => TahapTesis::TAHAP_1_BIMBINGAN->value,
        ]);
        $this->logTransition($tesis1, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);

        // ─── Mahasiswa 2: Tahap 2 Sempro (sudah daftar + approve, sudah diplot) ─
        $mhs2 = $this->createMahasiswa('S032608003', 'Citra Dewi', 'mhs.citra@student.uns.ac.id');
        $tesis2 = PengajuanTesis::create([
            'id' => Str::uuid(),
            'mahasiswa_id' => $mhs2->id,
            'judul_tesis' => 'Efektivitas Media Digital Interaktif dalam Pembelajaran Teknik Otomotif',
            'bidang_fokus' => 'Teknik Otomotif',
            'abstrak_rencana' => 'Kajian efektivitas media interaktif terhadap hasil belajar siswa SMK.',
            'pembimbing_1_id' => $users['pembimbing1']->id,
            'pembimbing_2_id' => $users['pembimbing2']->id,
            'nomor_sk_pembimbing' => 'SK/PEM/002/2026',
            'tanggal_sk_pembimbing' => Carbon::now()->subMonths(4),
            'status_tahap' => TahapTesis::TAHAP_2_SEMPRO->value,
        ]);
        $this->logTransition($tesis2, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);
        $this->logTransition($tesis2, TahapTesis::TAHAP_1_BIMBINGAN->value, TahapTesis::TAHAP_2_SEMPRO->value, $users['komisi']);

        PendaftaranSempro::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis2->id,
            'jadwal_usulan_sidang' => Carbon::now()->addDays(21)->setTime(9, 0),
            'naskah_proposal_url' => 'storage/sempro/naskah/demo-citra-proposal.pdf',
            'bukti_spp_url' => 'storage/sempro/spp/demo-citra-spp.pdf',
            'khs_url' => 'storage/sempro/khs/demo-citra-khs.pdf',
            'form_fpt_ti_01_url' => 'storage/sempro/fpt-ti-01/demo-citra-fpt.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);

        $sidangSempro2 = $this->createSidang($tesis2, 'sempro', $users, Carbon::now()->addDays(21)->setTime(9, 0));
        $this->assignPenguji($sidangSempro2, $users, $tesis2);
        // Belum dinilai (untuk demo "tugas penilaian" dosen)

        // ─── Mahasiswa 3: Tahap 3 Semhas (sudah lulus Sempro + revisi ACC) ──
        $mhs3 = $this->createMahasiswa('S032608004', 'Doni Wijaya', 'mhs.doni@student.uns.ac.id');
        $tesis3 = PengajuanTesis::create([
            'id' => Str::uuid(),
            'mahasiswa_id' => $mhs3->id,
            'judul_tesis' => 'Integrasi Kompetensi Soft Skill dalam Kurikulum Pendidikan Vokasi',
            'bidang_fokus' => 'Kurikulum Vokasi',
            'abstrak_rencana' => 'Analisis integrasi soft skill pada kurikulum SMK dan dampaknya terhadap kesiapan kerja.',
            'pembimbing_1_id' => $users['pembimbing1']->id,
            'pembimbing_2_id' => $users['pembimbing2']->id,
            'nomor_sk_pembimbing' => 'SK/PEM/003/2026',
            'tanggal_sk_pembimbing' => Carbon::now()->subMonths(6),
            'status_tahap' => TahapTesis::TAHAP_3_SEMHAS->value,
        ]);
        $this->logTransition($tesis3, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);
        $this->logTransition($tesis3, TahapTesis::TAHAP_1_BIMBINGAN->value, TahapTesis::TAHAP_2_SEMPRO->value, $users['komisi']);
        $this->logTransition($tesis3, TahapTesis::TAHAP_2_SEMPRO->value, TahapTesis::TAHAP_3_SEMHAS->value, $users['kaprodi']);

        PendaftaranSempro::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis3->id,
            'jadwal_usulan_sidang' => Carbon::now()->subMonths(2)->setTime(10, 0),
            'naskah_proposal_url' => 'storage/sempro/naskah/demo-doni-proposal.pdf',
            'bukti_spp_url' => 'storage/sempro/spp/demo-doni-spp.pdf',
            'khs_url' => 'storage/sempro/khs/demo-doni-khs.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);

        $sidangSempro3 = $this->createSidang($tesis3, 'sempro', $users, Carbon::now()->subMonths(2)->setTime(10, 0));
        $this->assignPenguji($sidangSempro3, $users, $tesis3);
        $this->fillNilai($sidangSempro3, $users, 82.5, 'A-', 'lulus_revisi_ringan');
        $this->createRevisiLengkap($sidangSempro3, $users, true); // sudah ACC semua + Kaprodi

        PendaftaranSemhas::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis3->id,
            'jadwal_usulan_sidang' => Carbon::now()->addDays(18)->setTime(13, 0),
            'naskah_bab_1_5_url' => 'storage/semhas/naskah/demo-doni-bab.pdf',
            'draf_artikel_ilmiah_urls' => [
                'storage/semhas/artikel/demo-doni-artikel1.pdf',
                'storage/semhas/artikel/demo-doni-artikel2.pdf',
            ],
            'bukti_status_under_review_url' => 'storage/semhas/bukti/demo-doni-under-review.pdf',
            'bukti_spp_url' => 'storage/semhas/spp/demo-doni-spp.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);

        $sidangSemhas3 = $this->createSidang($tesis3, 'semhas', $users, Carbon::now()->addDays(18)->setTime(13, 0));
        $this->assignPenguji($sidangSemhas3, $users, $tesis3);
        // Belum dinilai → muncul di "Tugas Sidang Saya" dosen

        // ─── Mahasiswa 4: Tahap 4 Ujian (sudah lulus Semhas + revisi ACC) ──
        $mhs4 = $this->createMahasiswa('S032608005', 'Eka Lestari', 'mhs.eka@student.uns.ac.id');
        $tesis4 = PengajuanTesis::create([
            'id' => Str::uuid(),
            'mahasiswa_id' => $mhs4->id,
            'judul_tesis' => 'Model Kerja Sama SMK dengan Industri dalam Penyiapan Tenaga Kerja Terampil',
            'bidang_fokus' => 'Link and Match',
            'abstrak_rencana' => 'Studi kasus model kemitraan SMK-Industri dan dampaknya terhadap employability lulusan.',
            'pembimbing_1_id' => $users['pembimbing1']->id,
            'pembimbing_2_id' => $users['pembimbing2']->id,
            'nomor_sk_pembimbing' => 'SK/PEM/004/2026',
            'tanggal_sk_pembimbing' => Carbon::now()->subMonths(8),
            'status_tahap' => TahapTesis::TAHAP_4_UJIAN->value,
        ]);
        $this->logTransition($tesis4, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);
        $this->logTransition($tesis4, TahapTesis::TAHAP_1_BIMBINGAN->value, TahapTesis::TAHAP_2_SEMPRO->value, $users['komisi']);
        $this->logTransition($tesis4, TahapTesis::TAHAP_2_SEMPRO->value, TahapTesis::TAHAP_3_SEMHAS->value, $users['kaprodi']);
        $this->logTransition($tesis4, TahapTesis::TAHAP_3_SEMHAS->value, TahapTesis::TAHAP_4_UJIAN->value, $users['kaprodi']);

        // Sempro history
        PendaftaranSempro::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis4->id,
            'jadwal_usulan_sidang' => Carbon::now()->subMonths(5)->setTime(9, 0),
            'naskah_proposal_url' => 'storage/sempro/naskah/demo-eka-proposal.pdf',
            'bukti_spp_url' => 'storage/sempro/spp/demo-eka-spp.pdf',
            'khs_url' => 'storage/sempro/khs/demo-eka-khs.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);
        $sidangSempro4 = $this->createSidang($tesis4, 'sempro', $users, Carbon::now()->subMonths(5)->setTime(9, 0));
        $this->assignPenguji($sidangSempro4, $users, $tesis4);
        $this->fillNilai($sidangSempro4, $users, 85.0, 'A', 'lulus_tanpa_revisi');
        $this->createRevisiLengkap($sidangSempro4, $users, true);

        // Semhas history
        PendaftaranSemhas::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis4->id,
            'jadwal_usulan_sidang' => Carbon::now()->subMonths(2)->setTime(10, 0),
            'naskah_bab_1_5_url' => 'storage/semhas/naskah/demo-eka-bab.pdf',
            'draf_artikel_ilmiah_urls' => [
                'storage/semhas/artikel/demo-eka-artikel1.pdf',
                'storage/semhas/artikel/demo-eka-artikel2.pdf',
            ],
            'bukti_status_under_review_url' => 'storage/semhas/bukti/demo-eka-under-review.pdf',
            'bukti_spp_url' => 'storage/semhas/spp/demo-eka-spp.pdf',
            'approval_pembimbing_1' => true,
            'approval_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);
        $sidangSemhas4 = $this->createSidang($tesis4, 'semhas', $users, Carbon::now()->subMonths(2)->setTime(10, 0));
        $this->assignPenguji($sidangSemhas4, $users, $tesis4);
        $this->fillNilai($sidangSemhas4, $users, 84.0, 'A-', 'lulus_revisi_ringan');
        $this->createRevisiLengkap($sidangSemhas4, $users, true);

        // Ujian aktif
        PendaftaranUjian::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis4->id,
            'jadwal_usulan_sidang' => Carbon::now()->addDays(20)->setTime(9, 0),
            'naskah_tesis_lengkap_url' => 'storage/ujian/naskah/demo-eka-tesis.pdf',
            'artikel_jurnal_url' => 'storage/ujian/artikel/demo-eka-jurnal.pdf',
            'prosiding_seminar_url' => 'storage/ujian/prosiding/demo-eka-prosiding.pdf',
            'sertifikat_bahasa_url' => 'storage/ujian/bahasa/demo-eka-toefl.pdf',
            'jenis_skor_bahasa' => 'TOEFL',
            'skor_bahasa' => 510,
            'bukti_spp_terakhir_url' => 'storage/ujian/spp/demo-eka-spp.pdf',
            'khs_kumulatif_url' => 'storage/ujian/khs/demo-eka-khs.pdf',
            'surat_bebas_plagiasi_url' => 'storage/ujian/plagiasi/demo-eka-turnitin.pdf',
            'similarity_score' => 18.50,
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);
        $sidangUjian4 = $this->createSidang($tesis4, 'ujian', $users, Carbon::now()->addDays(20)->setTime(9, 0));
        $this->assignPenguji($sidangUjian4, $users, $tesis4);
        // Belum dinilai

        // ─── Mahasiswa 5: Selesai Yudisium ─────────────────────────────────
        $mhs5 = $this->createMahasiswa('S032608006', 'Fajar Nugroho', 'mhs.fajar@student.uns.ac.id');
        $tesis5 = PengajuanTesis::create([
            'id' => Str::uuid(),
            'mahasiswa_id' => $mhs5->id,
            'judul_tesis' => 'Pengembangan Instrumen Penilaian Kompetensi Kerja Berbasis Industry 4.0',
            'bidang_fokus' => 'Asesmen Vokasi',
            'abstrak_rencana' => 'Pengembangan dan validasi instrumen asesmen kompetensi untuk SMK berbasis Industry 4.0.',
            'pembimbing_1_id' => $users['pembimbing1']->id,
            'pembimbing_2_id' => $users['pembimbing2']->id,
            'nomor_sk_pembimbing' => 'SK/PEM/005/2026',
            'tanggal_sk_pembimbing' => Carbon::now()->subMonths(10),
            'status_tahap' => TahapTesis::SELESAI_YUDISIUM->value,
        ]);
        $this->logTransition($tesis5, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);
        $this->logTransition($tesis5, TahapTesis::TAHAP_1_BIMBINGAN->value, TahapTesis::TAHAP_2_SEMPRO->value, $users['komisi']);
        $this->logTransition($tesis5, TahapTesis::TAHAP_2_SEMPRO->value, TahapTesis::TAHAP_3_SEMHAS->value, $users['kaprodi']);
        $this->logTransition($tesis5, TahapTesis::TAHAP_3_SEMHAS->value, TahapTesis::TAHAP_4_UJIAN->value, $users['kaprodi']);
        $this->logTransition($tesis5, TahapTesis::TAHAP_4_UJIAN->value, TahapTesis::SELESAI_YUDISIUM->value, $users['kaprodi']);

        // History singkat untuk yudisium (hanya ujian yang detail)
        PendaftaranUjian::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis5->id,
            'jadwal_usulan_sidang' => Carbon::now()->subMonths(1)->setTime(9, 0),
            'naskah_tesis_lengkap_url' => 'storage/ujian/naskah/demo-fajar-tesis.pdf',
            'artikel_jurnal_url' => 'storage/ujian/artikel/demo-fajar-jurnal.pdf',
            'prosiding_seminar_url' => 'storage/ujian/prosiding/demo-fajar-prosiding.pdf',
            'sertifikat_bahasa_url' => 'storage/ujian/bahasa/demo-fajar-eap.pdf',
            'jenis_skor_bahasa' => 'EAP',
            'skor_bahasa' => 72,
            'bukti_spp_terakhir_url' => 'storage/ujian/spp/demo-fajar-spp.pdf',
            'khs_kumulatif_url' => 'storage/ujian/khs/demo-fajar-khs.pdf',
            'surat_bebas_plagiasi_url' => 'storage/ujian/plagiasi/demo-fajar-turnitin.pdf',
            'similarity_score' => 12.30,
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => true,
            'status_verifikasi_admin' => 'verified',
        ]);
        $sidangUjian5 = $this->createSidang($tesis5, 'ujian', $users, Carbon::now()->subMonths(1)->setTime(9, 0));
        $this->assignPenguji($sidangUjian5, $users, $tesis5);
        $this->fillNilai($sidangUjian5, $users, 88.0, 'A', 'lulus_revisi_ringan');
        // Satu revisi masih pending ACC (untuk demo section "Revisi menunggu ACC")
        $this->createRevisiPartialPending($sidangUjian5, $users);

        // Juga seed Budi Santoso (user default) ke tahap 1 agar dashboard tidak kosong
        $budi = User::where('email', 'mhs.budi@student.uns.ac.id')->first();
        if ($budi && !PengajuanTesis::where('mahasiswa_id', $budi->id)->exists()) {
            $tesisBudi = PengajuanTesis::create([
                'id' => Str::uuid(),
                'mahasiswa_id' => $budi->id,
                'judul_tesis' => 'Implementasi Problem-Based Learning pada Mata Pelajaran Produktif SMK',
                'bidang_fokus' => 'Pendidikan Vokasi',
                'abstrak_rencana' => 'Kajian implementasi PBL dan dampaknya terhadap hasil belajar.',
                'pembimbing_1_id' => $users['pembimbing1']->id,
                'pembimbing_2_id' => $users['pembimbing2']->id,
                'nomor_sk_pembimbing' => 'SK/PEM/000/2026',
                'tanggal_sk_pembimbing' => Carbon::now()->subMonths(2),
                'status_tahap' => TahapTesis::TAHAP_1_BIMBINGAN->value,
            ]);
            $this->logTransition($tesisBudi, 'awal', TahapTesis::TAHAP_1_BIMBINGAN->value, $users['komisi']);
        }

        $this->command?->info('DemoDataSeeder selesai: 5+ mahasiswa di berbagai tahap, sidang, nilai, dan revisi siap untuk demo.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function resolveUsers(): array
    {
        return [
            'pembimbing1' => User::where('email', 'herman@fkip.uns.ac.id')->firstOrFail(),
            'pembimbing2' => User::where('email', 'siti.rahma@fkip.uns.ac.id')->firstOrFail(),
            'komisi'      => User::where('email', 'komisi.tesis@fkip.uns.ac.id')->firstOrFail(),
            'kaprodi'     => User::where('email', 'kaprodi.pgv@fkip.uns.ac.id')->firstOrFail(),
            'admin'       => User::where('email', 'admin.pasca@fkip.uns.ac.id')->firstOrFail(),
            'ketua'       => User::where('email', 'ketua.penguji@fkip.uns.ac.id')->firstOrFail(),
            'sekretaris'  => User::where('email', 'sekretaris.penguji@fkip.uns.ac.id')->firstOrFail(),
            'pengujiStudi'=> User::where('email', 'penguji.studi@fkip.uns.ac.id')->firstOrFail(),
            'pengujiPend' => User::where('email', 'penguji.pendidikan@fkip.uns.ac.id')->firstOrFail(),
        ];
    }

    private function cleanupDemoData(array $users): void
    {
        // Hapus hanya data milik mahasiswa demo (identifier S03260800x) agar aman
        $demoIds = User::where('identifier', 'like', 'S03260800%')
            ->orWhere('email', 'like', 'mhs.%@student.uns.ac.id')
            ->pluck('id');

        $tesisIds = PengajuanTesis::whereIn('mahasiswa_id', $demoIds)->pluck('id');

        if ($tesisIds->isEmpty()) {
            return;
        }

        $sidangIds = AktivitasSidang::whereIn('pengajuan_tesis_id', $tesisIds)->pluck('id');

        RevisiPenguji::whereIn('revisi_dokumen_id', function ($q) use ($sidangIds) {
            $q->select('id')->from('revisi_dokumens')->whereIn('sidang_id', $sidangIds);
        })->delete();

        RevisiDokumen::whereIn('sidang_id', $sidangIds)->delete();
        ManajemenNilaiSidang::whereIn('sidang_id', $sidangIds)->delete();
        PengujiSidang::whereIn('sidang_id', $sidangIds)->delete();
        AktivitasSidang::whereIn('id', $sidangIds)->delete();

        PendaftaranSempro::whereIn('pengajuan_tesis_id', $tesisIds)->delete();
        PendaftaranSemhas::whereIn('pengajuan_tesis_id', $tesisIds)->delete();
        PendaftaranUjian::whereIn('pengajuan_tesis_id', $tesisIds)->delete();
        StateTransitionLog::whereIn('pengajuan_tesis_id', $tesisIds)->delete();
        PengajuanTesis::whereIn('id', $tesisIds)->delete();

        // Hapus user mahasiswa demo tambahan (kecuali Budi yang sudah ada di DatabaseSeeder)
        User::whereIn('id', $demoIds)
            ->where('email', '!=', 'mhs.budi@student.uns.ac.id')
            ->delete();
    }

    private function createMahasiswa(string $identifier, string $name, string $email): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'id' => Str::uuid(),
                'name' => $name,
                'identifier' => $identifier,
                'password' => bcrypt('password'),
                'role' => 'mahasiswa',
            ]
        );

        // Pastikan role pivot ada
        if (!$user->userRoles()->where('role', 'mahasiswa')->exists()) {
            $user->userRoles()->create(['role' => 'mahasiswa']);
        }

        return $user;
    }

    private function createSidang(PengajuanTesis $tesis, string $tahap, array $users, Carbon $waktuMulai): AktivitasSidang
    {
        return AktivitasSidang::create([
            'id' => Str::uuid(),
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => $tahap,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuMulai->copy()->addHours(2),
            'ruangan' => $tahap === 'ujian' ? 'Ruang Sidang Pascasarjana A' : 'Ruang Seminar FKIP 2',
            'link_zoom' => 'https://zoom.us/j/demo' . substr(md5($tesis->id . $tahap), 0, 8),
            'komisi_tesis_id' => $users['komisi']->id,
            'nomor_surat_tugas' => 'ST/' . strtoupper($tahap) . '/' . date('Y') . '/' . rand(100, 999),
            'nomor_undangan' => 'UND/' . strtoupper($tahap) . '/' . date('Y') . '/' . rand(100, 999),
            'is_locked' => true,
        ]);
    }

    private function assignPenguji(AktivitasSidang $sidang, array $users, PengajuanTesis $tesis): void
    {
        $mapping = [
            ['dosen' => $users['ketua'], 'peran' => 'ketua_penguji'],
            ['dosen' => $users['sekretaris'], 'peran' => 'sekretaris_penguji'],
            ['dosen' => $users['pembimbing1'], 'peran' => 'pembimbing_1'],
            ['dosen' => $users['pembimbing2'], 'peran' => 'pembimbing_2'],
        ];

        // Untuk variasi, sesekali pakai penguji eksternal
        if (in_array($sidang->tahap_sidang, ['semhas', 'ujian'])) {
            $mapping[0] = ['dosen' => $users['pengujiPend'], 'peran' => 'ketua_penguji'];
            $mapping[1] = ['dosen' => $users['pengujiStudi'], 'peran' => 'sekretaris_penguji'];
        }

        foreach ($mapping as $m) {
            PengujiSidang::create([
                'id' => Str::uuid(),
                'sidang_id' => $sidang->id,
                'dosen_id' => $m['dosen']->id,
                'peran_penguji' => $m['peran'],
                'presensi_kehadiran' => true,
            ]);
        }
    }

    private function fillNilai(AktivitasSidang $sidang, array $users, float $rata, string $grade, string $keputusan): void
    {
        $pengujis = $sidang->pengujiSidangs;

        foreach ($pengujis as $p) {
            $p->update([
                'nilai_dimensi_1_naskah' => $rata + rand(-3, 3),
                'nilai_dimensi_2_publikasi' => $rata + rand(-2, 2),
                'nilai_dimensi_3_presentasi' => $rata + rand(-4, 2),
                'nilai_dimensi_4_tanyajawab' => $rata + rand(-3, 3),
                'nilai_indikator_1' => $rata + rand(-2, 2),
                'nilai_indikator_2' => $rata + rand(-2, 2),
                'nilai_indikator_3' => $rata + rand(-2, 2),
                'nilai_indikator_4' => $rata + rand(-2, 2),
                'nilai_indikator_5' => $rata + rand(-2, 2),
                'nilai_indikator_6' => $rata + rand(-2, 2),
                'nilai_indikator_7' => $rata + rand(-2, 2),
                'nilai_indikator_8' => $rata + rand(-2, 2),
                'nilai_indikator_9' => $rata + rand(-2, 2),
                'nilai_indikator_10' => $rata + rand(-2, 2),
                'nilai_total_angka' => $rata,
                'catatan_revisi' => 'Perbaiki sistematika penulisan dan perkuat landasan teori pada Bab II.',
            ]);
        }

        ManajemenNilaiSidang::create([
            'id' => Str::uuid(),
            'sidang_id' => $sidang->id,
            'komisi_tesis_validator_id' => $users['komisi']->id,
            'nilai_rata_rata' => $rata,
            'grade_kelulusan' => $grade,
            'keputusan_sidang' => $keputusan,
            'batas_waktu_revisi' => Carbon::now()->addWeeks(2),
        ]);
    }

    private function createRevisiLengkap(AktivitasSidang $sidang, array $users, bool $pengesahanKaprodi): void
    {
        $revisi = RevisiDokumen::create([
            'id' => Str::uuid(),
            'sidang_id' => $sidang->id,
            'naskah_revisi_final_url' => 'storage/revisi/demo-naskah-final.pdf',
            'status_approval_semua' => true,
            'pengesahan_kaprodi' => $pengesahanKaprodi,
            'disahkan_kaprodi_at' => $pengesahanKaprodi ? Carbon::now()->subDays(5) : null,
        ]);

        foreach ($sidang->pengujiSidangs as $p) {
            RevisiPenguji::create([
                'id' => Str::uuid(),
                'revisi_dokumen_id' => $revisi->id,
                'dosen_penguji_id' => $p->dosen_id,
                'uraian_hasil_perbaikan' => 'Sudah diperbaiki sesuai catatan penguji pada halaman 12–15 dan 34.',
                'bukti_halaman_perbaikan' => '12-15, 34',
                'status_acc' => 'acc',
                'acc_at' => Carbon::now()->subDays(7),
            ]);
        }
    }

    private function createRevisiPartialPending(AktivitasSidang $sidang, array $users): void
    {
        $revisi = RevisiDokumen::create([
            'id' => Str::uuid(),
            'sidang_id' => $sidang->id,
            'naskah_revisi_final_url' => 'storage/revisi/demo-naskah-pending.pdf',
            'status_approval_semua' => false,
            'pengesahan_kaprodi' => false,
        ]);

        $pengujis = $sidang->pengujiSidangs->values();
        foreach ($pengujis as $i => $p) {
            RevisiPenguji::create([
                'id' => Str::uuid(),
                'revisi_dokumen_id' => $revisi->id,
                'dosen_penguji_id' => $p->dosen_id,
                'uraian_hasil_perbaikan' => $i < 3
                    ? 'Perbaikan sudah dilakukan sesuai saran.'
                    : 'Masih menunggu review penguji.',
                'bukti_halaman_perbaikan' => $i < 3 ? '10, 22' : '-',
                'status_acc' => $i < 3 ? 'acc' : 'pending',
                'acc_at' => $i < 3 ? Carbon::now()->subDays(3) : null,
            ]);
        }
    }

    private function logTransition(PengajuanTesis $tesis, string $from, string $to, User $actor): void
    {
        StateTransitionLog::create([
            'pengajuan_tesis_id' => $tesis->id,
            'from_state' => $from,
            'to_state' => $to,
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'actor_role' => $actor->role,
            'is_override' => false,
            'created_at' => Carbon::now()->subDays(rand(1, 30)),
        ]);
    }
}
