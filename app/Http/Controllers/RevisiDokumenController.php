<?php

namespace App\Http\Controllers;

use App\Models\RevisiDokumen;
use App\Models\RevisiPenguji;
use App\Models\AktivitasSidang;
use App\Services\LifecycleStateMachine;
use Illuminate\Http\Request;

class RevisiDokumenController extends Controller
{
    public function submitMatriks(Request $request, $sidangId)
    {
        // GATE State Machine: mahasiswa hanya boleh submit matriks revisi
        // JIKA sidang ini sudah direkap nilainya oleh Komisi Tesis
        // (PenilaianSidangController::rekapNilaiKomisi) DAN keputusannya
        // bukan 'ujian_ulang' (kalau ujian ulang, tidak ada revisi —
        // mahasiswa wajib mengulang sidang, bukan mengirim revisi).
        $sidang = AktivitasSidang::with('manajemenNilai')->findOrFail($sidangId);

        if (!$sidang->manajemenNilai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Matriks revisi belum bisa diajukan: Komisi Tesis belum merekap nilai & keputusan sidang ini.'
            ], 422);
        }

        if ($sidang->manajemenNilai->keputusan_sidang === 'ujian_ulang') {
            return response()->json([
                'status' => 'error',
                'message' => 'Keputusan sidang adalah Ujian Ulang — mahasiswa wajib mengulang sidang, bukan mengirim matriks revisi.'
            ], 422);
        }

        $validated = $request->validate([
            'naskah_revisi_final_url' => 'required|string',
            'bukti_luaran_final_url' => 'nullable|string',
            'matriks' => 'required|array',
            'matriks.*.dosen_penguji_id' => 'required|uuid|exists:users,id',
            'matriks.*.uraian_hasil_perbaikan' => 'required|string',
            'matriks.*.bukti_halaman_perbaikan' => 'required|string'
        ]);

        $revisi = RevisiDokumen::updateOrCreate(
            ['sidang_id' => $sidangId],
            [
                'naskah_revisi_final_url' => $validated['naskah_revisi_final_url'],
                'bukti_luaran_final_url' => $validated['bukti_luaran_final_url'],
                'status_approval_semua' => false
            ]
        );

        foreach ($validated['matriks'] as $m) {
            RevisiPenguji::updateOrCreate(
                [
                    'revisi_dokumen_id' => $revisi->id,
                    'dosen_penguji_id' => $m['dosen_penguji_id']
                ],
                [
                    'uraian_hasil_perbaikan' => $m['uraian_hasil_perbaikan'],
                    'bukti_halaman_perbaikan' => $m['bukti_halaman_perbaikan'],
                    'status_acc' => 'pending'
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Matriks revisi berhasil diajukan.', 'data' => $revisi->load('revisiPengujis')]);
    }

    public function accPenguji(Request $request, $revisiPengujiId)
    {
        $item = RevisiPenguji::findOrFail($revisiPengujiId);
        $item->update([
            'status_acc' => 'acc',
            'feedback_penguji' => $request->input('feedback_penguji', 'Perbaikan disetujui.'),
            'acc_at' => now()
        ]);

        $revisi = $item->revisiDokumen;
        $totalPenguji = $revisi->revisiPengujis()->count();
        $totalAcc = $revisi->revisiPengujis()->where('status_acc', 'acc')->count();

        if ($totalPenguji > 0 && $totalPenguji === $totalAcc) {
            $revisi->update(['status_approval_semua' => true]);
        }

        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function pengesahanKaprodi(Request $request, $revisiId)
    {
        $revisi = RevisiDokumen::with('sidang.pengajuanTesis')->findOrFail($revisiId);

        if (!$revisi->status_approval_semua) {
            return response()->json(['status' => 'error', 'message' => 'Belum seluruh dewan penguji memberikan ACC revisi.'], 422);
        }

        $revisi->update([
            'pengesahan_kaprodi' => true,
            'disahkan_kaprodi_at' => now()
        ]);

        $tesis = $revisi->sidang->pengajuanTesis;
        $tahap = $revisi->sidang->tahap_sidang;

        if ($tahap === 'sempro') {
            $tesis->update(['status_tahap' => 'tahap_3_semhas']);
        } elseif ($tahap === 'semhas') {
            $tesis->update(['status_tahap' => 'tahap_4_ujian']);
        } elseif ($tahap === 'ujian') {
            $tesis->update(['status_tahap' => 'selesai_yudisium']);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Revisi disahkan Kaprodi. Status tahapan akademik berhasil diperbarui.',
            'data' => $tesis
        ]);
    }
}
