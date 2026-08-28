<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSemhas;
use App\Models\PengajuanTesis;
use App\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranSemhasController extends Controller
{
    /**
     * FR-05: Pendaftaran Seminar Hasil (Semhas) H-14 & Berkas Luaran Publikasi
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::findOrFail($pengajuanId);

        // 1. GATE State Machine: mahasiswa wajib sudah berada di tahap_3_semhas,
        //    yang hanya bisa dicapai jika revisi Sempro sudah di-ACC seluruh
        //    penguji DAN disahkan Kaprodi (RevisiDokumenController::pengesahanKaprodi).
        if ($blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_3_semhas')) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $blockReason], 422)
                : back()->withErrors(['error' => $blockReason]);
        }

        // 2. Validasi Input
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_bab_1_5_url' => 'required|string',
            'draf_artikel_ilmiah_urls' => 'required|array|min:2',
            'bukti_status_under_review_url' => 'required|string',
            'bukti_spp_url' => 'required|string'
        ]);

        // 3. Validasi Batas Waktu Minimal H-14
        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            $msg = 'Pendaftaran Semhas wajib diajukan minimal H-14.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['jadwal_usulan_sidang' => $msg])->withInput();
        }

        // 4. Simpan / Perbarui Pendaftaran
        $semhas = PendaftaranSemhas::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Seminar Hasil (Semhas) berhasil diajukan!');
    }
}
