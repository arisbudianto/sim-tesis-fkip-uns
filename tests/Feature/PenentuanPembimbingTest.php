<?php

namespace Tests\Feature;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul 5 — Penentuan Pembimbing (FR-01): penetapan Pembimbing 1 & 2
 * WAJIB lewat StateEngine (bukan update() langsung) dan WAJIB menolak
 * penetapan yang melebihi kuota bimbingan dosen.
 */
class PenentuanPembimbingTest extends TestCase
{
    use RefreshDatabase;

    public function test_penetapan_pembimbing_berhasil_dan_tercatat_di_histori(): void
    {
        $komisi = User::factory()->komisiTesis()->create();
        $pembimbing1 = User::factory()->dosen()->create();
        $pembimbing2 = User::factory()->dosen()->create();
        $tesis = PengajuanTesis::factory()->create();

        LifecycleStateMachine::tetapkanPembimbing(
            $tesis, $pembimbing1->id, $pembimbing2->id, 'SK/TEST/001', now()->toDateString(), $komisi
        );

        $tesis->refresh();
        $this->assertSame($pembimbing1->id, $tesis->pembimbing_1_id);
        $this->assertSame($pembimbing2->id, $tesis->pembimbing_2_id);

        $this->assertDatabaseHas('state_transition_log', [
            'pengajuan_tesis_id' => $tesis->id,
            'to_state' => 'pembimbing_ditetapkan',
            'actor_id' => $komisi->id,
        ]);
    }

    public function test_penetapan_pembimbing_melebihi_kuota_ditolak(): void
    {
        $komisi = User::factory()->komisiTesis()->create();

        // Dosen dengan kuota maksimum 1, sudah penuh oleh 1 mahasiswa lain.
        $dosenPenuh = User::factory()->dosen()->create(['kuota_bimbingan_maks' => 1]);
        PengajuanTesis::factory()->create([
            'pembimbing_1_id' => $dosenPenuh->id,
            'status_tahap' => 'tahap_2_sempro',
        ]);

        $pembimbing2 = User::factory()->dosen()->create();
        $tesisBaru = PengajuanTesis::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Pembimbing 1 melebihi kuota bimbingan!');

        try {
            LifecycleStateMachine::tetapkanPembimbing(
                $tesisBaru, $dosenPenuh->id, $pembimbing2->id, 'SK/TEST/002', now()->toDateString(), $komisi
            );
        } finally {
            $tesisBaru->refresh();
            $this->assertNull($tesisBaru->pembimbing_1_id);
            $this->assertDatabaseCount('state_transition_log', 0);
        }
    }
}
