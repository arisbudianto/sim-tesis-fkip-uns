<?php

namespace App\Http\Controllers\Sempro;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\Sidang\Services\AntiConflictScheduler;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PendaftaranSemproController extends Controller
{
    /**
     * Boleh mendaftar/daftar-ulang Sempro kalau:
     * 1. Memenuhi syarat transisi normal (tahap_1_bimbingan + 2 pembimbing), ATAU
     * 2. Sudah pernah mendaftar (status_tahap sudah tahap_2_sempro) TAPI
     *    pendaftaran sebelumnya DITOLAK — supaya mahasiswa tidak terjebak
     *    tidak bisa mendaftar ulang hanya karena status_tahap sudah terlanjur
     *    berpindah saat submit pertama (terlepas dari hasil verifikasinya).
     */
    protected function bolehDaftarSempro(PengajuanTesis $tesis): bool
    {
        if (LifecycleStateMachine::canTransitionTo($tesis, 'tahap_2_sempro')) {
            return true;
        }

        return $tesis->status_tahap === 'tahap_2_sempro'
            && $tesis->pendaftaranSempro
            && $tesis->pendaftaranSempro->status_verifikasi_admin === 'rejected';
    }

    /**
     * Tampilkan form pendaftaran Seminar Proposal (Sempro).
     * Halaman ini sebelumnya blank karena method create() belum pernah dibuat
     * — routes/web.php sudah memanggilnya, tapi controller cuma punya store().
     */
    public function create(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'pendaftaranSempro'])
            ->findOrFail($pengajuanId);

        // Otorisasi kepemilikan: hanya mahasiswa pemilik PengajuanTesis ini
        // yang boleh mendaftar Sempro atas namanya — menutup celah mendaftar
        // atas nama mahasiswa lain hanya dengan menebak/mengetahui ID.
        $this->authorize('daftarSidang', $tesis);

        // Tampilkan alasan blokir (kalau ada) di halaman, bukan cuma saat submit,
        // supaya mahasiswa tahu dari awal kenapa belum bisa mendaftar.
        $blockReason = $this->bolehDaftarSempro($tesis)
            ? null
            : 'Pendaftaran belum bisa dibuka: Alokasi 2 Pembimbing belum lengkap atau tahapan akademik Anda belum sesuai.';

        return view('sempro.create', compact('tesis', 'blockReason'));
    }

    /**
     * FR-03: Pendaftaran Seminar Proposal (Sempro) H-14 & Unggah Dokumen FPT-TI-01 & 02
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::with('pendaftaranSempro')->findOrFail($pengajuanId);

        // Otorisasi kepemilikan (lihat catatan di create()).
        $this->authorize('daftarSidang', $tesis);

        // 1. Verifikasi Lifecycle State Machine (termasuk izin daftar-ulang
        //    kalau pendaftaran sebelumnya ditolak)
        if (!$this->bolehDaftarSempro($tesis)) {
            $msg = 'Pendaftaran ditolak: Alokasi 2 Pembimbing belum lengkap atau tahapan tidak sesuai.';
            return $request->wantsJson() 
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        // 2. Validasi Input dan Berkas PDF
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            // FPT-TI-01: Permohonan Ujian Tesis 1 yang sudah ditandatangani
            // Pembimbing 1 & 2 — wajib, PDF saja, maksimal 1MB.
            'form_fpt_ti_01'       => 'required|file|mimes:pdf|max:1024',
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

        // Berkas FPT-TI-01 (wajib) — bukti persetujuan tertanda tangan
        // Pembimbing 1 & 2, ditinjau Admin/Komisi/Kaprodi sebelum menyetujui.
        $pathForm = $request->file('form_fpt_ti_01')->store('sempro/fpt-ti-01', 'public');
        $dataPendaftaran['form_fpt_ti_01_url'] = '/storage/' . $pathForm;

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

        // 6. Transisi Status Tahap Akademik Mahasiswa — WAJIB lewat StateEngine
        //    (bukan $tesis->update() langsung), supaya tercatat di
        //    state_transition_log dengan actor yang jelas (Modul 4).
        //    Kasus daftar-ULANG (status sudah tahap_2_sempro, pendaftaran
        //    sebelumnya ditolak) TIDAK memicu transisi baru — statusnya
        //    memang belum berubah, cuma berkasnya yang diperbarui.
        if ($tesis->status_tahap === 'tahap_1_bimbingan') {
            LifecycleStateMachine::transition($tesis, 'tahap_2_sempro', $request->user());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Pendaftaran Seminar Proposal berhasil diajukan.',
                'data'    => $sempro
            ], 200);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Seminar Proposal (Sempro) berhasil diajukan!');
    }

    /**
     * Verifikasi pendaftaran Sempro oleh Admin Prodi / Komisi Tesis / Kaprodi.
     * Setelah status jadi 'verified', Form Permohonan (FPT-TI-01), Surat Tugas,
     * dan Undangan Penguji baru bisa diunduh.
     */
    public function verifikasi(Request $request, $id)
    {
        $sempro = PendaftaranSempro::with('pengajuanTesis')->findOrFail($id);
        $this->authorize('verifikasiPendaftaran', $sempro->pengajuanTesis);

        $validated = $request->validate([
            'status_verifikasi_admin' => 'required|in:verified,rejected',
            'catatan_admin' => 'nullable|string',
        ]);

        $nama = $sempro->pengajuanTesis?->mahasiswa?->name;
        $status = $validated['status_verifikasi_admin'];

        if ($status === 'rejected') {
            $semproId = $sempro->id;
            $sempro->delete();

            AuditLogger::log(
                $request->user(),
                'sempro.verifikasi',
                'PendaftaranSempro',
                $semproId,
                "Pendaftaran Sempro {$nama} ditolak dan riwayatnya dihapus.",
                ['status' => 'rejected', 'catatan' => $validated['catatan_admin'] ?? null]
            );

            $pesan = 'Pendaftaran Sempro ditolak. Riwayat pendaftaran dihapus.';
            return $request->wantsJson()
                ? response()->json(['status' => 'success', 'message' => $pesan])
                : redirect()->route('dashboard')->with('success', $pesan);
        }

        $sempro->update($validated);

        AuditLogger::log(
            $request->user(),
            'sempro.verifikasi',
            'PendaftaranSempro',
            $sempro->id,
            "Pendaftaran Sempro {$nama} di-{$status}.",
            ['status' => $status, 'catatan' => $validated['catatan_admin'] ?? null]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $sempro]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Sempro disetujui.');
    }

    /**
     * Komisi Tesis / Admin / Kaprodi mengedit HANYA:
     * - hari/tanggal (jadwal usulan + waktu sidang),
     * - penguji eksternal (Ketua & Sekretaris).
     * Pembimbing utama & pendamping TIDAK boleh diubah dari form ini.
     */
    public function update(Request $request, $id)
    {
        $sempro = PendaftaranSempro::with('pengajuanTesis.pembimbing1', 'pengajuanTesis.pembimbing2')->findOrFail($id);
        $this->authorize('verifikasiPendaftaran', $sempro->pengajuanTesis);

        $tesis = $sempro->pengajuanTesis;

        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'ketua_penguji_id' => 'nullable|uuid|exists:users,id',
            'sekretaris_penguji_id' => 'nullable|uuid|exists:users,id',
        ]);

        if (!empty($validated['ketua_penguji_id']) && $validated['ketua_penguji_id'] === ($validated['sekretaris_penguji_id'] ?? null)) {
            return back()->withErrors(['ketua_penguji_id' => 'Ketua dan Sekretaris Penguji tidak boleh dosen yang sama.'])->withInput();
        }

        foreach (['ketua_penguji_id', 'sekretaris_penguji_id'] as $field) {
            if (!empty($validated[$field]) && in_array($validated[$field], array_filter([$tesis->pembimbing_1_id, $tesis->pembimbing_2_id]), true)) {
                return back()->withErrors([$field => 'Penguji eksternal tidak boleh sama dengan Pembimbing Utama atau Pendamping.'])->withInput();
            }
        }

        $sempro->update([
            'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
        ]);

        $waktuMulai = Carbon::parse($validated['jadwal_usulan_sidang']);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        $sidang = AktivitasSidang::firstOrNew([
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => 'sempro',
        ]);

        $dosenIds = array_values(array_filter([
            $validated['ketua_penguji_id'] ?? null,
            $validated['sekretaris_penguji_id'] ?? null,
            $tesis->pembimbing_1_id,
            $tesis->pembimbing_2_id,
        ]));

        $conflicts = AntiConflictScheduler::checkConflict(
            $waktuMulai->toDateTimeString(),
            $waktuSelesai->toDateTimeString(),
            $sidang->ruangan,
            $dosenIds,
            $sidang->exists ? $sidang->id : null
        );
        if (!empty($conflicts)) {
            return back()->withErrors(['jadwal_usulan_sidang' => implode(' ', $conflicts)])->withInput();
        }

        $sidang->waktu_mulai = $waktuMulai;
        $sidang->waktu_selesai = $waktuSelesai;
        if (!$sidang->exists) {
            $sidang->ruangan = $sidang->ruangan ?: 'TBA';
            $sidang->komisi_tesis_id = $request->user()->id;
            $sidang->is_locked = false;
        }
        $sidang->save();

        // Kunci pembimbing 1 & 2 dari data tesis — tidak diambil dari input.
        $this->syncPengujiTetap($sidang, 'pembimbing_1', $tesis->pembimbing_1_id);
        $this->syncPengujiTetap($sidang, 'pembimbing_2', $tesis->pembimbing_2_id);

        if (!empty($validated['ketua_penguji_id'])) {
            $this->syncPengujiTetap($sidang, 'ketua_penguji', $validated['ketua_penguji_id']);
        }
        if (!empty($validated['sekretaris_penguji_id'])) {
            $this->syncPengujiTetap($sidang, 'sekretaris_penguji', $validated['sekretaris_penguji_id']);
        }

        AuditLogger::log(
            $request->user(),
            'sempro.update',
            'PendaftaranSempro',
            $sempro->id,
            "Jadwal/penguji Sempro {$tesis->mahasiswa?->name} diperbarui. Pembimbing tetap.",
            [
                'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
                'ketua_penguji_id' => $validated['ketua_penguji_id'] ?? null,
                'sekretaris_penguji_id' => $validated['sekretaris_penguji_id'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $sempro->fresh()]);
        }

        return redirect()->route('dashboard')->with('success', 'Jadwal dan penguji Sempro berhasil diperbarui. Pembimbing utama & pendamping tidak diubah.');
    }

    protected function syncPengujiTetap(AktivitasSidang $sidang, string $peran, ?string $dosenId): void
    {
        if (!$dosenId) {
            return;
        }

        PengujiSidang::updateOrCreate(
            [
                'sidang_id' => $sidang->id,
                'peran_penguji' => $peran,
            ],
            [
                'dosen_id' => $dosenId,
            ]
        );
    }
}
