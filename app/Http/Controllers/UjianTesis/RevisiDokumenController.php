<?php

namespace App\Http\Controllers\UjianTesis;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use Illuminate\Http\Request;

class RevisiDokumenController extends Controller
{
    /**
     * Halaman Matriks Revisi Pasca Sidang (FR-10) — SEBELUMNYA route ini
     * terdaftar di routes/web.php tapi method-nya belum pernah dibuat sama
     * sekali, jadi setiap klik "Review & ACC" dari dashboard dosen/kaprodi
     * selalu 500 Server Error. Satu halaman ini melayani SEMUA pihak
     * (mahasiswa pemilik, dewan penguji sidang ybs, dan pengendali
     * akademik) sesuai perannya masing-masing — mahasiswa mengisi/
     * memperbarui matriks, dewan penguji memberi ACC/catatan perbaikan per
     * baris miliknya, Kaprodi mengesahkan setelah semua ACC.
     */
    public function index(Request $request, $sidangId)
    {
        $sidang = AktivitasSidang::with([
            'pengajuanTesis.mahasiswa',
            'pengujiSidangs.dosen',
            'manajemenNilai',
            'revisiDokumen.revisiPengujis.dosenPenguji',
        ])->findOrFail($sidangId);

        $user = $request->user();
        $tesis = $sidang->pengajuanTesis;

        // Otorisasi kepemilikan: mahasiswa pemilik tesis ini, dewan penguji
        // yang tercatat di sidang ini, atau pengendali akademik — tidak ada
        // satu Policy ability generik yang pas untuk ketiganya sekaligus,
        // jadi dicek langsung di sini (pola yang sama dipakai di beberapa
        // controller lain, mis. PenggunaController::resetPassword).
        $isMahasiswaPemilik = $user->id === $tesis->mahasiswa_id;
        $isPenguji = $sidang->pengujiSidangs->contains('dosen_id', $user->id);
        $isPengendali = $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
        abort_unless($isMahasiswaPemilik || $isPenguji || $isPengendali, 403, 'Anda tidak berwenang melihat revisi sidang ini.');

        $blockReason = null;
        if (!$sidang->manajemenNilai) {
            $blockReason = 'Matriks revisi belum bisa diajukan: Komisi Tesis belum merekap nilai & keputusan sidang ini.';
        } elseif ($sidang->manajemenNilai->keputusan_sidang === 'ujian_ulang') {
            $blockReason = 'Keputusan sidang adalah Ujian Ulang — mahasiswa wajib mengulang sidang, bukan mengirim matriks revisi.';
        }

        $revisi = $sidang->revisiDokumen;

        // Baris matriks per dewan penguji: kalau matriks sudah pernah
        // diajukan, pakai RevisiPenguji yang tersimpan (lengkap dengan
        // status ACC & catatan); kalau belum, siapkan baris kosong per
        // penguji di sidang ini supaya mahasiswa tinggal mengisi.
        $baristMatriks = $sidang->pengujiSidangs->map(function ($ps) use ($revisi) {
            $existing = $revisi?->revisiPengujis?->firstWhere('dosen_penguji_id', $ps->dosen_id);
            return [
                'dosen' => $ps->dosen,
                'peran_penguji' => $ps->peran_penguji,
                'revisiPenguji' => $existing,
            ];
        });

        return view('revisi.index', [
            'sidang' => $sidang,
            'tesis' => $tesis,
            'revisi' => $revisi,
            'baristMatriks' => $baristMatriks,
            'blockReason' => $blockReason,
            'isMahasiswaPemilik' => $isMahasiswaPemilik,
            'isPenguji' => $isPenguji,
            'isPengendali' => $isPengendali,
            'myRevisiPengujiId' => $isPenguji ? optional($revisi?->revisiPengujis?->firstWhere('dosen_penguji_id', $user->id))->id : null,
        ]);
    }

    public function submitMatriks(Request $request, $sidangId)
    {
        // GATE State Machine: mahasiswa hanya boleh submit matriks revisi
        // JIKA sidang ini sudah direkap nilainya oleh Komisi Tesis
        // (PenilaianSidangController::rekapNilaiKomisi) DAN keputusannya
        // bukan 'ujian_ulang' (kalau ujian ulang, tidak ada revisi —
        // mahasiswa wajib mengulang sidang, bukan mengirim revisi).
        $sidang = AktivitasSidang::with('manajemenNilai', 'pengajuanTesis', 'pengujiSidangs')->findOrFail($sidangId);

        // Otorisasi kepemilikan: hanya mahasiswa pemilik pengajuan tesis
        // sidang ini (atau pengendali akademik) yang boleh submit matriks —
        // menutup celah submit matriks atas nama mahasiswa lain.
        $this->authorize('daftarSidang', $sidang->pengajuanTesis);

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

        // Setiap baris matriks WAJIB merujuk dosen yang benar-benar tercatat
        // sebagai dewan penguji sidang ini — sebelumnya validasi hanya
        // 'exists:users,id' (dosen manapun lolos), celah yang memungkinkan
        // matriks mengarah ke dosen yang tidak relevan dengan sidang ybs.
        $dosenPengujiValid = $sidang->pengujiSidangs->pluck('dosen_id')->all();
        foreach ($validated['matriks'] as $m) {
            if (!in_array($m['dosen_penguji_id'], $dosenPengujiValid, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Terdapat baris matriks yang merujuk dosen bukan dewan penguji sidang ini.'
                ], 422);
            }
        }

