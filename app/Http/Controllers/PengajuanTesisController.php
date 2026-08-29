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
     * Role yang berwenang menetapkan/mengubah Pembimbing 1 & 2.
     * Mahasiswa hanya boleh MELIHAT siapa pembimbingnya, tidak mengedit.
     */
    protected function bolehAturPembimbing(?User $user): bool
    {
        return $user && in_array($user->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']);
    }

    /**
     * Role yang berwenang mengedit/menghapus pengajuan tesis setelah dibuat.
     * Mahasiswa hanya boleh membuat pengajuan (store), tidak mengubah/menghapus
     * data yang sudah tersimpan — itu wewenang Komisi Tesis/Kaprodi/Admin Prodi.
     */
    protected function bolehEditPengajuan(?User $user): bool
    {
        return $user && in_array($user->role, ['komisi_tesis', 'kaprodi', 'admin_prodi']);
    }

    /**
     * Form edit pengajuan tesis (hanya untuk data di tahap_1_bimbingan —
     * setelah masuk ke tahap sempro dst, judul/fokus dianggap final).
     */
    public function edit(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2'])->findOrFail($id);

        $user = $request->user();
        if (!$this->bolehEditPengajuan($user)) {
            abort(403, 'Mahasiswa tidak dapat mengubah data pengajuan tesis. Hubungi Komisi Tesis / Admin Program Studi untuk perubahan.');
        }

        $dosens = User::where('role', 'dosen')->get();
        $canEditPembimbing = $this->bolehAturPembimbing($user);

        return view('pengajuan.edit', compact('pengajuan', 'dosens', 'canEditPembimbing'));
    }

    public function update(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::findOrFail($id);

        $user = $request->user();
        if (!$this->bolehEditPengajuan($user)) {
            abort(403, 'Mahasiswa tidak dapat mengubah data pengajuan tesis. Hubungi Komisi Tesis / Admin Program Studi untuk perubahan.');
        }

        if ($pengajuan->status_tahap !== 'tahap_1_bimbingan') {
            $msg = 'Pengajuan tidak bisa diedit lagi karena sudah melewati Tahap 1 (Bimbingan).';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        $canEditPembimbing = $this->bolehAturPembimbing($user);

        $rules = [
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string',
            'abstrak_rencana' => 'nullable|string',
        ];

        // Field pembimbing cuma divalidasi kalau memang role-nya berwenang.
        // Mahasiswa yang somehow mengirim field ini tetap akan diabaikan
        // di bawah, bukan cuma disembunyikan di tampilan.
        if ($canEditPembimbing) {
            $rules['pembimbing_1_id'] = 'nullable|uuid|exists:users,id';
            $rules['pembimbing_2_id'] = 'nullable|uuid|exists:users,id|different:pembimbing_1_id|required_with:pembimbing_1_id';
            $rules['nomor_sk_pembimbing'] = 'nullable|string|required_with:pembimbing_1_id';
            $rules['tanggal_sk_pembimbing'] = 'nullable|date|required_with:pembimbing_1_id';
        }

        $validated = $request->validate($rules);

        $dataUpdate = [
            'judul_tesis' => $validated['judul_tesis'],
            'bidang_fokus' => $validated['bidang_fokus'],
            'abstrak_rencana' => $validated['abstrak_rencana'] ?? null,
        ];

        // Pembimbing bersifat opsional di form ini — cuma diproses & dicek
        // kuota kalau memang diisi (dropdown "-- Pilih Dosen --" tidak dipilih)
        // DAN role user berwenang (Komisi Tesis/Kaprodi/Admin Prodi).
        if ($canEditPembimbing && !empty($validated['pembimbing_1_id'])) {
            if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_1_id'], $id)) {
                $msg = 'Pembimbing 1 melebihi kuota bimbingan!';
                return $request->wantsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->withErrors(['error' => $msg])->withInput();
            }

            if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_2_id'], $id)) {
                $msg = 'Pembimbing 2 melebihi kuota bimbingan!';
                return $request->wantsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->withErrors(['error' => $msg])->withInput();
            }

            $dataUpdate['pembimbing_1_id'] = $validated['pembimbing_1_id'];
            $dataUpdate['pembimbing_2_id'] = $validated['pembimbing_2_id'];
            $dataUpdate['nomor_sk_pembimbing'] = $validated['nomor_sk_pembimbing'];
            $dataUpdate['tanggal_sk_pembimbing'] = $validated['tanggal_sk_pembimbing'];
        }

        $pengajuan->update($dataUpdate);

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

        if (!$this->bolehEditPengajuan($request->user())) {
            $msg = 'Mahasiswa tidak dapat menghapus data pengajuan tesis. Hubungi Komisi Tesis / Admin Program Studi.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : back()->withErrors(['error' => $msg]);
        }

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
        if (!$this->bolehAturPembimbing($request->user())) {
            $msg = 'Anda tidak berwenang menetapkan pembimbing. Hanya Komisi Tesis, Kaprodi, atau Admin Program Studi yang bisa melakukan ini.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : back()->withErrors(['error' => $msg]);
        }

        $validated = $request->validate([
            'pembimbing_1_id' => 'required|uuid|exists:users,id',
            'pembimbing_2_id' => 'required|uuid|exists:users,id|different:pembimbing_1_id',
            'nomor_sk_pembimbing' => 'required|string',
            'tanggal_sk_pembimbing' => 'required|date'
        ]);

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_1_id'], $id)) {
            $msg = 'Pembimbing 1 melebihi kuota bimbingan!';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_2_id'], $id)) {
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
