<?php

namespace App\Http\Controllers\UjianTesis;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranUjianController extends Controller
{
    /**
     * Tampilkan form pendaftaran Ujian Tesis.
     */
    public function create(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'pendaftaranUjian'])
            ->findOrFail($pengajuanId);

        $this->authorize('daftarSidang', $tesis);

        $blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_4_ujian');
        $statusSemhas = $this->getStatusLulusSemhas($tesis);

        return view('ujian-tesis.create', compact('tesis', 'blockReason', 'statusSemhas'));
    }

    /**
     * Item Checklist #2 ("Bukti Lulus Semhas & Publikasi") DITARIK OTOMATIS
     * (read-only) dari data Semhas yang sudah disahkan Kaprodi — BUKAN
     * field upload manual, sesuai prinsip yang sama dengan "Persetujuan
     * Revisi Sempro" pada Modul 7.
     */
    protected function getStatusLulusSemhas(PengajuanTesis $tesis): array
    {
        $semhasSidang = $tesis->aktivitasSidangs()->where('tahap_sidang', 'semhas')->first();
        $revisi = $semhasSidang?->revisiDokumen;

        if ($revisi && $revisi->pengesahan_kaprodi) {
            return [
                'lulus' => true,
                'label' => 'Lulus Semhas — Revisi Disahkan Kaprodi',
                'tanggal' => $revisi->disahkan_kaprodi_at,
            ];
        }

        return ['lulus' => false, 'label' => 'Belum Lulus Semhas / Revisi Belum Disahkan', 'tanggal' => null];
    }

    /**
     * FR-07: Pendaftaran Ujian Tesis H-14, ACC 2 Pembimbing & 8 Dokumen Prasyarat
     *
     * Catatan cakupan: item "Logbook Bimbingan Tesis" TIDAK termasuk
     * (FR-02 sengaja tidak diimplementasikan). 8 item checklist final:
     * 1. Naskah Tesis Lengkap (upload)
     * 2. Bukti Lulus Semhas & Publikasi (AUTO-REFERENCE, bukan upload —
     *    lihat getStatusLulusSemhas())
     * 3. Bukti Artikel Jurnal (upload)
     * 4. Bukti Seminar Internasional/Prosiding & Sertifikat (upload)
     * 5. Bukti Penguasaan Bahasa Inggris (upload + hard validation EAP/TOEFL)
     * 6. Bukti Pembayaran SPP Terakhir (upload)
     * 7. Kartu Hasil Studi / KHS Kumulatif (upload)
     * 8. Surat Bebas Plagiasi (upload + hard validation similarity <= 25%)
     */
    public function store(Request $request, $pengajuanId)
    {
        $tesis = PengajuanTesis::findOrFail($pengajuanId);

        $this->authorize('daftarSidang', $tesis);

        // 1. GATE State Machine: mahasiswa wajib sudah berada di tahap_4_ujian,
        //    yang hanya bisa dicapai jika revisi Semhas sudah di-ACC seluruh
        //    penguji DAN disahkan Kaprodi.
        if ($blockReason = LifecycleStateMachine::blockReason($tesis, 'tahap_4_ujian')) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $blockReason], 422)
                : back()->withErrors(['error' => $blockReason]);
        }

        // 2. Item Checklist #2 (Bukti Lulus Semhas & Publikasi) — defense
        //    in depth: pengecekan eksplisit di sini selain gate StateEngine
        //    di atas, supaya pesannya spesifik menyebut item checklist mana
        //    yang gagal (bukan pesan generik "belum boleh daftar").
        $statusSemhas = $this->getStatusLulusSemhas($tesis);
        if (!$statusSemhas['lulus']) {
            $msg = 'Pendaftaran ditolak: syarat #2 (Bukti Lulus Semhas & Publikasi) belum terpenuhi — revisi Semhas belum disahkan Kaprodi.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        // 3. Validasi Input & Berkas — masing-masing item checklist
        //    divalidasi TERPISAH supaya pesan error spesifik per field
        //    (acceptance criteria), bukan pesan generik.
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            // 1. Naskah Tesis Lengkap
            'naskah_tesis_lengkap' => 'required|file|mimes:pdf|max:51200',
            // 3. Bukti Artikel Jurnal (Sinta 1/2 / Internasional)
            'artikel_jurnal' => 'required|file|mimes:pdf|max:20480',
            // 4. Bukti Seminar Internasional/Prosiding & Sertifikat
            'prosiding_seminar' => 'required|file|mimes:pdf|max:20480',
            // 5. Bukti Penguasaan Bahasa Inggris
            'sertifikat_bahasa' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'jenis_skor_bahasa' => 'required|in:EAP,TOEFL',
            'skor_bahasa' => 'required|integer|min:0|max:1000',
            // 6. Bukti Pembayaran SPP Terakhir
            'bukti_spp_terakhir' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            // 7. Kartu Hasil Studi (KHS Kumulatif)
            'khs_kumulatif' => 'required|file|mimes:pdf|max:10240',
            // 8. Surat Bebas Plagiasi
            'surat_bebas_plagiasi' => 'required|file|mimes:pdf|max:10240',
            'similarity_score' => 'required|numeric|min:0|max:100',
        ]);

        // 4. HARD VALIDATION #5: EAP >= 65 ATAU TOEFL >= 475 — dua ambang
        //    batas BERBEDA tergantung jenis tes, bukan satu angka generik.
        $ambangLolos = $validated['jenis_skor_bahasa'] === 'EAP'
            ? $validated['skor_bahasa'] >= 65
            : $validated['skor_bahasa'] >= 475;

        if (!$ambangLolos) {
            $ambangLabel = $validated['jenis_skor_bahasa'] === 'EAP' ? 'EAP minimal 65' : 'TOEFL minimal 475';
            $msg = "Pendaftaran ditolak: syarat #5 (Bukti Penguasaan Bahasa Inggris) belum terpenuhi — skor {$validated['jenis_skor_bahasa']} Anda ({$validated['skor_bahasa']}) di bawah ambang batas kelulusan ({$ambangLabel}).";
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['skor_bahasa' => $msg])->withInput();
        }

        // 5. HARD VALIDATION #8: Similarity WAJIB <= 25%.
        if ($validated['similarity_score'] > 25) {
            $msg = "Pendaftaran ditolak: syarat #8 (Surat Bebas Plagiasi) belum terpenuhi — similarity score Anda ({$validated['similarity_score']}%) melebihi ambang batas maksimal 25%.";
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['similarity_score' => $msg])->withInput();
        }

        // 6. Validasi Batas Waktu Minimal H-14
        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            $msg = 'Pendaftaran Ujian Tesis wajib diajukan minimal H-14.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['jadwal_usulan_sidang' => $msg])->withInput();
        }

        // 7. Simpan Berkas Fisik ke Storage
        $pathNaskah = $request->file('naskah_tesis_lengkap')->store('ujian/naskah', 'public');
        $pathArtikel = $request->file('artikel_jurnal')->store('ujian/artikel', 'public');
        $pathProsiding = $request->file('prosiding_seminar')->store('ujian/prosiding', 'public');
        $pathBahasa = $request->file('sertifikat_bahasa')->store('ujian/bahasa', 'public');
        $pathSpp = $request->file('bukti_spp_terakhir')->store('ujian/spp', 'public');
        $pathKhs = $request->file('khs_kumulatif')->store('ujian/khs', 'public');
        $pathPlagiasi = $request->file('surat_bebas_plagiasi')->store('ujian/plagiasi', 'public');

        // 8. Simpan / Perbarui Pendaftaran — acc_tertulis_pembimbing_1/2
        //    SELALU direset ke false di setiap (re)submission, sama seperti
        //    pola approval naskah Semhas (Modul 7): revisi baru wajib
        //    disetujui ulang, approval draf lama tidak otomatis berlaku.
        $ujian = PendaftaranUjian::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            [
                'jadwal_usulan_sidang' => $validated['jadwal_usulan_sidang'],
                'naskah_tesis_lengkap_url' => '/storage/' . $pathNaskah,
                'artikel_jurnal_url' => '/storage/' . $pathArtikel,
                'prosiding_seminar_url' => '/storage/' . $pathProsiding,
                'sertifikat_bahasa_url' => '/storage/' . $pathBahasa,
                'jenis_skor_bahasa' => $validated['jenis_skor_bahasa'],
                'skor_bahasa' => $validated['skor_bahasa'],
                'bukti_spp_terakhir_url' => '/storage/' . $pathSpp,
                'khs_kumulatif_url' => '/storage/' . $pathKhs,
                'surat_bebas_plagiasi_url' => '/storage/' . $pathPlagiasi,
                'similarity_score' => $validated['similarity_score'],
                'acc_tertulis_pembimbing_1' => false,
                'acc_tertulis_pembimbing_2' => false,
                'status_verifikasi_admin' => 'pending',
            ]
        );

        AuditLogger::log(
            $request->user(),
            'ujian.store',
            'PendaftaranUjian',
            $ujian->id,
            'Pendaftaran Ujian Tesis diajukan — menunggu persetujuan tertulis Pembimbing 1 & 2.',
            []
        );

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $ujian]);
        }

        return redirect()->route('dashboard')->with('success', 'Pendaftaran Ujian Tesis berhasil diajukan! Menunggu persetujuan tertulis digital dari Pembimbing 1 & 2.');
    }

    /**
     * Persetujuan tertulis digital oleh Pembimbing 1/2 (FR-07 lanjutan) —
     * syarat wajib sebelum Komisi Tesis bisa melakukan plotting jadwal &
     * dewan penguji (ditegakkan di KomisiTesisController).
     */
    public function accPembimbing(Request $request, $id)
    {
        $ujian = PendaftaranUjian::with('pengajuanTesis')->findOrFail($id);
        $tesis = $ujian->pengajuanTesis;

        $this->authorize('approveNaskah', $tesis);

        $user = $request->user();
        $kolom = match ($user->id) {
            $tesis->pembimbing_1_id => 'acc_tertulis_pembimbing_1',
            $tesis->pembimbing_2_id => 'acc_tertulis_pembimbing_2',
            default => null,
        };

        if ($kolom === null) {
            $slot = $request->validate(['slot' => 'required|in:1,2'])['slot'];
            $kolom = "acc_tertulis_pembimbing_{$slot}";
        }

        $ujian->update([$kolom => true]);

        AuditLogger::log(
            $user,
            'ujian.accPembimbing',
            'PendaftaranUjian',
            $ujian->id,
            "Persetujuan tertulis digital Ujian Tesis via kolom {$kolom} oleh {$user->name}.",
            ['kolom' => $kolom]
        );

        // Modul 9: notifikasi status approval pembimbing ke mahasiswa.
        $ujian->refresh();
        $statusLengkap = ($ujian->acc_tertulis_pembimbing_1 && $ujian->acc_tertulis_pembimbing_2)
            ? 'Kedua pembimbing sudah menyetujui — menunggu plotting jadwal & dewan penguji oleh Komisi Tesis.'
            : 'Masih menunggu persetujuan dari pembimbing lainnya.';
        WhatsAppNotifierService::kirim('approval_pembimbing', $tesis->mahasiswa, [
            'nama_mahasiswa' => $tesis->mahasiswa->name ?? '-',
            'tahap_sidang' => 'UJIAN TESIS',
            'nama_pembimbing' => $user->name,
            'status_lengkap' => $statusLengkap,
        ], ['pendaftaran_ujian_id' => $ujian->id]);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'data' => $ujian]);
        }

        return redirect()->route('dashboard')->with('success', 'Persetujuan tertulis digital berhasil diberikan.');
    }
}
