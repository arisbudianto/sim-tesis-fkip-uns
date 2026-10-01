<?php

namespace Tests\Feature;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul 6 — Sempro: validasi wajib "Tidak bisa submit FPT-TI-09 (revisi)
 * sebelum FPT-TI-03 (nilai) seluruh penguji terisi", ditegakkan lewat
 * rekapNilaiKomisi() yang menolak rekap kalau ada penguji belum menilai.
 */
class RekapitulasiNilaiTest extends TestCase
{
    use RefreshDatabase;

    public function test_rekap_ditolak_jika_ada_penguji_belum_menilai(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $sidang = AktivitasSidang::factory()->create();

        // 3 penguji sudah menilai, 1 penguji (ketua) BELUM.
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(80)->create(['peran_penguji' => 'sekretaris_penguji']);
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(85)->create(['peran_penguji' => 'pembimbing_1']);
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(90)->create(['peran_penguji' => 'pembimbing_2']);
        PengujiSidang::factory()->for($sidang, 'sidang')->create(['peran_penguji' => 'ketua_penguji']); // nilai null

        $this->actingAs($komisi);

        $response = $this->postJson(route('sidang.rekapKomisi', $sidang->id), [
            'komisi_tesis_validator_id' => $komisi->id,
            'keputusan_sidang' => 'lulus_tanpa_revisi',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('manajemen_nilai_sidangs', 0);
    }

    public function test_rekap_berhasil_kalau_semua_penguji_sudah_menilai(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $sidang = AktivitasSidang::factory()->create();

        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(80)->create(['peran_penguji' => 'ketua_penguji']);
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(85)->create(['peran_penguji' => 'sekretaris_penguji']);
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(90)->create(['peran_penguji' => 'pembimbing_1']);
        PengujiSidang::factory()->for($sidang, 'sidang')->sudahDinilai(88)->create(['peran_penguji' => 'pembimbing_2']);

        $this->actingAs($komisi);

        $response = $this->postJson(route('sidang.rekapKomisi', $sidang->id), [
            'komisi_tesis_validator_id' => $komisi->id,
            'keputusan_sidang' => 'lulus_tanpa_revisi',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('manajemen_nilai_sidangs', [
            'sidang_id' => $sidang->id,
            'keputusan_sidang' => 'lulus_tanpa_revisi',
        ]);
    }
}
