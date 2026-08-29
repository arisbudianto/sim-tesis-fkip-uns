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
     * Tampilkan form pendaftaran Seminar Hasil (Semhas).
     */
    public function create($pengajuanId)
    {
        $tesis = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'pendaftaranSemhas'])
            ->findOrFail($pengajuanId);

        $blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_3_semhas');

        return view('semhas.create', compact('tesis', 'blockReason'));
    }

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

        // 2. Validasi Input & Berkas
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            // FPT-SH-01: Permohonan Seminar Hasil yang sudah ditandatangani
            // Pembimbing 1 & 2 — wajib, PDF saja, maksimal 1MB.
            'form_fpt_sh_01' => 'required|file|mimes:pdf|max:1024',
            'naskah_bab_1_5' => 'required|file|mimes:pdf|max:35840',
            'draf_artikel_ilmiah.*' => 'required|file|mimes:pdf|max:20480',
            'draf_artikel_ilmiah' => 'required|array|min:2',
            'bukti_status_under_review' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'bukti_spp' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // 3. Validasi Batas Waktu Minimal H-14
        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            $msg = 'Pendaftaran Semhas wajib diajukan minimal H-14.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['jadwal_usulan_sidang' => $msg])->withInput();
        }

        // 4. Simpan Berkas Fisik ke Storage
        $pathForm = $request->file('form_fpt_sh_01')->store('semhas/fpt-sh-01', 'public');
        $pathNaskah = $request->file('naskah_bab_1_5')->store('semhas/naskah', 'public');
        $pathReview = $request->file('bukti_status_under_review')->store('semhas/review', 'public');
        $pathSpp = $request->file('bukti_spp')->store('semhas/spp', 'public');

        $artikelUrls = [];
        foreach ($request->file('draf_artikel_ilmiah') as $file) {
            $artikelUrls[] = '/storage/' . $file->store('semhas/artikel', 'public');
        }

        // 5. Simpan / Perbarui Pendaftaran
        $semhas = PendaftaranSemhas::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            [
                'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
                'form_fpt_sh_01_url' => '/storage/' . $pathForm,
                'naskah_bab_1_5_url' => '/storage/' . $pathNaskah,
                'draf_artikel_ilmiah_urls' => $artikelUrls,
                'bukti_status_under_review_url' => '/storage/' . $pathReview,
                'bukti_spp_url' => '/storage/' . $pathSpp,
                'status_verifikasi_admin' => 'pending',
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Seminar Hasil (Semhas) berhasil diajukan!');
    }

    /**
     * Verifikasi pendaftaran Semhas oleh Admin Prodi / Komisi Tesis / Kaprodi.
     */
    public function verifikasi(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, ['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            $msg = 'Anda tidak berwenang memverifikasi pendaftaran Semhas.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : back()->withErrors(['error' => $msg]);
        }

        $validated = $request->validate([
            'status_verifikasi_admin' => 'required|in:verified,rejected',
        ]);

        $semhas = PendaftaranSemhas::findOrFail($id);
        $semhas->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        $pesan = $validated['status_verifikasi_admin'] === 'verified'
            ? 'Pendaftaran Semhas disetujui.'
            : 'Pendaftaran Semhas ditolak.';

        return redirect()->route('dashboard')->with('success', $pesan);
    }
}
