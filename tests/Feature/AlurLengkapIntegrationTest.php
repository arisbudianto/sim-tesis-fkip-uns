<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Modul 12, Tugas 2: Integration test alur PENUH satu mahasiswa — dari
 * pengajuan topik hingga status "Siap Yudisium" (selesai_yudisium),
 * melewati keempat tahap dengan seluruh gate StateEngine, plotting,
 * penilaian, dan revisi dieksekusi lewat HTTP request sungguhan.
 */
class AlurLengkapIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $mahasiswa;
    protected User $pembimbing1;
    protected User $pembimbing2;
    protected User $komisi;
    protected User $admin;
    protected User $kaprodi;
    protected PengajuanTesis $tesis;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->mahasiswa = User::factory()->mahasiswa()->create();
        $this->pembimbing1 = User::factory()->dosen()->create();
        $this->pembimbing2 = User::factory()->dosen()->create();
        $this->komisi = User::factory()->komisiTesis()->create();
        $this->admin = User::factory()->create(['role' => 'admin_prodi']);
        $this->admin->assignRole('admin_prodi');
        $this->kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $this->kaprodi->assignRole('kaprodi');
    }

    public function test_alur_lengkap_dari_pengajuan_topik_hingga_siap_yudisium(): void
    {
        $this->actingAs($this->mahasiswa);
        $this->postJson(route('pengajuan.store'), [
            'judul_tesis' => 'Pengembangan Model Pembelajaran Vokasi',
            'bidang_fokus' => 'Teknik Elektro',
            'usulan_pembimbing_1_id' => $this->pembimbing1->id,
            'usulan_pembimbing_2_id' => $this->pembimbing2->id,
            'form_fpt_ti_00' => UploadedFile::fake()->create('fpt-ti-00.pdf', 100, 'application/pdf'),
        ])->assertStatus(201);

        $this->tesis = PengajuanTesis::where('mahasiswa_id', $this->mahasiswa->id)->firstOrFail();
        $this->assertSame('tahap_1_bimbingan', $this->tesis->status_tahap);

        $this->actingAs($this->komisi);
        $this->postJson(route('pengajuan.alokasi', $this->tesis->id), [
            'pembimbing_1_id' => $this->pembimbing1->id,
            'pembimbing_2_id' => $this->pembimbing2->id,
            'nomor_sk_pembimbing' => 'SK/TEST/001',
            'tanggal_sk_pembimbing' => now()->toDateString(),
        ])->assertStatus(200);

        $this->tesis->refresh();
        $this->assertSame($this->pembimbing1->id, $this->tesis->pembimbing_1_id);
        $this->assertSame('tahap_1_bimbingan', $this->tesis->status_tahap);

        $this->jalankanTahapSidang('sempro');
        $this->tesis->refresh();
        $this->assertSame('tahap_3_semhas', $this->tesis->status_tahap);

        $this->actingAs($this->mahasiswa);
        $this->postJson(route('semhas.store', $this->tesis->id), [
            'jadwal_usulan_sidang' => now()->addDays(15)->toDateString(),
            'form_fpt_sh_01' => UploadedFile::fake()->create('fpt-sh-01.pdf', 100, 'application/pdf'),
            'naskah_bab_1_5' => UploadedFile::fake()->create('naskah.pdf', 500, 'application/pdf'),
            'draf_artikel_ilmiah' => [
                UploadedFile::fake()->create('artikel1.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('artikel2.pdf', 100, 'application/pdf'),
            ],
            'bukti_status_under_review' => UploadedFile::fake()->create('review.pdf', 100, 'application/pdf'),
            'bukti_spp' => UploadedFile::fake()->create('spp.pdf', 100, 'application/pdf'),
        ])->assertStatus(200);

        $this->tesis->refresh();
        $semhas = $this->tesis->pendaftaranSemhas;
        $this->assertNotNull($semhas);

        $this->actingAs($this->pembimbing1);
        $this->postJson(route('semhas.approveNaskah', $semhas->id))->assertStatus(200);
        $this->actingAs($this->pembimbing2);
        $this->postJson(route('semhas.approveNaskah', $semhas->id))->assertStatus(200);

        $this->actingAs($this->admin);
        $this->postJson(route('semhas.verifikasi', $semhas->id), ['status_verifikasi_admin' => 'verified'])
            ->assertStatus(200);

        $this->jalankanTahapSidang('semhas');
        $this->tesis->refresh();
        $this->assertSame('tahap_4_ujian', $this->tesis->status_tahap);

        $this->actingAs($this->mahasiswa);
        $this->postJson(route('ujian.store', $this->tesis->id), [
            'jadwal_usulan_sidang' => now()->addDays(15)->toDateString(),
            'naskah_tesis_lengkap' => UploadedFile::fake()->create('tesis.pdf', 1000, 'application/pdf'),
            'artikel_jurnal' => UploadedFile::fake()->create('jurnal.pdf', 200, 'application/pdf'),
            'prosiding_seminar' => UploadedFile::fake()->create('prosiding.pdf', 200, 'application/pdf'),
            'sertifikat_bahasa' => UploadedFile::fake()->create('toefl.pdf', 100, 'application/pdf'),
            'jenis_skor_bahasa' => 'TOEFL',
            'skor_bahasa' => 500,
            'bukti_spp_terakhir' => UploadedFile::fake()->create('spp.pdf', 100, 'application/pdf'),
            'khs_kumulatif' => UploadedFile::fake()->create('khs.pdf', 100, 'application/pdf'),
            'surat_bebas_plagiasi' => UploadedFile::fake()->create('plagiasi.pdf', 100, 'application/pdf'),
            'similarity_score' => 18.5,
        ])->assertStatus(200);

        $this->tesis->refresh();
        $ujian = $this->tesis->pendaftaranUjian;
        $this->assertNotNull($ujian);

        $this->actingAs($this->pembimbing1);
        $this->postJson(route('ujian.accPembimbing', $ujian->id))->assertStatus(200);
        $this->actingAs($this->pembimbing2);
        $this->postJson(route('ujian.accPembimbing', $ujian->id))->assertStatus(200);

        $pengujiStudi = User::factory()->dosen()->create();
        $pengujiPendidikan = User::factory()->dosen()->create();

        $this->actingAs($this->komisi);
        $this->postJson(route('ujian.plotting', $this->tesis->id), [
            'waktu_mulai' => now()->addDays(60)->toDateTimeString(),
            'waktu_selesai' => now()->addDays(60)->addHours(2)->toDateTimeString(),
            'ruangan' => 'Ruang Ujian Akhir',
            'komisi_tesis_id' => $this->komisi->id,
            'penguji' => [
                ['dosen_id' => $this->pembimbing1->id, 'peran_penguji' => 'pembimbing_1'],
                ['dosen_id' => $this->pembimbing2->id, 'peran_penguji' => 'pembimbing_2'],
                ['dosen_id' => $pengujiStudi->id, 'peran_penguji' => 'penguji_studi'],
                ['dosen_id' => $pengujiPendidikan->id, 'peran_penguji' => 'penguji_pendidikan'],
            ],
        ])->assertStatus(200);

        $sidangUjian = AktivitasSidang::where('pengajuan_tesis_id', $this->tesis->id)->where('tahap_sidang', 'ujian')->firstOrFail();
        $pengujiList = PengujiSidang::where('sidang_id', $sidangUjian->id)->get();
        $this->assertCount(4, $pengujiList);

        foreach ($pengujiList as $ps) {
            $this->actingAs($ps->dosen);
            $this->postJson(route('sidang.submitNilai', $sidangUjian->id), [
                'dosen_id' => $ps->dosen_id,
                'nilai_dimensi_1_naskah' => 85,
                'nilai_dimensi_2_publikasi' => 85,
                'nilai_dimensi_3_presentasi' => 85,
                'nilai_dimensi_4_tanyajawab' => 85,
            ])->assertStatus(200);
        }

        $this->actingAs($this->komisi);
        $this->postJson(route('sidang.rekapKomisi', $sidangUjian->id), [
            'komisi_tesis_validator_id' => $this->komisi->id,
            'keputusan_sidang' => 'lulus_revisi_ringan',
            'batas_waktu_revisi' => now()->addDays(14)->toDateString(),
        ])->assertStatus(200);

        $this->actingAs($this->mahasiswa);
        $this->postJson(route('revisi.submitMatriks', $sidangUjian->id), [
            'naskah_revisi_final_url' => '/storage/revisi/final.pdf',
            'matriks' => $pengujiList->map(fn ($ps) => [
                'dosen_penguji_id' => $ps->dosen_id,
                'uraian_hasil_perbaikan' => 'Sudah diperbaiki sesuai catatan.',
                'bukti_halaman_perbaikan' => 'Halaman 42-45',
            ])->toArray(),
        ])->assertStatus(200);

        $revisi = RevisiDokumen::where('sidang_id', $sidangUjian->id)->firstOrFail();
        $revisiPengujiList = RevisiPenguji::where('revisi_dokumen_id', $revisi->id)->get();
        $this->assertCount(4, $revisiPengujiList);

        foreach ($revisiPengujiList as $rp) {
            $dosen = User::find($rp->dosen_penguji_id);
            $this->actingAs($dosen);
            $this->postJson(route('revisi.accPenguji', $rp->id))->assertStatus(200);
        }

        $revisi->refresh();
        $this->assertTrue($revisi->status_approval_semua);

        $this->actingAs($this->kaprodi);
        $this->postJson(route('revisi.pengesahanKaprodi', $revisi->id))->assertStatus(200);

        $revisi->refresh();
        $this->assertTrue($revisi->pengesahan_kaprodi);

        $this->tesis->refresh();
        $this->assertSame('selesai_yudisium', $this->tesis->status_tahap);

        $this->assertGreaterThanOrEqual(3, $this->tesis->stateTransitionLogs()->count());
    }

    protected function jalankanTahapSidang(string $tahap): void
    {
        $this->tesis->refresh();

        $this->actingAs($this->mahasiswa);
        if ($tahap === 'sempro') {
            $this->postJson(route('sempro.store', $this->tesis->id), [
                'jadwal_usulan_sidang' => now()->addDays(15)->toDateString(),
                'form_fpt_ti_01' => UploadedFile::fake()->create('fpt-ti-01.pdf', 100, 'application/pdf'),
                'naskah_proposal' => UploadedFile::fake()->create('proposal.pdf', 500, 'application/pdf'),
                'bukti_spp' => UploadedFile::fake()->create('spp.pdf', 100, 'application/pdf'),
                'khs' => UploadedFile::fake()->create('khs.pdf', 100, 'application/pdf'),
            ])->assertStatus(200);
        }

        $this->tesis->refresh();
        $pendaftaranRel = $tahap === 'sempro' ? 'pendaftaranSempro' : 'pendaftaranSemhas';
        $pendaftaran = $this->tesis->{$pendaftaranRel};
        $this->assertNotNull($pendaftaran, "Pendaftaran {$tahap} gagal tersimpan.");

        if ($tahap === 'sempro') {
            $this->actingAs($this->admin);
            $this->postJson(route('sempro.verifikasi', $pendaftaran->id), ['status_verifikasi_admin' => 'verified'])
                ->assertStatus(200);
        }

        $ketua = User::factory()->dosen()->create();
        $sekretaris = User::factory()->dosen()->create();

        $this->actingAs($this->komisi);
        $routePlotting = $tahap === 'sempro' ? 'sempro.plotting' : 'semhas.plotting';
        $this->postJson(route($routePlotting, $this->tesis->id), [
            'waktu_mulai' => now()->addDays($tahap === 'sempro' ? 20 : 40)->toDateTimeString(),
            'waktu_selesai' => now()->addDays($tahap === 'sempro' ? 20 : 40)->addHours(2)->toDateTimeString(),
            'ruangan' => "Ruang {$tahap} " . uniqid(),
            'komisi_tesis_id' => $this->komisi->id,
            'penguji' => [
                ['dosen_id' => $ketua->id, 'peran_penguji' => 'ketua_penguji'],
                ['dosen_id' => $sekretaris->id, 'peran_penguji' => 'sekretaris_penguji'],
                ['dosen_id' => $this->pembimbing1->id, 'peran_penguji' => 'pembimbing_1'],
                ['dosen_id' => $this->pembimbing2->id, 'peran_penguji' => 'pembimbing_2'],
            ],
        ])->assertStatus(200);

        $sidang = AktivitasSidang::where('pengajuan_tesis_id', $this->tesis->id)->where('tahap_sidang', $tahap)->firstOrFail();
        $pengujiList = PengujiSidang::where('sidang_id', $sidang->id)->get();
        $this->assertCount(4, $pengujiList);

        foreach ($pengujiList as $ps) {
            $payload = ['dosen_id' => $ps->dosen_id];
            for ($i = 1; $i <= 10; $i++) {
                $payload["nilai_indikator_{$i}"] = 85;
            }
            $this->actingAs($ps->dosen);
            $this->postJson(route('sidang.submitNilai', $sidang->id), $payload)->assertStatus(200);
        }

        $this->actingAs($this->komisi);
        $this->postJson(route('sidang.rekapKomisi', $sidang->id), [
            'komisi_tesis_validator_id' => $this->komisi->id,
            'keputusan_sidang' => 'lulus_revisi_ringan',
            'batas_waktu_revisi' => now()->addDays(14)->toDateString(),
        ])->assertStatus(200);

        $this->actingAs($this->mahasiswa);
        $this->postJson(route('revisi.submitMatriks', $sidang->id), [
            'naskah_revisi_final_url' => "/storage/revisi/{$tahap}-final.pdf",
            'matriks' => $pengujiList->map(fn ($ps) => [
                'dosen_penguji_id' => $ps->dosen_id,
                'uraian_hasil_perbaikan' => 'Sudah diperbaiki.',
                'bukti_halaman_perbaikan' => 'Halaman 10-12',
            ])->toArray(),
        ])->assertStatus(200);

        $revisi = RevisiDokumen::where('sidang_id', $sidang->id)->firstOrFail();
        $revisiPengujiList = RevisiPenguji::where('revisi_dokumen_id', $revisi->id)->get();

        foreach ($revisiPengujiList as $rp) {
            $dosen = User::find($rp->dosen_penguji_id);
            $this->actingAs($dosen);
            $this->postJson(route('revisi.accPenguji', $rp->id))->assertStatus(200);
        }

        $this->actingAs($this->kaprodi);
        $this->postJson(route('revisi.pengesahanKaprodi', $revisi->id))->assertStatus(200);
    }
}
