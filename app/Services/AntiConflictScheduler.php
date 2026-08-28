<?php

namespace App\Services;

use App\Models\AktivitasSidang;
use App\Models\PengujiSidang;
use Carbon\Carbon;
use Exception;

class AntiConflictScheduler
{
    public static function checkConflict(string $waktuMulai, string $waktuSelesai, ?string $ruangan, array $dosenIds, ?string $ignoreSidangId = null): array
    {
        $start = Carbon::parse($waktuMulai);
        $end = Carbon::parse($waktuSelesai);
        $conflicts = [];

        if ($ruangan) {
            $roomConflict = AktivitasSidang::where('ruangan', $ruangan)
                ->when($ignoreSidangId, fn($q) => $q->where('id', '!=', $ignoreSidangId))
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('waktu_mulai', [$start, $end])
                      ->orWhereBetween('waktu_selesai', [$start, $end]);
                })->exists();

            if ($roomConflict) {
                $conflicts[] = "Ruangan {$ruangan} sedang digunakan pada rentang waktu tersebut.";
            }
        }

        foreach ($dosenIds as $dosenId) {
            $lecturerConflict = PengujiSidang::where('dosen_id', $dosenId)
                ->whereHas('sidang', function ($q) use ($start, $end, $ignoreSidangId) {
                    $q->when($ignoreSidangId, fn($sq) => $sq->where('id', '!=', $ignoreSidangId))
                      ->where(function ($sq) use ($start, $end) {
                          $sq->whereBetween('waktu_mulai', [$start, $end])
                             ->orWhereBetween('waktu_selesai', [$start, $end]);
                      });
                })->exists();

            if ($lecturerConflict) {
                $conflicts[] = "Dosen (ID: {$dosenId}) memiliki jadwal menguji sidang lain yang bersamaan.";
            }
        }

        return $conflicts;
    }
}
