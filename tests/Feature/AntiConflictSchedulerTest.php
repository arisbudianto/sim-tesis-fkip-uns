<?php

namespace Tests\Feature;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\Sidang\Services\AntiConflictScheduler;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Modul 12, Tugas 1: unit test untuk logic paling kritikal & berisiko
 * tinggi menurut analisis risiko proposal Bab VI — deteksi bentrok
 * jadwal ruangan & dosen.
 */
class AntiConflictSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tidak_ada_konflik_kalau_ruangan_dan_dosen_masih_kosong(): void
    {
        $dosen = User::factory()->dosen()->create();

        $conflicts = AntiConflictScheduler::checkConflict(
            now()->addDays(20)->toDateTimeString(),
            now()->addDays(20)->addHours(2)->toDateTimeString(),
            'Ruang Kosong',
            [$dosen->id]
        );

        $this->assertEmpty($conflicts);
    }

    public function test_konflik_ruangan_terdeteksi_kalau_waktu_bertumpuk(): void
    {
        $waktuMulai = now()->addDays(20)->setTime(9, 0);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Sidang A',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $conflicts = AntiConflictScheduler::checkConflict(
            $waktuMulai->toDateTimeString(),
            $waktuSelesai->toDateTimeString(),
            'Ruang Sidang A',
            []
        );

        $this->assertNotEmpty($conflicts);
        $this->assertStringContainsString('Ruang Sidang A', $conflicts[0]);
    }

    public function test_tidak_ada_konflik_ruangan_kalau_rentang_waktu_tidak_beririsan(): void
    {
        $waktuMulai = now()->addDays(20)->setTime(9, 0);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Sidang B',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $waktuMulaiBaru = $waktuSelesai->copy()->addHours(3);
        $conflicts = AntiConflictScheduler::checkConflict(
            $waktuMulaiBaru->toDateTimeString(),
            $waktuMulaiBaru->copy()->addHours(2)->toDateTimeString(),
            'Ruang Sidang B',
            []
        );

        $this->assertEmpty($conflicts);
    }

    public function test_konflik_dosen_terdeteksi_kalau_dosen_sudah_jadi_penguji_sidang_lain_di_waktu_sama(): void
    {
        $dosen = User::factory()->dosen()->create();
        $waktuMulai = now()->addDays(20)->setTime(9, 0);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        $sidangLain = AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Lain',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);
        PengujiSidang::factory()->for($sidangLain, 'sidang')->create(['dosen_id' => $dosen->id]);

        $conflicts = AntiConflictScheduler::checkConflict(
            $waktuMulai->toDateTimeString(),
            $waktuSelesai->toDateTimeString(),
            'Ruang Berbeda',
            [$dosen->id]
        );

        $this->assertNotEmpty($conflicts);
        // Pesan bentrok sekarang human-readable (nama dosen), bukan UUID.
        $this->assertStringContainsString($dosen->name, $conflicts[array_key_last($conflicts)]);
    }

    public function test_ignore_sidang_id_mengecualikan_sidang_itu_sendiri_saat_edit_jadwal(): void
    {
        $waktuMulai = now()->addDays(20)->setTime(9, 0);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        $sidang = AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Sidang C',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $conflicts = AntiConflictScheduler::checkConflict(
            $waktuMulai->toDateTimeString(),
            $waktuSelesai->toDateTimeString(),
            'Ruang Sidang C',
            [],
            ignoreSidangId: $sidang->id
        );

        $this->assertEmpty($conflicts);
    }

    /**
     * Modul 12, Tugas 3 (uji beban sederhana): simulasi 2 percobaan
     * plotting BERSAMAAN untuk ruangan+waktu yang SAMA — hanya satu yang
     * boleh berhasil. PHPUnit berjalan single-thread (tidak bisa simulasi
     * concurrency HTTP sungguhan), jadi tes ini memverifikasi lapisan
     * pertahanan KEDUA: constraint UNIQUE di level database
     * (unique_ruangan_sidang_waktu) tetap menolak percobaan kedua meski
     * AntiConflictScheduler di level aplikasi entah bagaimana terlewati
     * (race condition antara cek & insert). Untuk uji beban HTTP
     * sungguhan, lihat RUNBOOK-DEPLOYMENT.md (k6/Apache Bench).
     */
    public function test_constraint_unik_database_mencegah_dua_sidang_di_ruangan_waktu_sama(): void
    {
        $waktuMulai = now()->addDays(20)->setTime(9, 0);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Rebutan',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        AktivitasSidang::factory()->create([
            'ruangan' => 'Ruang Rebutan',
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
        ]);
    }
}
