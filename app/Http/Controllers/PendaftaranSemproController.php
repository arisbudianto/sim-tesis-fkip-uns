<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSempro;
use App\Models\PengajuanTesis;
use App\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PendaftaranSemproController extends Controller
{
    /**
     * Tampilkan form pendaftaran Seminar Proposal (Sempro).
     * Halaman ini sebelumnya blank karena method create() belum pernah dibuat
     * — routes/web.php sudah memanggilnya, tapi controller cuma punya store().
     */
    public function create($pengajuanId)
    {
        $tesis = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'pendaftaranSempro'])
            ->findOrFail($pengajuanId);

        // Tampilkan alasan blokir (kalau ada) di halaman, bukan cuma saat submit,
        // supaya mahasiswa tahu dari awal kenapa belum bisa mendaftar.
        $blockReason = LifecycleStateMachine::canTransitionTo($tesis, 'tahap_2_sempro')
            ? null
            : 'Pendaftaran belum bisa dibuka: Alokasi 2 Pembimbing belum lengkap atau tahapan akademik Anda belum sesuai.';

        return view('sempro.create', compact('tesis', 'blockReason'));
    }

    /**
     * FR-03: Pendaftaran Seminar Proposal (Sempro) H-14 & Unggah Dokumen FPT-TI-01 & 02
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::findOrFail($pengajuanId);

        // 1. Verifikasi Lifecycle State Machine
        if (!LifecycleStateMachine::canTransitionTo($tesis, 'tahap_2_sempro')) {
            $msg = 'Pendaftaran ditolak: Alokasi 2 Pembimbing belum lengkap atau tahapan tidak sesuai.';
            return $request->wantsJson() 
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        // 2. Validasi Input dan Berkas PDF
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_proposal'      => 'nullable|file|mimes:pdf|max:35840', // Maks 35 MB
            'bukti_spp'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'khs'                  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // Fallback string URL jika disubmit via simulasi
            'naskah_proposal_url'  => 'nullable|string',
            'bukti_spp_url'        => 'nullable|string',
            'khs_url'              => 'nullable|string',
        ]);

        // 3. Validasi Batas Waktu Minimal H-14 Kalender
        $minDate = Carbon::now()->addDays(14)->startOfDay();
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            $msg = 'Pendaftaran Sempro wajib diajukan minimal H-14 (14 hari sebelum pelaksanaan sidang).';
            return $request->wantsJson() 
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['jadwal_usulan_sidang' => $msg])->withInput();
        }

        // 4. Proses Penyimpanan Berkas Fisik ke Storage
        $dataPendaftaran = [
            'jadwal_usulan_sidang'     => $validated['jadwal_usulan_sidang'],
            'status_verifikasi_admin'  => 'pending',
        ];

        if ($request->hasFile('naskah_proposal')) {
            $path = $request->file('naskah_proposal')->store('sempro/naskah', 'public');
            $dataPendaftaran['naskah_proposal_url'] = '/storage/' . $path;
        } elseif (!empty($validated['naskah_proposal_url'])) {
            $dataPendaftaran['naskah_proposal_url'] = $validated['naskah_proposal_url'];
        }

        if ($request->hasFile('bukti_spp')) {
            $path = $request->file('bukti_spp')->store('sempro/spp', 'public');
            $dataPendaftaran['bukti_spp_url'] = '/storage/' . $path;
        } elseif (!empty($validated['bukti_spp_url'])) {
            $dataPendaftaran['bukti_spp_url'] = $validated['bukti_spp_url'];
        }

        if ($request->hasFile('khs')) {
            $path = $request->file('khs')->store('sempro/khs', 'public');
            $dataPendaftaran['khs_url'] = '/storage/' . $path;
        } elseif (!empty($validated['khs_url'])) {
            $dataPendaftaran['khs_url'] = $validated['khs_url'];
        }

        // 5. Simpan / Perbarui Data Pendaftaran
        $sempro = PendaftaranSempro::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $dataPendaftaran
        );

        // 6. Transisi Status Tahap Akademik Mahasiswa
        $tesis->update(['status_tahap' => 'tahap_2_sempro']);

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pendaftaran Seminar Proposal berhasil diajukan.',
                'data'    => $sempro
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Seminar Proposal (Sempro) berhasil diajukan!');
    }
}