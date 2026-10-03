<?php

namespace App\Http\Controllers\Semhas;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\Sidang\Services\AntiConflictScheduler;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranSemhasController extends Controller
{
    /**
     * Tampilkan form pendaftaran Seminar Hasil (Semhas).
     */
    public function create(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'pendaftaranSemhas'])
            ->findOrFail($pengajuanId);

        $this->authorize('daftarSidang', $tesis);

        $blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_3_semhas');

        // Persetujuan Revisi Sempro: DITARIK OTOMATIS (read-only) dari data
        // Sempro yang sudah disahkan Kaprodi — mahasiswa/siapa pun TIDAK
        // BISA mengisi field ini secara manual, murni hasil query.
        $revisiSempro = $this->getStatusRevisiSempro($tesis);

        return view('semhas.create', compact('tesis', 'blockReason', 'revisiSempro'));
    }

    /**
     * Query read-only status pengesahan revisi Sempro — satu-satunya
     * sumber kebenaran untuk field "Persetujuan Revisi Sempro" (tidak ada
     * input form untuk ini di mana pun, sengaja, sesuai instruksi Modul 7).
     */
    protected function getStatusRevisiSempro(PengajuanTesis $tesis): array
    {
        $semproSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'sempro')->first();
        $revisi = $semproSidang?->revisiDokumen;

        if ($revisi && $revisi->pengesahan_kaprodi) {
            return [
                'disahkan' => true,
                'label' => 'ACC Revisi Sempro Disahkan Kaprodi',
                'tanggal' => $revisi->disahkan_kaprodi_at,
            ];
        }

        return [
            'disahkan' => false,
            'label' => 'Belum Disahkan Kaprodi',
            'tanggal' => null,
        ];
    }

    /**
     * FR-05: Pendaftaran Seminar Hasil (Semhas) H-14 & Berkas Luaran Publikasi
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::findOrFail($pengajuanId);

        $this->authorize('daftarSidang', $tesis);

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
        // approval_pembimbing_1/2 SELALU direset ke false di setiap
        // (re)submission — memastikan interpretasi "wajib disetujui
        // Pembimbing Utama & Pendamping" tetap berlaku: kalau mahasiswa
        // mengunggah ulang naskah (revisi), approval sebelumnya (untuk
        // draft naskah yang lama) TIDAK otomatis berlaku ke draft baru.
        $semhas = PendaftaranSemhas::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            [
                'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
                'form_fpt_sh_01_url' => '/storage/' . $pathForm,
                'naskah_bab_1_5_url' => '/storage/' . $pathNaskah,
                'draf_artikel_ilmiah_urls' => $artikelUrls,
                'bukti_status_under_review_url' => '/storage/' . $pathReview,
                'bukti_spp_url' => '/storage/' . $pathSpp,
                'approval_pembimbing_1' => false,
                'approval_pembimbing_2' => false,
                'status_verifikasi_admin' => 'pending',
            ]
        );

        AuditLogger::log(
            $request->user(),
            'semhas.store',
            'PendaftaranSemhas',
            $semhas->id,
            "Pendaftaran Semhas diajukan — menunggu approval naskah Pembimbing 1 & 2.",
            []
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Seminar Hasil (Semhas) berhasil diajukan!');
    }

    /**
     * Approval digital naskah oleh Pembimbing Utama (pembimbing_1) atau
     * Pendamping (pembimbing_2) — syarat wajib sebelum Admin Prodi bisa
     * memverifikasi/menyetujui pendaftaran Semhas secara final.
     */
    public function approveNaskah(Request $request, $id)
    {
        $semhas = PendaftaranSemhas::with('pengajuanTesis')->findOrFail($id);
        $tesis = $semhas->pengajuanTesis;

        $this->authorize('approveNaskah', $tesis);

        $user = $request->user();
        $kolom = match ($user->id) {
            $tesis->pembimbing_1_id => 'approval_pembimbing_1',
            $tesis->pembimbing_2_id => 'approval_pembimbing_2',
            default => null, // pengendali akademik (override) — approve slot yang diminta eksplisit
        };

        // Pengendali akademik (Komisi Tesis/Kaprodi/Admin Prodi) bisa
        // meng-override approval slot tertentu secara eksplisit lewat body.
        if ($kolom === null) {
            $slot = $request->validate(['slot' => 'required|in:1,2'])['slot'];
            $kolom = "approval_pembimbing_{$slot}";
        }

        $semhas->update([$kolom => true]);

        AuditLogger::log(
            $user,
            'semhas.approveNaskah',
            'PendaftaranSemhas',
            $semhas->id,
            "Naskah Bab I-V Semhas disetujui via kolom {$kolom} oleh {$user->name}.",
            ['kolom' => $kolom]
        );

        // Modul 9: notifikasi status approval pembimbing ke mahasiswa.
        $semhas->refresh();
        $statusLengkap = ($semhas->approval_pembimbing_1 && $semhas->approval_pembimbing_2)
            ? 'Kedua pembimbing sudah menyetujui — menunggu verifikasi Admin Prodi.'
            : 'Masih menunggu persetujuan dari pembimbing lainnya.';
        WhatsAppNotifierService::kirim('approval_pembimbing', $tesis->mahasiswa, [
            'nama_mahasiswa' => $tesis->mahasiswa->name ?? '-',
            'tahap_sidang' => 'SEMHAS',
            'nama_pembimbing' => $user->name,
            'status_lengkap' => $statusLengkap,
        ], ['pendaftaran_semhas_id' => $semhas->id]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        return redirect()->route('dashboard')->with('success', 'Naskah Semhas berhasil disetujui.');
    }

    /**
     * Verifikasi pendaftaran Semhas oleh Admin Prodi / Komisi Tesis / Kaprodi.
     */
    public function verifikasi(Request $request, $id)
    {
        $semhas = PendaftaranSemhas::with('pengajuanTesis')->findOrFail($id);
        $this->authorize('verifikasiPendaftaran', $semhas->pengajuanTesis);

        $validated = $request->validate([
            'status_verifikasi_admin' => 'required|in:verified,rejected',
        ]);

        // Syarat administrasi WAJIB (Modul 7): naskah Bab I-V harus sudah
        // disetujui KEDUA pembimbing sebelum Admin Prodi bisa menyetujui
        // (bukan menolak) pendaftaran ini secara final. Pesan spesifik
        // per pembimbing yang belum approve, bukan pesan generik.
        if ($validated['status_verifikasi_admin'] === 'verified') {
            $belum = [];
            if (!$semhas->approval_pembimbing_1) $belum[] = 'Pembimbing 1 (' . ($semhas->pengajuanTesis->pembimbing1->name ?? '-') . ')';
            if (!$semhas->approval_pembimbing_2) $belum[] = 'Pembimbing 2 (' . ($semhas->pengajuanTesis->pembimbing2->name ?? '-') . ')';

            if (!empty($belum)) {
                $msg = 'Tidak bisa menyetujui pendaftaran: naskah Bab I-V belum disetujui oleh ' . implode(' dan ', $belum) . '.';
                return $request->wantsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->withErrors(['error' => $msg]);
            }
        }

        $semhas->update($validated);

        AuditLogger::log(
            $request->user(),
            'semhas.verifikasi',
            'PendaftaranSemhas',
            $semhas->id,
            "Pendaftaran Semhas {$semhas->pengajuanTesis?->mahasiswa?->name} di-{$validated['status_verifikasi_admin']}.",
            ['status' => $validated['status_verifikasi_admin']]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas]);
        }

        $pesan = $validated['status_verifikasi_admin'] === 'verified'
            ? 'Pendaftaran Semhas disetujui.'
            : 'Pendaftaran Semhas ditolak.';

        return redirect()->route('dashboard')->with('success', $pesan);
    }

    /**
     * Adopsi dari PendaftaranSemproController::update() — Komisi Tesis /
     * Admin / Kaprodi mengedit dalam satu form yang sama persis seperti
     * Sempro: judul/bidang fokus/abstrak, link Naskah Lengkap (Bab I-V),
     * hari/tanggal + Ruangan/Link Zoom, dan Ketua/Sekretaris Penguji
     * eksternal. Komposisi dewan penguji Semhas (Ketua + Sekretaris +
     * Pembimbing 1 & 2, lihat KomisiTesisController::createSidangWithPenguji)
     * SAMA PERSIS dengan Sempro, jadi field-nya bisa diadopsi tanpa
     * penyesuaian peran. Pembimbing utama & pendamping tetap tidak bisa
     * diubah dari sini (tetap lewat menu Pengajuan).
     */
    public function update(Request $request, $id)
    {
        $semhas = PendaftaranSemhas::with('pengajuanTesis.pembimbing1', 'pengajuanTesis.pembimbing2')->findOrFail($id);
        $this->authorize('verifikasiPendaftaran', $semhas->pengajuanTesis);

        $tesis = $semhas->pengajuanTesis;

        $validated = $request->validate([
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string|max:255',
            'abstrak_rencana' => 'nullable|string',
            'jadwal_usulan_sidang' => 'required|date',
            'ruangan' => 'nullable|string|max:255',
            'link_zoom' => 'nullable|string|max:500',
            'ketua_penguji_id' => 'nullable|uuid|exists:users,id',
            'sekretaris_penguji_id' => 'nullable|uuid|exists:users,id',
            // Sama seperti Naskah Lengkap Sempro: link (Google Drive/cloud
            // lain), bukan upload file, supaya tidak kena batas ukuran
            // upload PHP/hosting.
            'naskah_bab_1_5_url' => 'nullable|url|max:1000',
        ]);

        if (!empty($validated['ketua_penguji_id']) && $validated['ketua_penguji_id'] === ($validated['sekretaris_penguji_id'] ?? null)) {
            return back()->withErrors(['ketua_penguji_id' => 'Ketua dan Sekretaris Penguji tidak boleh dosen yang sama.'])->withInput();
        }

        foreach (['ketua_penguji_id', 'sekretaris_penguji_id'] as $field) {
            if (!empty($validated[$field]) && in_array($validated[$field], array_filter([$tesis->pembimbing_1_id, $tesis->pembimbing_2_id]), true)) {
                return back()->withErrors([$field => 'Penguji eksternal tidak boleh sama dengan Pembimbing Utama atau Pendamping.'])->withInput();
            }
        }

        $tesis->update([
            'judul_tesis' => $validated['judul_tesis'],
            'bidang_fokus' => $validated['bidang_fokus'],
            'abstrak_rencana' => $validated['abstrak_rencana'] ?? null,
        ]);

        $dataSemhas = [
            'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
        ];
        if (!empty($validated['naskah_bab_1_5_url'])) {
            $dataSemhas['naskah_bab_1_5_url'] = $validated['naskah_bab_1_5_url'];
        }
        $semhas->update($dataSemhas);

        $waktuMulai = Carbon::parse($validated['jadwal_usulan_sidang']);
        $waktuSelesai = $waktuMulai->copy()->addHours(2);

        $sidang = AktivitasSidang::firstOrNew([
            'pengajuan_tesis_id' => $tesis->id,
            'tahap_sidang' => 'semhas',
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
        // Sama seperti Sempro: ruangan dikosongkan artinya memang daring
        // (dipakai Link Zoom), BUKAN "pakai nilai lama" — 'TBA' cuma fallback
        // kalau Ruangan MAUPUN Link Zoom sama-sama kosong.
        $ruangan = $validated['ruangan'] ?? null;
        $linkZoom = $validated['link_zoom'] ?? null;
        if (!filled($ruangan) && !filled($linkZoom)) {
            $ruangan = 'TBA';
        }
        $sidang->ruangan = $ruangan;
        $sidang->link_zoom = $linkZoom;
        if (!$sidang->exists) {
            $sidang->komisi_tesis_id = $request->user()->id;
            $sidang->is_locked = false;
        }
        $sidang->save();

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
            'semhas.update',
            'PendaftaranSemhas',
            $semhas->id,
            "Judul/data proposal & jadwal/penguji Semhas {$tesis->mahasiswa?->name} diperbarui. Pembimbing tetap.",
            [
                'judul_tesis' => $validated['judul_tesis'],
                'bidang_fokus' => $validated['bidang_fokus'],
                'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
                'ketua_penguji_id' => $validated['ketua_penguji_id'] ?? null,
                'sekretaris_penguji_id' => $validated['sekretaris_penguji_id'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $semhas->fresh()]);
        }

        return redirect()->route('dashboard')->with('success', 'Data proposal, jadwal, dan penguji Semhas berhasil diperbarui. Pembimbing utama & pendamping tidak diubah.');
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
