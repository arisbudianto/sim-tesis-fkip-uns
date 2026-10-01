<?php

namespace Tests\Feature;

use App\Domain\Notifikasi\Models\NotifikasiLog;
use App\Domain\Notifikasi\Models\NotifikasiTemplate;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Modul 9 — Notifikasi Terpusat: template dari database (bukan hardcode),
 * seluruh notifikasi (berhasil MAUPUN gagal) tercatat ke notifikasi_log,
 * dan mekanisme retry otomatis.
 */
class NotifikasiTerpusatTest extends TestCase
{
    use RefreshDatabase;

    protected function buatTemplateUji(): NotifikasiTemplate
    {
        return NotifikasiTemplate::create([
            'key' => 'uji_coba',
            'nama_template' => 'Template Uji Coba',
            'body' => 'Halo {nama}, ini pesan uji untuk {tujuan}.',
            'is_active' => true,
        ]);
    }

    public function test_notifikasi_tercatat_gagal_kalau_template_tidak_ditemukan(): void
    {
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        WhatsAppNotifierService::kirim('template_tidak_ada', $mahasiswa, []);

        $this->assertDatabaseHas('notifikasi_log', [
            'template_key' => 'template_tidak_ada',
            'status' => 'gagal',
        ]);
    }

    public function test_notifikasi_tercatat_gagal_kalau_nomor_wa_kosong(): void
    {
        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => null]);

        WhatsAppNotifierService::kirim('uji_coba', $mahasiswa, ['nama' => 'Budi', 'tujuan' => 'tes']);

        $this->assertDatabaseHas('notifikasi_log', [
            'template_key' => 'uji_coba',
            'status' => 'gagal',
        ]);
        $log = NotifikasiLog::where('template_key', 'uji_coba')->first();
        $this->assertStringContainsString('nomor_wa', $log->error_message);
    }

    public function test_template_di_render_dengan_placeholder_yang_benar(): void
    {
        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        WhatsAppNotifierService::kirim('uji_coba', $mahasiswa, ['nama' => 'Budi Santoso', 'tujuan' => 'sidang']);

        $log = NotifikasiLog::where('template_key', 'uji_coba')->first();
        $this->assertSame('Halo Budi Santoso, ini pesan uji untuk sidang.', $log->pesan_terkirim);
    }

    public function test_notifikasi_berhasil_tercatat_terkirim_saat_gateway_sukses(): void
    {
        Config::set('whatsapp.url', 'https://fake-gateway.test/send');
        Config::set('whatsapp.token', 'fake-token');
        Http::fake(['fake-gateway.test/*' => Http::response(['status' => 'ok'], 200)]);

        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        WhatsAppNotifierService::kirim('uji_coba', $mahasiswa, ['nama' => 'Budi', 'tujuan' => 'tes']);

        $this->assertDatabaseHas('notifikasi_log', [
            'template_key' => 'uji_coba',
            'status' => 'terkirim',
        ]);
    }

    public function test_notifikasi_gagal_tercatat_saat_gateway_error(): void
    {
        Config::set('whatsapp.url', 'https://fake-gateway.test/send');
        Config::set('whatsapp.token', 'fake-token');
        Http::fake(['fake-gateway.test/*' => Http::response(['error' => 'invalid'], 500)]);

        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        WhatsAppNotifierService::kirim('uji_coba', $mahasiswa, ['nama' => 'Budi', 'tujuan' => 'tes']);

        $this->assertDatabaseHas('notifikasi_log', [
            'template_key' => 'uji_coba',
            'status' => 'gagal',
        ]);
    }

    public function test_retry_command_mencoba_ulang_notifikasi_gagal_dan_berhasil(): void
    {
        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        $log = NotifikasiLog::create([
            'penerima_id' => $mahasiswa->id,
            'penerima_nama' => $mahasiswa->name,
            'nomor_tujuan' => $mahasiswa->nomor_wa,
            'channel' => 'whatsapp',
            'template_key' => 'uji_coba',
            'pesan_terkirim' => 'Pesan gagal sebelumnya.',
            'status' => 'gagal',
            'percobaan_ke' => 1,
            'error_message' => 'Simulasi gagal.',
        ]);

        Config::set('whatsapp.url', 'https://fake-gateway.test/send');
        Config::set('whatsapp.token', 'fake-token');
        Http::fake(['fake-gateway.test/*' => Http::response(['status' => 'ok'], 200)]);

        $this->artisan('notifikasi:retry')->assertSuccessful();

        $log->refresh();
        $this->assertSame('terkirim', $log->status);
        $this->assertSame(2, $log->percobaan_ke);
    }

    public function test_retry_command_tidak_mengulang_notifikasi_yang_sudah_capai_batas_maksimal(): void
    {
        $this->buatTemplateUji();
        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);

        $log = NotifikasiLog::create([
            'penerima_id' => $mahasiswa->id,
            'nomor_tujuan' => $mahasiswa->nomor_wa,
            'channel' => 'whatsapp',
            'template_key' => 'uji_coba',
            'pesan_terkirim' => 'Pesan gagal.',
            'status' => 'gagal',
            'percobaan_ke' => 5,
        ]);

        $this->artisan('notifikasi:retry')->assertSuccessful();

        $log->refresh();
        $this->assertSame(5, $log->percobaan_ke);
    }

    public function test_approval_pembimbing_semhas_memicu_notifikasi_ke_mahasiswa(): void
    {
        NotifikasiTemplate::create([
            'key' => 'approval_pembimbing',
            'nama_template' => 'x',
            'body' => 'Halo {nama_mahasiswa}, {nama_pembimbing} approve {tahap_sidang}. {status_lengkap}',
            'is_active' => true,
        ]);

        $mahasiswa = User::factory()->mahasiswa()->create(['nomor_wa' => '081234567890']);
        $pembimbing1 = User::factory()->dosen()->create();
        $pembimbing2 = User::factory()->dosen()->create();
        $tesis = PengajuanTesis::factory()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'pembimbing_1_id' => $pembimbing1->id,
            'pembimbing_2_id' => $pembimbing2->id,
            'status_tahap' => 'tahap_3_semhas',
        ]);
        PendaftaranSemhas::factory()->for($tesis, 'pengajuanTesis')->create();

        $this->actingAs($pembimbing1);
        $semhas = $tesis->pendaftaranSemhas;

        $this->postJson(route('semhas.approveNaskah', $semhas->id))->assertStatus(200);

        $this->assertDatabaseHas('notifikasi_log', [
            'template_key' => 'approval_pembimbing',
            'penerima_id' => $mahasiswa->id,
        ]);
    }
}