        $revisi = RevisiDokumen::updateOrCreate(
            ['sidang_id' => $sidangId],
            [
                'naskah_revisi_final_url' => $validated['naskah_revisi_final_url'],
                'bukti_luaran_final_url' => $validated['bukti_luaran_final_url'] ?? null,
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
                    // Resubmission (mis. setelah salah satu penguji minta
                    // perbaikan lagi) WAJIB balik ke 'pending' supaya penguji
                    // yang bersangkutan meninjau ulang versi terbaru — tidak
                    // otomatis ikut status ACC dari pengajuan sebelumnya.
                    'status_acc' => 'pending',
                    'feedback_penguji' => null,
                    'acc_at' => null,
                ]
            );
        }

        AuditLogger::log(
            $request->user(),
            'revisi.submitMatriks',
            'RevisiDokumen',
            $revisi->id,
            "Matriks revisi {$sidang->pengajuanTesis?->mahasiswa?->name} ({$sidang->tahap_sidang}) diajukan/diperbarui.",
            ['sidang_id' => $sidangId, 'jumlah_baris' => count($validated['matriks'])]
        );

        // Notifikasi WA ke setiap dewan penguji — supaya mereka tahu ada
        // matriks baru yang perlu ditinjau tanpa harus mengecek dashboard
        // berulang kali (pola yang sama dengan undangan_menguji/jadwal_terkunci).
        $linkRevisi = route('revisi.index', $sidang->id);
        $namaMahasiswa = $sidang->pengajuanTesis->mahasiswa->name ?? '-';
        foreach ($sidang->pengujiSidangs as $ps) {
            if (!$ps->dosen) continue;
            WhatsAppNotifierService::kirim('revisi_diajukan', $ps->dosen, [
                'nama_dosen' => $ps->dosen->name,
                'nama_mahasiswa' => $namaMahasiswa,
                'tahap_sidang' => strtoupper($sidang->tahap_sidang),
                'link_revisi' => $linkRevisi,
            ], ['sidang_id' => $sidang->id, 'revisi_dokumen_id' => $revisi->id]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Matriks revisi berhasil diajukan.', 'data' => $revisi->load('revisiPengujis')]);
        }

        return redirect()->route('revisi.index', $sidangId)->with('success', 'Matriks revisi berhasil diajukan. Dewan penguji sudah diberi tahu lewat WhatsApp.');
    }

    /**
     * ACC ATAU minta perbaikan lagi atas satu baris matriks — sebelumnya
     * method ini CUMA bisa meng-ACC (tidak ada jalur "tolak" sama sekali,
     * padahal kolom status_acc sudah punya pilihan 'perlu_perbaikan_lagi'
     * di database sejak awal). Dikirim lewat input 'status_acc', konsisten
     * dengan pola verifikasi di controller lain (mis. SemproController::
     * verifikasi menerima 'status_verifikasi_admin' verified/rejected).
     */
    public function accPenguji(Request $request, $revisiPengujiId)
    {
        $item = RevisiPenguji::with('revisiDokumen.sidang.pengajuanTesis.mahasiswa')->findOrFail($revisiPengujiId);

        // Otorisasi kepemilikan via Policy: seorang dosen hanya boleh
        // memberi ACC pada baris revisi miliknya sendiri, kecuali
        // Komisi Tesis/Kaprodi/Admin Prodi untuk override administratif.
        $this->authorize('acc', $item);

        $validated = $request->validate([
            'status_acc' => 'required|in:acc,perlu_perbaikan_lagi',
            'feedback_penguji' => 'nullable|string|max:2000',
        ]);

        if ($validated['status_acc'] === 'perlu_perbaikan_lagi' && empty($validated['feedback_penguji'])) {
            return back()->withErrors(['feedback_penguji' => 'Catatan perbaikan wajib diisi kalau meminta perbaikan lagi.'])->withInput();
        }

        $item->update([
            'status_acc' => $validated['status_acc'],
            'feedback_penguji' => $validated['feedback_penguji'] ?? ($validated['status_acc'] === 'acc' ? 'Perbaikan disetujui.' : null),
            'acc_at' => $validated['status_acc'] === 'acc' ? now() : null,
        ]);

        $revisi = $item->revisiDokumen;
        $totalPenguji = $revisi->revisiPengujis()->count();
        $totalAcc = $revisi->revisiPengujis()->where('status_acc', 'acc')->count();

        // Kalau SALAH SATU penguji meminta perbaikan lagi, rekapitulasi
        // "semua sudah ACC" otomatis batal — mahasiswa wajib mengirim ulang
        // matriks (submitMatriks akan mengembalikan status seluruh baris
        // ke 'pending' lagi) sebelum bisa menuju pengesahan Kaprodi.
        $revisi->update(['status_approval_semua' => $totalPenguji > 0 && $totalPenguji === $totalAcc]);

        AuditLogger::log(
            $request->user(),
            'revisi.accPenguji',
            'RevisiPenguji',
            $item->id,
            $validated['status_acc'] === 'acc'
                ? "ACC revisi oleh dosen penguji {$item->dosenPenguji?->name}."
                : "Dosen penguji {$item->dosenPenguji?->name} meminta perbaikan lagi.",
            ['status_acc' => $validated['status_acc']]
        );

        $tesis = $revisi->sidang->pengajuanTesis;
        $linkRevisi = route('revisi.index', $revisi->sidang_id);

        if ($validated['status_acc'] === 'perlu_perbaikan_lagi' && $tesis->mahasiswa) {
            // Notifikasi ke mahasiswa SEGERA — sebelumnya tidak ada jalur
            // ini sama sekali, mahasiswa harus tahu ada catatan perbaikan
            // tanpa harus mengecek manual berulang kali.
            WhatsAppNotifierService::kirim('revisi_perlu_perbaikan', $tesis->mahasiswa, [
                'nama_mahasiswa' => $tesis->mahasiswa->name ?? '-',
                'nama_dosen' => $item->dosenPenguji->name ?? '-',
                'tahap_sidang' => strtoupper($revisi->sidang->tahap_sidang ?? '-'),
                'feedback_penguji' => $validated['feedback_penguji'] ?? '-',
                'link_revisi' => $linkRevisi,
            ], ['sidang_id' => $revisi->sidang_id, 'revisi_penguji_id' => $item->id]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $item]);
        }

        $pesan = $validated['status_acc'] === 'acc'
            ? 'Revisi berhasil di-ACC.'
            : 'Permintaan perbaikan lagi berhasil dikirim ke mahasiswa.';

        return redirect()->route('revisi.index', $revisi->sidang_id)->with('success', $pesan);
    }

    public function pengesahanKaprodi(Request $request, $revisiId)
    {
        $revisi = RevisiDokumen::with('sidang.pengajuanTesis.mahasiswa')->findOrFail($revisiId);

        $this->authorize('pengesahan', RevisiPenguji::class);

        if (!$revisi->status_approval_semua) {
            return response()->json(['status' => 'error', 'message' => 'Belum seluruh dewan penguji memberikan ACC revisi.'], 422);
        }

        $revisi->update([
            'pengesahan_kaprodi' => true,
            'disahkan_kaprodi_at' => now()
        ]);

        $tesis = $revisi->sidang->pengajuanTesis;
        $tahap = $revisi->sidang->tahap_sidang;

        $targetTahap = match ($tahap) {
            'sempro' => 'tahap_3_semhas',
            'semhas' => 'tahap_4_ujian',
            'ujian'  => 'selesai_yudisium',
            default  => null,
        };

        if ($targetTahap) {
            // Ditegakkan lewat LifecycleStateMachine::transition() (bukan update()
            // langsung) supaya canTransitionTo() ikut memvalidasi ulang prasyarat
            // sebelum status_tahap benar-benar berpindah — satu titik kebenaran
            // untuk seluruh state machine, bukan hanya gate saat pendaftaran.
            try {
                LifecycleStateMachine::transition($tesis, $targetTahap);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
            }
        }

        AuditLogger::log(
            $request->user(),
            'revisi.pengesahanKaprodi',
            'RevisiDokumen',
            $revisi->id,
            "Kaprodi mengesahkan revisi {$tesis->mahasiswa?->name} — status berpindah ke {$targetTahap}.",
            ['tahap_sebelumnya' => $tahap, 'tahap_baru' => $targetTahap]
        );

        if ($tesis->mahasiswa) {
            $statusLanjutan = $targetTahap === 'selesai_yudisium'
                ? 'Selamat, Anda sudah menyelesaikan seluruh tahap tesis dan siap yudisium!'
                : 'Anda sekarang bisa melanjutkan ke tahap berikutnya di SIM-TESIS.';

            WhatsAppNotifierService::kirim('revisi_disahkan', $tesis->mahasiswa, [
                'nama_mahasiswa' => $tesis->mahasiswa->name ?? '-',
                'tahap_sidang' => strtoupper($tahap),
                'status_lanjutan' => $statusLanjutan,
            ], ['sidang_id' => $revisi->sidang_id, 'revisi_dokumen_id' => $revisi->id]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Revisi disahkan Kaprodi. Status tahapan akademik berhasil diperbarui.',
                'data' => $tesis
            ]);
        }

        return redirect()->route('dashboard')->with('success', 'Revisi disahkan dan status tahapan akademik mahasiswa berhasil diperbarui.');
    }
}