<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranUjian;
use App\Models\PengajuanTesis;
use App\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranUjianController extends Controller
{
    /**
     * FR-07: Pendaftaran Ujian Tesis H-14, ACC 2 Pembimbing & 9 Dokumen Prasyarat
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::findOrFail($pengajuanId);

        // 1. GATE State Machine: mahasiswa wajib sudah berada di tahap_4_ujian,
        //    yang hanya bisa dicapai jika revisi Semhas sudah di-ACC seluruh
        //    penguji DAN disahkan Kaprodi (RevisiDokumenController::pengesahanKaprodi).
        if ($blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_4_ujian')) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $blockReason], 422)
                : back()->withErrors(['error' => $blockReason]);
        }

        // 2. Validasi Input & Hard Constraint Skor Bahasa / Similarity
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_tesis_lengkap_url' => 'required|string',
            'bukti_lulus_semhas_url' => 'nullable|string',
            'artikel_jurnal_url' => 'required|string',
            'prosiding_seminar_url' => 'required|string',
            'sertifikat_bahasa_url' => 'required|string',
            'skor_bahasa' => 'required|integer|min:475',
            'bukti_spp_terakhir_url' => 'required|string',
            'khs_kumulatif_url' => 'required|string',
            'surat_bebas_plagiasi_url' => 'required|string',
            'similarity_score' => 'required|numeric|max:25.00'
        ]);

        // 3. Validasi Batas Waktu Minimal H-14
        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            $msg = 'Pendaftaran Ujian Tesis wajib diajukan minimal H-14.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['jadwal_usulan_sidang' => $msg])->withInput();
        }

        // 4. Simpan / Perbarui Pendaftaran
        $ujian = PendaftaranUjian::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $validated
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $ujian]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Ujian Tesis berhasil diajukan!');
    }
}
