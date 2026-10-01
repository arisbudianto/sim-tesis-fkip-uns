<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Modul 8 — Ujian Tesis: hard validation EAP/TOEFL, similarity <= 25%,
 * komposisi 4 dewan penguji khusus (2 Pembimbing + Bidang Studi + Bidang
 * Pendidikan), gate persetujuan pembimbing sebelum plotting, dan gateway
 * yudisium 4/4 ACC + pengesahan Kaprodi.
 */
class PendaftaranUjianTesisTest extends TestCase
{
    use RefreshDatabase;

    protected function siapkanTesisTahap4(): PengajuanTesis
    {
        $pembimbing1 = User::factory()->dosen()->create();
        $pembimbing2 = User::factory()->dosen()->create();
        $tesis = PengajuanTesis::factory()->create([
            'pembimbing_1_id' => $pembimbing1->id,
            'pembimbing_2_id' => $pembimbing2->id,
            'status_tahap' => 'tahap_4_ujian',
        ]);

        $komisi = User::factory()->komisiTesis()->create();
        $sidangSemhas = AktivitasSidang::factory()->create([
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => 'semhas',
            'komisi_tesis_id' => $komisi->id,
        ]);
        RevisiDokumen::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidangSemhas->id,
            'naskah_revisi_final_url' => '/storage/dummy.pdf',
            'status_approval_semua' => true,
            'pengesahan_kaprodi' => true,
            'disahkan_kaprodi_at' => now(),
        ]);

        return $tesis->fresh();
    }

    protected function fileValidUjian(): array
    {
        Storage::fake('public');
        return [
            'jadwal_usulan_sidang' => now()->addDays(15)->toDateString(),
            'naskah_tesis_lengkap' => UploadedFile::fake()->create('naskah.pdf', 500, 'application/pdf'),
            'artikel_jurnal' => UploadedFile::fake()->create('artikel.pdf', 200, 'application/pdf'),
            'prosiding_seminar' => UploadedFile::fake()->create('prosiding.pdf', 200, 'application/pdf'),
            'sertifikat_bahasa' => UploadedFile::fake()->create('toefl.pdf', 100, 'application/pdf'),
            'jenis_skor_bahasa' => 'TOEFL',
            'skor_bahasa' => 500,
            'bukti_spp_terakhir' => UploadedFile::fake()->create('spp.pdf', 100, 'application/pdf'),
            'khs_kumulatif' => UploadedFile::fake()->create('khs.pdf', 100, 'application/pdf'),
            'surat_bebas_plagiasi' => UploadedFile::fake()->create('plagiasi.pdf', 100, 'application/pdf'),
            'similarity_score' => 18.5,
        ];
    }

    public function test_toefl_di_bawah_475_ditolak(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        $this->actingAs($tesis->mahasiswa);

        $data = $this->fileValidUjian();
        $data['jenis_skor_bahasa'] = 'TOEFL';
        $data['skor_bahasa'] = 400;

        $response = $this->postJson(route('ujian.store', $tesis->id), $data);

        $response->assertStatus(422);
        $this->assertDatabaseCount('pendaftaran_ujians', 0);
    }

    public function test_eap_65_lolos_meski_di_bawah_475(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        $this->actingAs($tesis->mahasiswa);

        $data = $this->fileValidUjian();
        $data['jenis_skor_bahasa'] = 'EAP';
        $data['skor_bahasa'] = 65;

        $response = $this->postJson(route('ujian.store', $tesis->id), $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pendaftaran_ujians', [
            'pengajuan_tesis_id' => $tesis->id,
            'jenis_skor_bahasa' => 'EAP',
            'skor_bahasa' => 65,
        ]);
    }

    public function test_eap_di_bawah_65_ditolak(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        $this->actingAs($tesis->mahasiswa);

        $data = $this->fileValidUjian();
        $data['jenis_skor_bahasa'] = 'EAP';
        $data['skor_bahasa'] = 60;

        $response = $this->postJson(route('ujian.store', $tesis->id), $data);

        $response->assertStatus(422);
        $this->assertDatabaseCount('pendaftaran_ujians', 0);
    }

    public function test_similarity_di_atas_25_persen_ditolak(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        $this->actingAs($tesis->mahasiswa);

        $data = $this->fileValidUjian();
        $data['similarity_score'] = 30.0;

        $response = $this->postJson(route('ujian.store', $tesis->id), $data);

        $response->assertStatus(422);
        $this->assertDatabaseCount('pendaftaran_ujians', 0);
    }

    public function test_plotting_ditolak_sebelum_kedua_pembimbing_acc(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        PendaftaranUjian::factory()->for($tesis, 'pengajuanTesis')->create([
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => false,
        ]);

        $komisi = User::factory()->komisiTesis()->create();
        $pengujiStudi = User::factory()->dosen()->create();
        $pengujiPendidikan = User::factory()->dosen()->create();
        $this->actingAs($komisi);

        $response = $this->postJson(route('ujian.plotting', $tesis->id), [
            'waktu_mulai' => now()->addDays(20)->toDateTimeString(),
            'waktu_selesai' => now()->addDays(20)->addHours(2)->toDateTimeString(),
            'ruangan' => 'Ruang Sidang A',
            'komisi_tesis_id' => $komisi->id,
            'penguji' => [
                ['dosen_id' => $tesis->pembimbing_1_id, 'peran_penguji' => 'pembimbing_1'],
                ['dosen_id' => $tesis->pembimbing_2_id, 'peran_penguji' => 'pembimbing_2'],
                ['dosen_id' => $pengujiStudi->id, 'peran_penguji' => 'penguji_studi'],
                ['dosen_id' => $pengujiPendidikan->id, 'peran_penguji' => 'penguji_pendidikan'],
            ],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('aktivitas_sidangs', 1);
    }

    public function test_plotting_ujian_dengan_komposisi_lama_ketua_sekretaris_ditolak(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        PendaftaranUjian::factory()->for($tesis, 'pengajuanTesis')->create([
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => true,
        ]);

        $komisi = User::factory()->komisiTesis()->create();
        $dosenLain1 = User::factory()->dosen()->create();
        $dosenLain2 = User::factory()->dosen()->create();
        $this->actingAs($komisi);

        $response = $this->postJson(route('ujian.plotting', $tesis->id), [
            'waktu_mulai' => now()->addDays(20)->toDateTimeString(),
            'waktu_selesai' => now()->addDays(20)->addHours(2)->toDateTimeString(),
            'ruangan' => 'Ruang Sidang A',
            'komisi_tesis_id' => $komisi->id,
            'penguji' => [
                ['dosen_id' => $dosenLain1->id, 'peran_penguji' => 'ketua_penguji'],
                ['dosen_id' => $dosenLain2->id, 'peran_penguji' => 'sekretaris_penguji'],
                ['dosen_id' => $tesis->pembimbing_1_id, 'peran_penguji' => 'pembimbing_1'],
                ['dosen_id' => $tesis->pembimbing_2_id, 'peran_penguji' => 'pembimbing_2'],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_plotting_ujian_dengan_komposisi_benar_berhasil(): void
    {
        $tesis = $this->siapkanTesisTahap4();
        PendaftaranUjian::factory()->for($tesis, 'pengajuanTesis')->create([
            'acc_tertulis_pembimbing_1' => true,
            'acc_tertulis_pembimbing_2' => true,
        ]);

        $komisi = User::factory()->komisiTesis()->create();
        $pengujiStudi = User::factory()->dosen()->create();
        $pengujiPendidikan = User::factory()->dosen()->create();
        $this->actingAs($komisi);

        $response = $this->postJson(route('ujian.plotting', $tesis->id), [
            'waktu_mulai' => now()->addDays(20)->toDateTimeString(),
            'waktu_selesai' => now()->addDays(20)->addHours(2)->toDateTimeString(),
            'ruangan' => 'Ruang Sidang A',
            'komisi_tesis_id' => $komisi->id,
            'penguji' => [
                ['dosen_id' => $tesis->pembimbing_1_id, 'peran_penguji' => 'pembimbing_1'],
                ['dosen_id' => $tesis->pembimbing_2_id, 'peran_penguji' => 'pembimbing_2'],
                ['dosen_id' => $pengujiStudi->id, 'peran_penguji' => 'penguji_studi'],
                ['dosen_id' => $pengujiPendidikan->id, 'peran_penguji' => 'penguji_pendidikan'],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('aktivitas_sidangs', [
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => 'ujian',
        ]);
    }

    public function test_pengesahan_kaprodi_ditolak_kalau_belum_4_dari_4_acc(): void
    {
        $tesis = $this->siapkanTesisTahap4();

        $komisi = User::factory()->komisiTesis()->create();
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $kaprodi->assignRole('kaprodi');

        $sidangUjian = AktivitasSidang::factory()->create([
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => 'ujian',
            'komisi_tesis_id' => $komisi->id,
        ]);

        $revisi = RevisiDokumen::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidangUjian->id,
            'naskah_revisi_final_url' => '/storage/dummy.pdf',
            'status_approval_semua' => false,
            'pengesahan_kaprodi' => false,
        ]);

        // Baru 3 dari 4 penguji ACC — status_approval_semua HARUS tetap false.
        foreach (range(1, 3) as $i) {
            RevisiPenguji::create([
                'id' => (string) Str::uuid(),
                'revisi_dokumen_id' => $revisi->id,
                'dosen_penguji_id' => User::factory()->dosen()->create()->id,
                'uraian_hasil_perbaikan' => 'Sudah diperbaiki',
                'bukti_halaman_perbaikan' => 'hal 10',
                'status_acc' => 'acc',
                'acc_at' => now(),
            ]);
        }

        $this->actingAs($kaprodi);
        $response = $this->postJson(route('revisi.pengesahanKaprodi', $revisi->id));

        $response->assertStatus(422);
        $tesis->refresh();
        $this->assertSame('tahap_4_ujian', $tesis->status_tahap);
    }
}
