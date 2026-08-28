<?php

namespace App\Http\Controllers;

use App\Models\AktivitasSidang;
use App\Models\PengujiSidang;
use App\Models\PengajuanTesis;
use App\Services\AntiConflictScheduler;
use App\Services\LifecycleStateMachine;
use Illuminate\Http\Request;

class KomisiTesisController extends Controller
{
    public function plottingSempro(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'sempro');
    }

    public function plottingSemhas(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'semhas');
    }

    public function plottingUjian(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'ujian');
    }

    private function createSidangWithPenguji(Request $request, string $pengajuanId, string $tahap)
    {
        // GATE State Machine: Komisi Tesis tidak boleh menjadwalkan sidang
        // untuk tahap yang belum "terbuka" bagi mahasiswa tersebut (mis.
        // menjadwalkan Semhas padahal mahasiswa masih di tahap Sempro).
        $tesis = PengajuanTesis::findOrFail($pengajuanId);
        if ($blockReason = LifecycleStateMachine::blockReasonForSidang($tesis, $tahap)) {
            return response()->json(['status' => 'error', 'message' => $blockReason], 422);
        }

        $validated = $request->validate([
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'ruangan' => 'nullable|string',
            'link_zoom' => 'nullable|string',
            'komisi_tesis_id' => 'required|uuid|exists:users,id',
            'penguji' => 'required|array|min:3',
            'penguji.*.dosen_id' => 'required|uuid|exists:users,id',
            'penguji.*.peran_penguji' => 'required|string'
        ]);

        $dosenIds = array_column($validated['penguji'], 'dosen_id');

        $conflicts = AntiConflictScheduler::checkConflict(
            $validated['waktu_mulai'],
            $validated['waktu_selesai'],
            $validated['ruangan'],
            $dosenIds
        );

        if (!empty($conflicts)) {
            return response()->json(['status' => 'error', 'message' => 'Terjadi bentrok jadwal!', 'conflicts' => $conflicts], 422);
        }

        $sidang = AktivitasSidang::create([
            'pengajuan_tesis_id' => $pengajuanId,
            'tahap_sidang' => $tahap,
            'waktu_mulai' => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'ruangan' => $validated['ruangan'],
            'link_zoom' => $validated['link_zoom'],
            'komisi_tesis_id' => $validated['komisi_tesis_id'],
            'is_locked' => true
        ]);

        foreach ($validated['penguji'] as $p) {
            PengujiSidang::create([
                'sidang_id' => $sidang->id,
                'dosen_id' => $p['dosen_id'],
                'peran_penguji' => $p['peran_penguji']
            ]);
        }

        return response()->json(['status' => 'success', 'message' => "Plotting jadwal & penguji {$tahap} berhasil.", 'data' => $sidang->load('pengujiSidangs')]);
    }
}
