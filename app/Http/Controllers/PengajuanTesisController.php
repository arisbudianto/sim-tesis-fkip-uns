<?php

namespace App\Http\Controllers;

use App\Models\PengajuanTesis;
use App\Models\User;
use App\Services\AdvisorQuotaEngine;
use Illuminate\Http\Request;

class PengajuanTesisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => 'required|uuid|exists:users,id',
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string',
            'abstrak_rencana' => 'nullable|string'
        ]);

        // Satu mahasiswa hanya boleh punya satu pengajuan tesis aktif.
        // Kalau mau ganti judul/fokus, arahkan ke edit(), bukan bikin baru.
        $sudahAda = PengajuanTesis::where('mahasiswa_id', $validated['mahasiswa_id'])->exists();
        if ($sudahAda) {
            $msg = 'Mahasiswa ini sudah memiliki pengajuan tesis. Silakan gunakan menu Edit untuk mengubah data, bukan mengajukan baru.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        $pengajuan = PengajuanTesis::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $pengajuan], 201);
        }

        return redirect()->route('dashboard')->with('success', 'Pengajuan tesis berhasil disimpan.');
    }

    /**
     * Form edit pengajuan tesis (hanya untuk data di tahap_1_bimbingan —
     * setelah masuk ke tahap sempro dst, judul/fokus dianggap final).
     */
    public function edit($id)
    {
        $pengajuan = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2'])->findOrFail($id);
        $dosens = User::where('role', 'dosen')->get();
        return view('pengajuan.edit', compact('pengajuan', 'dosens'));
    }

    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::findOrFail($id);

        if ($pengajuan->status_tahap !== 'tahap_1_bimbingan') {
            $msg = 'Pengajuan tidak bisa diedit lagi karena sudah melewati Tahap 1 (Bimbingan).';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        $validated = $request->validate([
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string',
            'abstrak_rencana' => 'nullable|string'
        ]);

        $pengajuan->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $pengajuan]);
        }

        return redirect()->route('dashboard')->with('success', 'Pengajuan tesis berhasil diperbarui.');
    }

    /**
     * Hapus pengajuan tesis — hanya diizinkan selama masih di Tahap 1,
     * supaya data yang sudah ada sidang/nilai/revisi tidak ikut hilang
     * (cascadeOnDelete akan menghapus semua data turunannya).
     */
    public function destroy(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::findOrFail($id);

        if ($pengajuan->status_tahap !== 'tahap_1_bimbingan') {
            $msg = 'Pengajuan tidak bisa dihapus karena sudah melewati Tahap 1 (Bimbingan) dan memiliki data lanjutan (sidang/nilai/revisi).';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        $pengajuan->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Pengajuan tesis berhasil dihapus.']);
        }

        return redirect()->route('dashboard')->with('success', 'Pengajuan tesis berhasil dihapus.');
    }

    public function alokasiPembimbing(Request $request, $id)
    {
        $validated = $request->validate([
            'pembimbing_1_id' => 'required|uuid|exists:users,id',
            'pembimbing_2_id' => 'required|uuid|exists:users,id|different:pembimbing_1_id',
            'nomor_sk_pembimbing' => 'required|string',
            'tanggal_sk_pembimbing' => 'required|date'
        ]);

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_1_id'])) {
            $msg = 'Pembimbing 1 melebihi kuota bimbingan!';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_2_id'])) {
            $msg = 'Pembimbing 2 melebihi kuota bimbingan!';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        $pengajuan = PengajuanTesis::findOrFail($id);
        $pengajuan->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Dosen pembimbing 1 & 2 berhasil dialokasikan', 'data' => $pengajuan]);
        }

        return redirect()->route('dashboard')->with('success', 'Pembimbing 1 & 2 berhasil dialokasikan untuk ' . ($pengajuan->mahasiswa->name ?? 'mahasiswa') . '.');
    }
}
