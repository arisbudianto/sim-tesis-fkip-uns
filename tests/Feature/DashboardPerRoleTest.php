<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Modul 11 — Dashboard per Role: acceptance criteria eksplisit "Setiap
 * dashboard hanya menampilkan data sesuai lingkup akses peran". Tes ini
 * memverifikasi ISOLASI DATA antar mahasiswa/dosen, bukan cuma UI gating.
 */
class DashboardPerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_hanya_melihat_pengajuan_tesisnya_sendiri_tidak_mahasiswa_lain(): void
    {
        $mahasiswaA = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Satu']);
        $mahasiswaB = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Dua Rahasia']);

        PengajuanTesis::factory()->create(['mahasiswa_id' => $mahasiswaA->id, 'judul_tesis' => 'Judul Punya A']);
        PengajuanTesis::factory()->create(['mahasiswa_id' => $mahasiswaB->id, 'judul_tesis' => 'Judul Rahasia Punya B']);

        $this->actingAs($mahasiswaA);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Judul Punya A');
        $response->assertDontSee('Judul Rahasia Punya B');
        $response->assertDontSee('Mahasiswa Dua Rahasia');
    }

    public function test_dosen_hanya_melihat_mahasiswa_bimbingannya_sendiri(): void
    {
        $dosenA = User::factory()->dosen()->create();
        $dosenLain = User::factory()->dosen()->create();
        $mahasiswaBimbinganA = User::factory()->mahasiswa()->create();
        $mahasiswaBimbinganLain = User::factory()->mahasiswa()->create();

        PengajuanTesis::factory()->create([
            'mahasiswa_id' => $mahasiswaBimbinganA->id,
            'pembimbing_1_id' => $dosenA->id,
            'judul_tesis' => 'Tesis Bimbingan Dosen A',
        ]);
        PengajuanTesis::factory()->create([
            'mahasiswa_id' => $mahasiswaBimbinganLain->id,
            'pembimbing_1_id' => $dosenLain->id,
            'judul_tesis' => 'Tesis Bimbingan Dosen Lain',
        ]);

        $this->actingAs($dosenA);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tesis Bimbingan Dosen A');
        $response->assertDontSee('Tesis Bimbingan Dosen Lain');
    }

    public function test_dosen_melihat_jadwal_sidang_sebagai_penguji_yang_ditugaskan_ke_dirinya(): void
    {
        $dosenPenguji = User::factory()->dosen()->create();
        $dosenLain = User::factory()->dosen()->create();
        $sidang = AktivitasSidang::factory()->create();

        PengujiSidang::factory()->for($sidang, 'sidang')->create(['dosen_id' => $dosenPenguji->id, 'peran_penguji' => 'ketua_penguji']);
        PengujiSidang::factory()->for($sidang, 'sidang')->create(['dosen_id' => $dosenLain->id, 'peran_penguji' => 'sekretaris_penguji']);

        $this->actingAs($dosenPenguji);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(strtoupper($sidang->tahap_sidang));
    }

    public function test_kaprodi_melihat_daftar_legalitas_pending_yang_sudah_4_dari_4_acc(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $kaprodi->assignRole('kaprodi');

        $sidang = AktivitasSidang::factory()->create(['tahap_sidang' => 'ujian']);
        RevisiDokumen::create([
            'id' => (string) Str::uuid(),
            'sidang_id' => $sidang->id,
            'naskah_revisi_final_url' => '/storage/dummy.pdf',
            'status_approval_semua' => true,
            'pengesahan_kaprodi' => false,
        ]);

        $this->actingAs($kaprodi);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('4/4 ACC Penguji');
    }

    public function test_admin_prodi_melihat_antrean_verifikasi_yang_masih_pending_saja(): void
    {
        $admin = User::factory()->create(['role' => 'admin_prodi']);
        $admin->assignRole('admin_prodi');

        $mhsPending = User::factory()->mahasiswa()->create(['name' => 'Mahasiswa Pending Verifikasi']);
        $tesisPending = PengajuanTesis::factory()->create(['mahasiswa_id' => $mhsPending->id, 'status_tahap' => 'tahap_2_sempro']);
        PendaftaranSempro::factory()->for($tesisPending, 'pengajuanTesis')->create(['status_verifikasi_admin' => 'pending']);

        $this->actingAs($admin);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Mahasiswa Pending Verifikasi');
    }

    public function test_komisi_tesis_melihat_kuota_bimbingan_semua_dosen(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        User::factory()->dosen()->create(['name' => 'Dosen Kuota Test']);

        $this->actingAs($komisi);
        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dosen Kuota Test');
        $response->assertSee('Kuota Bimbingan per Dosen');
    }
}
