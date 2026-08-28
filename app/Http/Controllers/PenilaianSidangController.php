<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSidang;
use App\Models\PengujiSidang;
use App\Models\ManajemenNilaiSidang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PenilaianSidangController extends Controller
{
    public function submitNilaiPenguji(Request $request, $sidangId)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|uuid|exists:users,id',
            'nilai_dimensi_1_naskah' => 'required|numeric|min:0|max:100',
            'nilai_dimensi_2_publikasi' => 'required|numeric|min:0|max:100',
            'nilai_dimensi_3_presentasi' => 'required|numeric|min:0|max:100',
            'nilai_dimensi_4_tanyajawab' => 'required|numeric|min:0|max:100',
            'catatan_revisi' => 'nullable|string'
        ]);

        $total = ($validated['nilai_dimensi_1_naskah'] + $validated['nilai_dimensi_2_publikasi'] + 
                  $validated['nilai_dimensi_3_presentasi'] + $validated['nilai_dimensi_4_tanyajawab']) / 4;

        $penguji = PengujiSidang::where('sidang_id', $sidangId)
            ->where('dosen_id', $validated['dosen_id'])
            ->firstOrFail();

        $penguji->update([
            'nilai_dimensi_1_naskah' => $validated['nilai_dimensi_1_naskah'],
            'nilai_dimensi_2_publikasi' => $validated['nilai_dimensi_2_publikasi'],
            'nilai_dimensi_3_presentasi' => $validated['nilai_dimensi_3_presentasi'],
            'nilai_dimensi_4_tanyajawab' => $validated['nilai_dimensi_4_tanyajawab'],
            'nilai_total_angka' => $total,
            'catatan_revisi' => $validated['catatan_revisi'],
            'presensi_kehadiran' => true,
            'qr_signature_hash' => Str::random(40)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Nilai penguji tersimpan.', 'data' => $penguji]);
    }

    public function rekapNilaiKomisi(Request $request, $sidangId)
    {
        $validated = $request->validate([
            'komisi_tesis_validator_id' => 'required|uuid|exists:users,id',
            'keputusan_sidang' => 'required|in:lulus_tanpa_revisi,lulus_revisi_ringan,lulus_revisi_berat,ujian_ulang',
            'batas_waktu_revisi' => 'nullable|date'
        ]);

        $sidang = AktivitasSidang::with('pengujiSidangs')->findOrFail($sidangId);
        $avg = $sidang->pengujiSidangs->avg('nilai_total_angka');

        if ($avg >= 85) $grade = 'A';
        elseif ($avg >= 80) $grade = 'A-';
        elseif ($avg >= 75) $grade = 'B+';
        elseif ($avg >= 70) $grade = 'B';
        elseif ($avg >= 65) $grade = 'C+';
        else $grade = 'TIDAK_LULUS';

        $rekap = ManajemenNilaiSidang::updateOrCreate(
            ['sidang_id' => $sidangId],
            [
                'komisi_tesis_validator_id' => $validated['komisi_tesis_validator_id'],
                'nilai_rata_rata' => $avg,
                'grade_kelulusan' => $grade,
                'keputusan_sidang' => $validated['keputusan_sidang'],
                'batas_waktu_revisi' => $validated['batas_waktu_revisi'],
                'qr_bap_hash' => Str::random(40)
            ]
        );

        return response()->json(['status' => 'success', 'message' => 'Rekapitulasi BAP selesai.', 'data' => $rekap]);
    }
}
