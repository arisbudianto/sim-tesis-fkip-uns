<?php

namespace App\Domain\Sidang\Services;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Models\User;
use Carbon\Carbon;

/**
 * AntiConflictScheduler — deteksi bentrok jadwal real-time saat Komisi Tesis
 * melakukan plotting dewan penguji + ruangan.
 *
 * Cek yang dilakukan:
 * 1. Bentrok ruangan (jika ruangan diisi; Zoom-only / hybrid tanpa ruangan dilewati).
 * 2. Bentrok waktu dosen yang sama lintas SEMUA tahap sidang (sempro/semhas/ujian).
 *
 * Mengembalikan array string pesan error yang actionable (siap ditampilkan ke UI).
 * Array kosong = tidak ada bentrok.
 */
class AntiConflictScheduler
{
    /**
     * @param  string       $waktuMulai
     * @param  string       $waktuSelesai
     * @param  string|null  $ruangan        null / empty = sesi Zoom-only / hybrid tanpa ruang fisik
     * @param  array        $dosenIds       daftar UUID dosen yang akan ditugaskan
     * @param  string|null  $ignoreSidangId abaikan sidang ini (saat edit jadwal yang sudah ada)
     * @return array<int, string>           daftar pesan bentrok (kosong = aman)
     */
    public static function checkConflict(
        string $waktuMulai,
        string $waktuSelesai,
        ?string $ruangan,
        array $dosenIds,
        ?string $ignoreSidangId = null
    ): array {
        $start = Carbon::parse($waktuMulai);
        $end = Carbon::parse($waktuSelesai);
        $conflicts = [];

        // 1. Bentrok ruangan (hanya jika ruangan fisik diisi)
        if ($ruangan && trim($ruangan) !== '') {
            $roomConflict = AktivitasSidang::where('ruangan', $ruangan)
                ->when($ignoreSidangId, fn ($q) => $q->where('id', '!=', $ignoreSidangId))
                ->where(function ($q) use ($start, $end) {
                    $q->where(function ($q2) use ($start, $end) {
                        // Overlap klasik: mulai atau selesai berada di dalam rentang existing
                        $q2->whereBetween('waktu_mulai', [$start, $end])
                           ->orWhereBetween('waktu_selesai', [$start, $end])
                           // atau rentang existing sepenuhnya menelan usulan baru
                           ->orWhere(function ($q3) use ($start, $end) {
                               $q3->where('waktu_mulai', '<=', $start)
                                  ->where('waktu_selesai', '>=', $end);
                           });
                    });
                })
                ->with('pengajuanTesis.mahasiswa')
                ->first();

            if ($roomConflict) {
                $mhs = $roomConflict->pengajuanTesis?->mahasiswa?->name ?? 'mahasiswa lain';
                $tahap = strtoupper($roomConflict->tahap_sidang);
                $waktu = $roomConflict->waktu_mulai?->format('d/m/Y H:i');
                $conflicts[] = "Ruangan \"{$ruangan}\" sudah dipakai untuk sidang {$tahap} mahasiswa {$mhs} pada {$waktu}.";
            }
        }

        // 2. Bentrok dosen (lintas semua tahap)
        foreach ($dosenIds as $dosenId) {
            if (!$dosenId) {
                continue;
            }

            $lecturerConflict = PengujiSidang::where('dosen_id', $dosenId)
                ->whereHas('sidang', function ($q) use ($start, $end, $ignoreSidangId) {
                    $q->when($ignoreSidangId, fn ($sq) => $sq->where('id', '!=', $ignoreSidangId))
                      ->where(function ($sq) use ($start, $end) {
                          $sq->whereBetween('waktu_mulai', [$start, $end])
                             ->orWhereBetween('waktu_selesai', [$start, $end])
                             ->orWhere(function ($sq2) use ($start, $end) {
                                 $sq2->where('waktu_mulai', '<=', $start)
                                     ->where('waktu_selesai', '>=', $end);
                             });
                      });
                })
                ->with(['dosen', 'sidang.pengajuanTesis.mahasiswa'])
                ->first();

            if ($lecturerConflict) {
                $dosenName = $lecturerConflict->dosen?->name ?? 'Dosen';
                $mhs = $lecturerConflict->sidang?->pengajuanTesis?->mahasiswa?->name ?? 'mahasiswa lain';
                $tahap = strtoupper($lecturerConflict->sidang?->tahap_sidang ?? 'sidang');
                $waktu = $lecturerConflict->sidang?->waktu_mulai?->format('d/m/Y H:i') ?? '-';
                $conflicts[] = "{$dosenName} sudah ditugaskan pada sidang {$tahap} mahasiswa {$mhs} di jam yang sama ({$waktu}).";
            }
        }

        return $conflicts;
    }

    /**
     * Convenience: throw Exception jika ada bentrok (untuk dipanggil dari controller).
     */
    public static function assertNoConflict(
        string $waktuMulai,
        string $waktuSelesai,
        ?string $ruangan,
        array $dosenIds,
        ?string $ignoreSidangId = null
    ): void {
        $conflicts = self::checkConflict($waktuMulai, $waktuSelesai, $ruangan, $dosenIds, $ignoreSidangId);
        if (!empty($conflicts)) {
            throw new \Exception(implode(' ', $conflicts));
        }
    }
}
