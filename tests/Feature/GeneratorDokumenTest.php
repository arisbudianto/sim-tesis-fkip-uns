<?php

namespace Tests\Feature;

use App\Domain\Dokumen\Models\DokumenCetak;
use App\Domain\Dokumen\Services\DocumentGeneratorService;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Modul 10 — Generator Dokumen: idempotent (re-download tanpa regenerate),
 * versioning saat regenerate dipaksa, dan endpoint verifikasi QR/hash.
 */
class GeneratorDokumenTest extends TestCase
{
    use RefreshDatabase;

    protected function generator(): DocumentGeneratorService
    {
        return app(DocumentGeneratorService::class);
    }

    protected function siapkanSidangDenganPenguji(): AktivitasSidang
    {
        Storage::fake('public');
        $sidang = AktivitasSidang::factory()->create(['tahap_sidang' => 'sempro']);
        foreach (['ketua_penguji', 'sekretaris_penguji', 'pembimbing_1', 'pembimbing_2'] as $peran) {
            PengujiSidang::factory()->for($sidang, 'sidang')->create(['peran_penguji' => $peran]);
        }
        return $sidang->fresh();
    }

    public function test_generate_pertama_kali_membuat_satu_baris_dokumen_cetak(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();

        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);

        $this->assertDatabaseCount('dokumen_cetaks', 1);
        $this->assertDatabaseHas('dokumen_cetaks', [
            'kode_dokumen' => 'SURAT-TUGAS-SEMPRO',
            'dokumentable_id' => $sidang->id,
            'versi' => 1,
        ]);
    }

    public function test_generate_kedua_kali_idempoten_tidak_membuat_baris_baru(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();

        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);
        $hashPertama = DokumenCetak::first()->hash_verifikasi;

        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);

        $this->assertDatabaseCount('dokumen_cetaks', 1);
        $this->assertSame($hashPertama, DokumenCetak::first()->hash_verifikasi);
    }

    public function test_file_pdf_benar_benar_tersimpan_di_storage(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();

        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);

        $dokumen = DokumenCetak::first();
        $this->assertNotNull($dokumen->file_path);
        Storage::disk('public')->assertExists($dokumen->file_path);
    }

    public function test_regenerate_membuat_versi_baru_versi_lama_tetap_ada(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();

        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);
        $versi1 = DokumenCetak::first();

        $this->generator()->regenerate('SURAT-TUGAS-SEMPRO', $sidang->id);

        $this->assertDatabaseCount('dokumen_cetaks', 2);
        $this->assertDatabaseHas('dokumen_cetaks', ['id' => $versi1->id, 'versi' => 1]);
        $this->assertDatabaseHas('dokumen_cetaks', ['kode_dokumen' => 'SURAT-TUGAS-SEMPRO', 'versi' => 2]);

        Storage::disk('public')->assertExists($versi1->file_path);
    }

    public function test_verifikasi_hash_valid_mengembalikan_dokumen_sah(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();
        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);
        $dokumen = DokumenCetak::first();

        $response = $this->get(route('dokumen.verifikasi', $dokumen->hash_verifikasi));

        $response->assertOk();
        $response->assertViewHas('valid', true);
        $response->assertSee($dokumen->kode_dokumen);
    }

    public function test_verifikasi_hash_tidak_dikenal_mengembalikan_tidak_valid(): void
    {
        $response = $this->get(route('dokumen.verifikasi', 'hash-yang-tidak-pernah-ada-di-sistem'));

        $response->assertOk();
        $response->assertViewHas('valid', false);
    }

    public function test_regenerate_hanya_boleh_pengendali_akademik(): void
    {
        $sidang = $this->siapkanSidangDenganPenguji();
        $this->generator()->generate('SURAT-TUGAS-SEMPRO', $sidang->id);

        $mahasiswa = User::factory()->mahasiswa()->create();
        $this->actingAs($mahasiswa);

        $response = $this->get(route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-SEMPRO', 'id' => $sidang->id]) . '?regenerate=1');

        $response->assertForbidden();
        $this->assertDatabaseCount('dokumen_cetaks', 1);
    }
}
