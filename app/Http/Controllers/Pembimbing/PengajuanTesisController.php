<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Models\User;
use App\Domain\Pembimbing\Services\AdvisorQuotaEngine;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengajuanTesisController extends Controller
{
    /**
     * GET /pengajuan — daftar semua pengajuan tesis (versi halaman
     * standalone dari tabel yang juga muncul di tab Overview dashboard).
     * Route ini sudah terdaftar sejak awal tapi method-nya belum pernah
     * dibuat — akan 500 kalau diakses langsung. Diperbaiki di sini.
     */
    public function index(Request $request)
    {
        $pengajuans = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2'])->latest()->get();

        return view('pembimbing.index', compact('pengajuans'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string',
            'abstrak_rencana' => 'nullable|string',
            // Usulan 2 calon pembimbing (wajib untuk mahasiswa)
            'usulan_pembimbing_1_id' => 'required|uuid|exists:users,id',
            'usulan_pembimbing_2_id' => 'required|uuid|exists:users,id|different:usulan_pembimbing_1_id',
            // Formulir resmi FPT-TI-00 (Permohonan Proposal dan Pembimbing)
            'form_fpt_ti_00' => 'required|file|mimes:pdf|max:5120', // maks 5MB
        ];
        // mahasiswa_id HANYA boleh dipilih bebas oleh pengendali akademik
        // (mis. Admin Prodi menginput data atas nama mahasiswa). Untuk
        // role mahasiswa, field ini diabaikan dari body — dipaksa dari
        // Auth::id() supaya TIDAK BISA mengajukan atas nama mahasiswa lain.
        if ($user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            $rules['mahasiswa_id'] = 'required|uuid|exists:users,id';
            // Pengendali boleh skip usulan & file (mereka langsung alokasi)
            $rules['usulan_pembimbing_1_id'] = 'nullable|uuid|exists:users,id';
            $rules['usulan_pembimbing_2_id'] = 'nullable|uuid|exists:users,id|different:usulan_pembimbing_1_id';
            $rules['form_fpt_ti_00'] = 'nullable|file|mimes:pdf|max:5120';
        }

        $validated = $request->validate($rules);
        $mahasiswaId = $validated['mahasiswa_id'] ?? $user->id;

        // Satu mahasiswa hanya boleh punya satu pengajuan tesis aktif.
        // Kalau mau ganti judul/fokus, arahkan ke edit(), bukan bikin baru.
        $sudahAda = PengajuanTesis::where('mahasiswa_id', $mahasiswaId)->exists();
        if ($sudahAda) {
            $msg = 'Mahasiswa ini sudah memiliki pengajuan tesis. Silakan gunakan menu Edit untuk mengubah data, bukan mengajukan baru.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        $pathFpt = null;
        if ($request->hasFile('form_fpt_ti_00')) {
            $pathFpt = $request->file('form_fpt_ti_00')->store('pengajuan/fpt-ti-00', 'public');
        }

        $pengajuan = PengajuanTesis::create([
            'mahasiswa_id' => $mahasiswaId,
            'judul_tesis' => $validated['judul_tesis'],
            'bidang_fokus' => $validated['bidang_fokus'],
            'abstrak_rencana' => $validated['abstrak_rencana'] ?? null,
            'usulan_pembimbing_1_id' => $validated['usulan_pembimbing_1_id'] ?? null,
            'usulan_pembimbing_2_id' => $validated['usulan_pembimbing_2_id'] ?? null,
            'form_fpt_ti_00_url' => $pathFpt,
            'status_usulan_pembimbing' => !empty($validated['usulan_pembimbing_1_id']) ? 'pending' : null,
        ]);

        AuditLogger::log(
            $user,
            'pengajuan.store',
            'PengajuanTesis',
            $pengajuan->id,
            "Usulan judul & calon pembimbing oleh {$user->name}.",
            [
                'usulan_pembimbing_1_id' => $pengajuan->usulan_pembimbing_1_id,
                'usulan_pembimbing_2_id' => $pengajuan->usulan_pembimbing_2_id,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $pengajuan], 201);
        }

        return redirect()->route('dashboard')->with('success', 'Usulan judul tesis dan calon pembimbing berhasil diajukan. Menunggu keputusan Komisi Tesis.');
    }

    /**
     * Mahasiswa boleh memperbarui usulan pembimbing + PDF jika sebelumnya
     * ditolak Komisi Tesis (status_usulan_pembimbing = rejected).
     */
    public function resubmitUsulan(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::findOrFail($id);
        $user = $request->user();

        if ($pengajuan->mahasiswa_id !== $user->id && !$user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            abort(403, 'Anda tidak berwenang mengubah usulan ini.');
        }

        if ($pengajuan->status_usulan_pembimbing === 'approved' || ($pengajuan->pembimbing_1_id && $pengajuan->pembimbing_2_id)) {
            return back()->withErrors(['error' => 'Usulan sudah disetujui / pembimbing sudah ditetapkan. Tidak bisa diajukan ulang.']);
        }

        $validated = $request->validate([
            'judul_tesis' => 'sometimes|string|max:500',
            'bidang_fokus' => 'sometimes|string',
            'usulan_pembimbing_1_id' => 'required|uuid|exists:users,id',
            'usulan_pembimbing_2_id' => 'required|uuid|exists:users,id|different:usulan_pembimbing_1_id',
            'form_fpt_ti_00' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data = [
            'usulan_pembimbing_1_id' => $validated['usulan_pembimbing_1_id'],
            'usulan_pembimbing_2_id' => $validated['usulan_pembimbing_2_id'],
            'status_usulan_pembimbing' => 'pending',
            'catatan_komisi_usulan' => null,
        ];
        if (!empty($validated['judul_tesis'])) {
            $data['judul_tesis'] = $validated['judul_tesis'];
        }
        if (!empty($validated['bidang_fokus'])) {
            $data['bidang_fokus'] = $validated['bidang_fokus'];
        }

        if ($request->hasFile('form_fpt_ti_00')) {
            if ($pengajuan->form_fpt_ti_00_url) {
                Storage::disk('public')->delete($pengajuan->form_fpt_ti_00_url);
            }
            $data['form_fpt_ti_00_url'] = $request->file('form_fpt_ti_00')->store('pengajuan/fpt-ti-00', 'public');
        }

        $pengajuan->update($data);

        AuditLogger::log(
            $user,
            'pengajuan.resubmitUsulan',
            'PengajuanTesis',
            $pengajuan->id,
            'Mahasiswa mengajukan ulang usulan pembimbing setelah ditolak.',
            $data
        );

        return redirect()->route('dashboard')->with('success', 'Usulan pembimbing berhasil diajukan ulang. Menunggu keputusan Komisi Tesis.');
    }

    /**
     * Komisi Tesis menolak usulan calon pembimbing dari mahasiswa.
     * Mahasiswa dapat mengajukan ulang (resubmitUsulan).
     */
    public function tolakUsulan(Request $request, $id)
    {
        if (!$this->bolehAturPembimbing($request->user())) {
            return back()->withErrors(['error' => 'Anda tidak berwenang menolak usulan pembimbing.']);
        }

        $validated = $request->validate([
            'catatan_komisi_usulan' => 'required|string|min:10|max:1000',
        ]);

        $pengajuan = PengajuanTesis::findOrFail($id);
        $pengajuan->update([
            'status_usulan_pembimbing' => 'rejected',
            'catatan_komisi_usulan' => $validated['catatan_komisi_usulan'],
        ]);

        AuditLogger::log(
            $request->user(),
            'pengajuan.tolakUsulan',
            'PengajuanTesis',
            $pengajuan->id,
            "Usulan pembimbing ditolak untuk {$pengajuan->mahasiswa?->name}.",
            ['catatan' => $validated['catatan_komisi_usulan']]
        );

        return redirect()->route('dashboard')->with('success', 'Usulan pembimbing ditolak. Mahasiswa dapat mengajukan ulang.');
    }

    /**
     * Role yang berwenang menetapkan/mengubah Pembimbing 1 & 2.
     * Mahasiswa hanya boleh MELIHAT siapa pembimbingnya, tidak mengedit.
     */
    protected function bolehAturPembimbing(?User $user): bool
    {
        return $user && $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
    }

    /**
     * Role yang berwenang mengedit/menghapus pengajuan tesis setelah dibuat.
     * Mahasiswa hanya boleh membuat pengajuan (store), tidak mengubah/menghapus
     * data yang sudah tersimpan — itu wewenang Komisi Tesis/Kaprodi/Admin Prodi.
     */
    protected function bolehEditPengajuan(?User $user): bool
    {
        return $user && $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
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

        return view('pembimbing.edit', compact('pengajuan', 'dosens', 'canEditPembimbing'));
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

        // Judul/fokus/abstrak: field milik controller ini (tidak melibatkan
        // StateEngine karena tidak mengubah status/pembimbing).
        $pengajuan->update($dataUpdate);

        // Pembimbing bersifat opsional di form ini — cuma diproses lewat
        // StateEngine (LifecycleStateMachine::tetapkanPembimbing) kalau
        // memang diisi (dropdown "-- Pilih Dosen --" tidak dipilih) DAN
        // role user berwenang (Komisi Tesis/Kaprodi/Admin Prodi). WAJIB
        // lewat StateEngine — bukan $pengajuan->update() manual — supaya
        // tercatat di state_transition_log (acceptance criteria Modul 4/5).
        if ($canEditPembimbing && !empty($validated['pembimbing_1_id'])) {
            try {
                LifecycleStateMachine::tetapkanPembimbing(
                    $pengajuan,
                    $validated['pembimbing_1_id'],
                    $validated['pembimbing_2_id'],
                    $validated['nomor_sk_pembimbing'],
                    $validated['tanggal_sk_pembimbing'],
                    $user
                );
            } catch (\Exception $e) {
                return $request->wantsJson()
                    ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 422)
                    : back()->withErrors(['error' => $e->getMessage()])->withInput();
            }

            AuditLogger::log(
                $user,
                'pengajuan.alokasiPembimbing',
                'PengajuanTesis',
                $pengajuan->id,
                "Alokasi Pembimbing 1 & 2 untuk {$pengajuan->mahasiswa?->name}.",
                ['pembimbing_1_id' => $validated['pembimbing_1_id'], 'pembimbing_2_id' => $validated['pembimbing_2_id']]
            );

            if ($request->wantsJson()) {
                return response()->json(['status' => 'success', 'data' => $pengajuan]);
            }

            return redirect()->route('dashboard')->with('success', 'Pengajuan tesis berhasil diperbarui.');
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $pengajuan]);
        }

        return redirect()->route('dashboard')->with('success', 'Pengajuan tesis berhasil diperbarui.');
    }

    /**
     * Laporan histori transisi status (Modul 4, acceptance criteria kedua):
     * "Ada laporan histori transisi per mahasiswa yang bisa diaudit."
     */
    public function historiStatus(Request $request, $id)
    {
        $pengajuan = PengajuanTesis::with(['mahasiswa', 'stateTransitionLogs'])->findOrFail($id);

        $this->authorize('view', $pengajuan);

        return view('pembimbing.histori-status', compact('pengajuan'));
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

        $pengajuan = PengajuanTesis::findOrFail($id);

        try {
            LifecycleStateMachine::tetapkanPembimbing(
                $pengajuan,
                $validated['pembimbing_1_id'],
                $validated['pembimbing_2_id'],
                $validated['nomor_sk_pembimbing'],
                $validated['tanggal_sk_pembimbing'],
                $request->user()
            );
        } catch (\Exception $e) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $e->getMessage()], 422)
                : back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        // Tandai usulan mahasiswa sebagai disetujui (meski Komisi boleh
        // menetapkan dosen yang berbeda dari usulan).
        $pengajuan->update([
            'status_usulan_pembimbing' => 'approved',
            'catatan_komisi_usulan' => $request->input('catatan_komisi_usulan'),
        ]);

        AuditLogger::log(
            $request->user(),
            'pengajuan.alokasiPembimbing',
            'PengajuanTesis',
            $pengajuan->id,
            "Alokasi Pembimbing 1 & 2 untuk {$pengajuan->mahasiswa?->name} (usulan mahasiswa disetujui/ditetapkan).",
            ['pembimbing_1_id' => $validated['pembimbing_1_id'], 'pembimbing_2_id' => $validated['pembimbing_2_id']]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Dosen pembimbing 1 & 2 berhasil dialokasikan', 'data' => $pengajuan]);
        }

        return redirect()->route('dashboard')->with('success', 'Pembimbing 1 & 2 berhasil dialokasikan untuk ' . ($pengajuan->mahasiswa->name ?? 'mahasiswa') . '. Draft SK Pembimbing bisa diunduh dari tabel pengajuan.');
    }

    /**
     * Modul 5: GET /dosen/kuota-tersedia — daftar dosen dengan kepakaran &
     * SISA kuota bimbingan (bukan cuma kuota maksimum statis), dipakai
     * Komisi Tesis untuk memilih pembimbing yang masih longgar.
     */
    public function kuotaTersedia(Request $request)
    {
        $dosens = User::where('role', 'dosen')->orderBy('name')->get()->map(function ($dosen) {
            $terpakai = PengajuanTesis::where(function ($q) use ($dosen) {
                $q->where('pembimbing_1_id', $dosen->id)
                  ->orWhere('pembimbing_2_id', $dosen->id);
            })->where('status_tahap', '!=', 'selesai_yudisium')->count();

            return [
                'id' => $dosen->id,
                'name' => $dosen->name,
                'identifier' => $dosen->identifier,
                'bidang_keahlian' => $dosen->bidang_keahlian,
                'kuota_maksimum' => $dosen->kuota_bimbingan_maks,
                'kuota_terpakai' => $terpakai,
                'sisa_kuota' => max(0, $dosen->kuota_bimbingan_maks - $terpakai),
            ];
        });

        return $request->wantsJson()
            ? response()->json(['status' => 'success', 'data' => $dosens])
            : view('pembimbing.kuota-tersedia', compact('dosens'));
    }

    /**
     * Modul 5: GET /penugasan-pembimbing/{mahasiswaId} — status penugasan
     * pembimbing untuk mahasiswa tertentu (lookup by mahasiswa_id, bukan
     * id pengajuan tesis, sesuai penamaan endpoint yang diminta).
     */
    public function penugasanPembimbing(Request $request, $mahasiswaId)
    {
        $pengajuan = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->firstOrFail();

        $this->authorize('view', $pengajuan);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => [
                'mahasiswa' => $pengajuan->mahasiswa?->name,
                'pembimbing_1' => $pengajuan->pembimbing1?->name,
                'pembimbing_2' => $pengajuan->pembimbing2?->name,
                'nomor_sk_pembimbing' => $pengajuan->nomor_sk_pembimbing,
                'tanggal_sk_pembimbing' => $pengajuan->tanggal_sk_pembimbing,
                'status_tahap' => $pengajuan->status_tahap,
            ]]);
        }

        return view('pembimbing.penugasan', compact('pengajuan'));
    }
}
