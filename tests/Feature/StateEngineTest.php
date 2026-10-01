<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul 4 — StateEngine: 3 skenario wajib sesuai spesifikasi.
 * Dijalankan di SQLite in-memory (lihat phpunit.xml) — TIDAK PERNAH
 * menyentuh database MySQL produksi.
 */
class StateEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skenario 1: transisi valid berurutan.
     * Tahap 1 (Bimbingan, 2 pembimbing lengkap) -> Tahap 2 (Sempro) harus
     * berhasil, mengubah status_tahap, DAN mencatat satu baris histori
     * dengan actor yang benar.
     */
    public function test_transisi_valid_berurutan_berhasil_dan_tercatat_di_histori(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $tesis = PengajuanTesis::factory()->denganPembimbingLengkap()->create([
            'status_tahap' => 'tahap_1_bimbingan',
        ]);

        $this->assertTrue(LifecycleStateMachine::canTransitionTo($tesis, 'tahap_2_sempro'));

        LifecycleStateMachine::transition($tesis, 'tahap_2_sempro', $komisi);

        $tesis->refresh();
        $this->assertSame('tahap_2_sempro', $tesis->status_tahap);

        $this->assertDatabaseHas('state_transition_log', [
            'pengajuan_tesis_id' => $tesis->id,
            'from_state' => 'tahap_1_bimbingan',
            'to_state' => 'tahap_2_sempro',
            'actor_id' => $komisi->id,
            'is_override' => false,
        ]);
    }

    /**
     * Skenario 2: percobaan lompat tahap HARUS ditolak.
     * Mahasiswa masih di Tahap 1 (bahkan belum punya pembimbing lengkap)
     * tapi mencoba langsung transisi ke Tahap 3 (Semhas) — harus dilempar
     * Exception, status_tahap TIDAK BOLEH berubah, dan TIDAK ADA baris
     * histori baru yang tercatat.
     */
    public function test_percobaan_lompat_tahap_ditolak(): void
    {
        $tesis = PengajuanTesis::factory()->create([
            'status_tahap' => 'tahap_1_bimbingan',
        ]);

        $this->assertFalse(LifecycleStateMachine::canTransitionTo($tesis, 'tahap_3_semhas'));

        $this->expectException(\Exception::class);

        try {
            LifecycleStateMachine::transition($tesis, 'tahap_3_semhas', null);
        } finally {
            $tesis->refresh();
            $this->assertSame('tahap_1_bimbingan', $tesis->status_tahap);
            $this->assertDatabaseCount('state_transition_log', 0);
        }
    }

    /**
     * Skenario 3: percobaan transisi MUNDUR (rollback) oleh user yang
     * BUKAN Komisi Tesis harus ditolak — status TIDAK BOLEH berubah.
     */
    public function test_rollback_tanpa_hak_komisi_tesis_ditolak(): void
    {
        $dosenBiasa = User::factory()->dosen()->create(); // BUKAN komisi_tesis
        $tesis = PengajuanTesis::factory()->denganPembimbingLengkap()->create([
            'status_tahap' => 'tahap_2_sempro',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Rollback/koreksi status hanya boleh dilakukan oleh Komisi Tesis.');

        try {
            LifecycleStateMachine::rollback($tesis, 'tahap_1_bimbingan', $dosenBiasa, 'Percobaan tidak sah');
        } finally {
            $tesis->refresh();
            $this->assertSame('tahap_2_sempro', $tesis->status_tahap);
            $this->assertDatabaseCount('state_transition_log', 0);
        }
    }

    /**
     * Kasus positif tambahan: rollback OLEH Komisi Tesis (dengan alasan)
     * harus berhasil dan tercatat dengan is_override = true.
     */
    public function test_rollback_oleh_komisi_tesis_berhasil_dan_tercatat_sebagai_override(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $tesis = PengajuanTesis::factory()->denganPembimbingLengkap()->create([
            'status_tahap' => 'tahap_2_sempro',
        ]);

        LifecycleStateMachine::rollback($tesis, 'tahap_1_bimbingan', $komisi, 'Kesalahan input jadwal sidang.');

        $tesis->refresh();
        $this->assertSame('tahap_1_bimbingan', $tesis->status_tahap);

        $this->assertDatabaseHas('state_transition_log', [
            'pengajuan_tesis_id' => $tesis->id,
            'to_state' => 'tahap_1_bimbingan',
            'is_override' => true,
            'override_reason' => 'Kesalahan input jadwal sidang.',
        ]);
    }
}
