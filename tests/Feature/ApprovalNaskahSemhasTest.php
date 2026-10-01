<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul 7 — Semhas: naskah Bab I-V wajib disetujui Pembimbing 1 & 2
 * sebelum Admin Prodi bisa memverifikasi (approve) pendaftaran secara
 * final. Pesan error harus spesifik menyebut pembimbing mana yang belum
 * approve, bukan pesan generik.
 */
class ApprovalNaskahSemhasTest extends TestCase
{
    use RefreshDatabase;

    protected function buatSemhasSiapVerifikasi(): array
    {
        $pembimbing1 = User::factory()->dosen()->create(['name' => 'Dr. Pembimbing Satu']);
        $pembimbing2 = User::factory()->dosen()->create(['name' => 'Dr. Pembimbing Dua']);
        $tesis = PengajuanTesis::factory()->create([
            'pembimbing_1_id' => $pembimbing1->id,
            'pembimbing_2_id' => $pembimbing2->id,
            'status_tahap' => 'tahap_3_semhas',
        ]);
        $semhas = PendaftaranSemhas::factory()->for($tesis, 'pengajuanTesis')->create([
            'approval_pembimbing_1' => false,
            'approval_pembimbing_2' => false,
            'status_verifikasi_admin' => 'pending',
        ]);

        return compact('pembimbing1', 'pembimbing2', 'tesis', 'semhas');
    }

    public function test_admin_tidak_bisa_verifikasi_sebelum_kedua_pembimbing_approve(): void
    {
        $data = $this->buatSemhasSiapVerifikasi();
        $admin = User::factory()->create(['role' => 'admin_prodi']);
        $admin->assignRole('admin_prodi');

        $this->actingAs($admin);

        $response = $this->postJson(route('semhas.verifikasi', $data['semhas']->id), [
            'status_verifikasi_admin' => 'verified',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'Tidak bisa menyetujui pendaftaran: naskah Bab I-V belum disetujui oleh Pembimbing 1 (Dr. Pembimbing Satu) dan Pembimbing 2 (Dr. Pembimbing Dua).']);

        $data['semhas']->refresh();
        $this->assertSame('pending', $data['semhas']->status_verifikasi_admin);
    }

    public function test_pembimbing_lain_tidak_bisa_approve_naskah_mahasiswa_bukan_bimbingannya(): void
    {
        $data = $this->buatSemhasSiapVerifikasi();
        $dosenLain = User::factory()->dosen()->create(); // bukan pembimbing_1/2 tesis ini

        $this->actingAs($dosenLain);

        $response = $this->postJson(route('semhas.approveNaskah', $data['semhas']->id), []);

        $response->assertStatus(403);
        $data['semhas']->refresh();
        $this->assertFalse($data['semhas']->approval_pembimbing_1);
    }

    public function test_pembimbing_1_bisa_approve_naskah_bimbingannya_sendiri(): void
    {
        $data = $this->buatSemhasSiapVerifikasi();

        $this->actingAs($data['pembimbing1']);

        $response = $this->postJson(route('semhas.approveNaskah', $data['semhas']->id), []);

        $response->assertStatus(200);
        $data['semhas']->refresh();
        $this->assertTrue($data['semhas']->approval_pembimbing_1);
        $this->assertFalse($data['semhas']->approval_pembimbing_2);
    }

    public function test_admin_bisa_verifikasi_setelah_kedua_pembimbing_approve(): void
    {
        $data = $this->buatSemhasSiapVerifikasi();
        $data['semhas']->update(['approval_pembimbing_1' => true, 'approval_pembimbing_2' => true]);

        $admin = User::factory()->create(['role' => 'admin_prodi']);
        $admin->assignRole('admin_prodi');
        $this->actingAs($admin);

        $response = $this->postJson(route('semhas.verifikasi', $data['semhas']->id), [
            'status_verifikasi_admin' => 'verified',
        ]);

        $response->assertStatus(200);
        $data['semhas']->refresh();
        $this->assertSame('verified', $data['semhas']->status_verifikasi_admin);
    }
}
