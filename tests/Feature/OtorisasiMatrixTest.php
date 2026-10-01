<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Modul 12, Tugas 3 (celah otorisasi): matriks sistematis — mahasiswa
 * (role paling minim wewenang) mencoba mengakses endpoint yang HANYA
 * boleh diakses pengendali akademik. Setiap baris HARUS menghasilkan
 * 403, bukan 200/302/500.
 */
class OtorisasiMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mahasiswa = User::factory()->mahasiswa()->create();
    }

    #[DataProvider('endpointPengendaliAkademikProvider')]
    public function test_mahasiswa_ditolak_403_di_endpoint_pengendali_akademik(string $method, \Closure $routeBuilder): void
    {
        $tesis = PengajuanTesis::factory()->denganPembimbingLengkap()->create();
        $sidang = AktivitasSidang::factory()->create(['pengajuan_tesis_id' => $tesis->id]);
        $sempro = PendaftaranSempro::factory()->for($tesis, 'pengajuanTesis')->create();

        $route = $routeBuilder($tesis, $sidang, $sempro);

        $this->actingAs($this->mahasiswa);
        $response = $this->json($method, $route);

        $response->assertStatus(403);
    }

    public static function endpointPengendaliAkademikProvider(): array
    {
        return [
            'alokasi pembimbing' => ['POST', fn ($tesis) => route('pengajuan.alokasi', $tesis->id)],
            'kuota tersedia dosen' => ['GET', fn () => route('pengajuan.kuotaTersedia')],
            'verifikasi sempro' => ['POST', fn ($tesis, $sidang, $sempro) => route('sempro.verifikasi', $sempro->id)],
            'plotting sempro' => ['POST', fn ($tesis) => route('sempro.plotting', $tesis->id)],
            'plotting semhas' => ['POST', fn ($tesis) => route('semhas.plotting', $tesis->id)],
            'plotting ujian' => ['POST', fn ($tesis) => route('ujian.plotting', $tesis->id)],
            'rekap nilai komisi' => ['POST', fn ($tesis, $sidang) => route('sidang.rekapKomisi', $sidang->id)],
            'hapus pengajuan' => ['DELETE', fn ($tesis) => route('pengajuan.destroy', $tesis->id)],
        ];
    }

    public function test_komisi_tesis_ditolak_403_di_endpoint_khusus_kaprodi(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $sidang = AktivitasSidang::factory()->create();
        $revisi = RevisiDokumen::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidang->id,
            'naskah_revisi_final_url' => '/storage/dummy.pdf',
            'status_approval_semua' => true,
            'pengesahan_kaprodi' => false,
        ]);

        $this->actingAs($komisi);
        $response = $this->postJson(route('revisi.pengesahanKaprodi', $revisi->id));

        $response->assertStatus(403);
    }

    public function test_dosen_yang_bukan_penguji_ditolak_403_input_nilai_sidang_orang_lain(): void
    {
        $dosenLuar = User::factory()->dosen()->create();
        $sidang = AktivitasSidang::factory()->create(['tahap_sidang' => 'ujian']);

        $this->actingAs($dosenLuar);
        $response = $this->postJson(route('sidang.submitNilai', $sidang->id), [
            'nilai_dimensi_1_naskah' => 80,
            'nilai_dimensi_2_publikasi' => 80,
            'nilai_dimensi_3_presentasi' => 80,
            'nilai_dimensi_4_tanyajawab' => 80,
        ]);

        $response->assertStatus(403);
    }

    public function test_endpoint_dashboard_tanpa_login_redirect_ke_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Modul 11 fix2: mahasiswa TIDAK boleh mengajukan pengajuan tesis
     * atas nama mahasiswa lain (celah keamanan yang pernah ditemukan &
     * diperbaiki — mahasiswa_id dulu bisa diisi bebas dari body request).
     */
    public function test_mahasiswa_tidak_bisa_mengajukan_pengajuan_atas_nama_mahasiswa_lain(): void
    {
        $mahasiswaLain = User::factory()->mahasiswa()->create();
        $dosen1 = User::factory()->dosen()->create();
        $dosen2 = User::factory()->dosen()->create();

        $this->actingAs($this->mahasiswa);
        $this->postJson(route('pengajuan.store'), [
            'mahasiswa_id' => $mahasiswaLain->id,
            'judul_tesis' => 'Judul Coba Impersonasi',
            'bidang_fokus' => 'Teknik Elektro',
            'usulan_pembimbing_1_id' => $dosen1->id,
            'usulan_pembimbing_2_id' => $dosen2->id,
            'form_fpt_ti_00' => UploadedFile::fake()->create('fpt-ti-00.pdf', 100, 'application/pdf'),
        ])->assertStatus(201);

        // Pengajuan HARUS tercatat atas nama diri sendiri, BUKAN mahasiswa lain.
        $this->assertDatabaseHas('pengajuan_tesis', [
            'mahasiswa_id' => $this->mahasiswa->id,
            'judul_tesis' => 'Judul Coba Impersonasi',
        ]);
        $this->assertDatabaseMissing('pengajuan_tesis', [
            'mahasiswa_id' => $mahasiswaLain->id,
            'judul_tesis' => 'Judul Coba Impersonasi',
        ]);
    }
}
