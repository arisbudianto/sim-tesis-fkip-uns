<?php

namespace App\Http\Controllers\Sidang;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Services\AntiConflictScheduler;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use Illuminate\Http\Request;

class KomisiTesisController extends Controller
{
    public function plottingSempro(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'sempro');
    }

    public function plottingSemhas(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'semhas');
    }

    public function plottingUjian(Request $request, $pengajuanId)
    {
        return $this->createSidangWithPenguji($request, $pengajuanId, 'ujian');
    }

    private function createSidangWithPenguji(Request $request, string $pengajuanId, string $tahap)
    {
        $this->authorize('plot', AktivitasSidang::class);

        // GATE State Machine: Komisi Tesis tidak boleh menjadwalkan sidang
        // untuk tahap yang belum "terbuka" bagi mahasiswa tersebut (mis.
        // menjadwalkan Semhas padahal mahasiswa masih di tahap Sempro).
        $tesis = PengajuanTesis::findOrFail($pengajuanId);
        if ($blockReason = LifecycleStateMachine::blockReasonForSidang($tesis, $tahap)) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $blockReason], 422)
                : back()->withErrors(['error' => $blockReason]);
        }

        $validated = $request->validate([
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'ruangan' => 'nullable|string',
            'link_zoom' => 'nullable|string',
            'komisi_tesis_id' => 'required|uuid|exists:users,id',
            'penguji' => 'required|array|min:4',
            'penguji.*.dosen_id' => 'required|uuid|exists:users,id',
            'penguji.*.peran_penguji' => 'required|string|in:ketua_penguji,sekretaris_penguji,pembimbing_1,pembimbing_2,penguji_studi,penguji_pendidikan'
        ]);

        $dosenIds = array_column($validated['penguji'], 'dosen_id');

        // Integritas dewan penguji: satu dosen tidak boleh menempati lebih
        // dari satu peran sekaligus dalam satu sidang yang sama.
        if (count($dosenIds) !== count(array_unique($dosenIds))) {
            $msg = 'Satu dosen tidak boleh ditugaskan dua kali sebagai penguji pada sidang yang sama.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        // Komposisi Dewan Penguji BERBEDA antara Sempro/Semhas dan Ujian
        // Tesis (Modul 8 mengoreksi asumsi lama yang menyeragamkan semua
        // tahap):
        // - Sempro/Semhas: Ketua Penguji & Sekretaris Penguji EKSTERNAL,
        //   ditambah 2 Pembimbing sebagai anggota.
        // - Ujian Tesis: 2 Pembimbing BERPERAN sebagai Ketua & Sekretaris
        //   sidang, ditambah 1 Penguji Bidang Studi & 1 Penguji Bidang
        //   Pendidikan sebagai anggota eksternal (sesuai proposal Bab III-D).
        $peranList = array_column($validated['penguji'], 'peran_penguji');
        $wajib = $tahap === 'ujian'
            ? ['pembimbing_1', 'pembimbing_2', 'penguji_studi', 'penguji_pendidikan']
            : ['ketua_penguji', 'sekretaris_penguji', 'pembimbing_1', 'pembimbing_2'];
        $labelWajib = $tahap === 'ujian'
            ? 'Pembimbing 1, Pembimbing 2, Penguji Bidang Studi, dan Penguji Bidang Pendidikan'
            : 'Ketua Penguji, Sekretaris Penguji, Pembimbing 1, dan Pembimbing 2';

        $hilang = array_diff($wajib, $peranList);
        if (count($validated['penguji']) !== 4 || !empty($hilang)) {
            $msg = ucfirst($tahap) . " wajib memiliki tepat 4 dewan penguji dengan peran lengkap: {$labelWajib}.";
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg])->withInput();
        }

        // Modul 8, syarat FR-07 lanjutan: Ujian Tesis TIDAK BOLEH diplotting
        // sebelum KEDUA pembimbing memberi persetujuan tertulis digital
        // (acc_tertulis_pembimbing_1/2) atas pendaftaran ujian mahasiswa ybs.
        if ($tahap === 'ujian') {
            $pendaftaranUjian = $tesis->pendaftaranUjian;
            if (!$pendaftaranUjian || !$pendaftaranUjian->acc_tertulis_pembimbing_1 || !$pendaftaranUjian->acc_tertulis_pembimbing_2) {
                $belum = [];
                if (!$pendaftaranUjian || !$pendaftaranUjian->acc_tertulis_pembimbing_1) $belum[] = 'Pembimbing 1';
                if (!$pendaftaranUjian || !$pendaftaranUjian->acc_tertulis_pembimbing_2) $belum[] = 'Pembimbing 2';
                $msg = 'Plotting Ujian Tesis belum bisa dilakukan: persetujuan tertulis digital dari '
                     . implode(' dan ', $belum) . ' belum diberikan.';
                return $request->wantsJson()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->withErrors(['error' => $msg]);
            }
        }

        $conflicts = AntiConflictScheduler::checkConflict(
            $validated['waktu_mulai'],
            $validated['waktu_selesai'],
            $validated['ruangan'] ?? null,
            $dosenIds
        );

        if (!empty($conflicts)) {
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => 'Terjadi bentrok jadwal!', 'conflicts' => $conflicts], 422)
                : back()->withErrors(['error' => 'Terjadi bentrok jadwal: ' . implode(' ', $conflicts)])->withInput();
        }

        $sidang = AktivitasSidang::create([
            'pengajuan_tesis_id' => $pengajuanId,
            'tahap_sidang' => $tahap,
            'waktu_mulai' => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'ruangan' => $validated['ruangan'] ?? null,
            'link_zoom' => $validated['link_zoom'] ?? null,
            'komisi_tesis_id' => $validated['komisi_tesis_id'],
            'is_locked' => true,
            'nomor_surat_tugas' => self::generateNomorDokumen('ST', $tahap),
            'nomor_undangan' => self::generateNomorDokumen('UND', $tahap),
        ]);

        foreach ($validated['penguji'] as $p) {
            PengujiSidang::create([
                'sidang_id' => $sidang->id,
                'dosen_id' => $p['dosen_id'],
                'peran_penguji' => $p['peran_penguji']
            ]);
        }

        // Blast notifikasi undangan menguji ke seluruh dewan penguji +
        // notifikasi jadwal terkunci ke mahasiswa (Modul 9 — template
        // dari database, seluruh pengiriman tercatat ke notifikasi_log).
        // Kegagalan gateway WA TIDAK PERNAH menggagalkan plotting itu
        // sendiri (ditangani di dalam WhatsAppNotifierService).
        $sidang->load('pengujiSidangs.dosen', 'pengajuanTesis.mahasiswa');

        AuditLogger::log(
            $request->user(),
            'sidang.plotting',
            'AktivitasSidang',
            $sidang->id,
            "Plotting jadwal & dewan penguji {$tahap} untuk {$sidang->pengajuanTesis?->mahasiswa?->name}.",
            ['tahap' => $tahap, 'jumlah_penguji' => count($validated['penguji'])]
        );

        $lokasi = $sidang->ruangan ? "Ruang: {$sidang->ruangan}" : "Link Zoom: {$sidang->link_zoom}";
        $namaMahasiswa = $sidang->pengajuanTesis->mahasiswa->name ?? '-';
        $linkKalenderIcs = route('sidang.kalenderIcs', $sidang->id);
        $linkNaskah = $this->getLinkNaskah($sidang->pengajuanTesis, $tahap);
        [$linkSuratTugas, $linkUndangan] = $this->getLinkDokumenResmi($sidang, $tahap);
        $linkFormPenilaian = route('dashboard');

        foreach ($sidang->pengujiSidangs as $ps) {
            if (!$ps->dosen) continue;
            WhatsAppNotifierService::kirim('undangan_menguji', $ps->dosen, [
                'nama_dosen' => $ps->dosen->name,
                'nama_mahasiswa' => $namaMahasiswa,
                'tahap_sidang' => strtoupper($tahap),
                'waktu_mulai' => $sidang->waktu_mulai,
                'lokasi' => $lokasi,
                'link_surat_tugas' => $linkSuratTugas,
                'link_undangan' => $linkUndangan,
                'link_naskah' => $linkNaskah,
                'link_form_penilaian' => $linkFormPenilaian,
                'link_kalender_ics' => $linkKalenderIcs,
            ], ['sidang_id' => $sidang->id, 'tahap' => $tahap]);
        }

        if ($sidang->pengajuanTesis->mahasiswa) {
            WhatsAppNotifierService::kirim('jadwal_terkunci', $sidang->pengajuanTesis->mahasiswa, [
                'nama_mahasiswa' => $namaMahasiswa,
                'tahap_sidang' => strtoupper($tahap),
                'waktu_mulai' => $sidang->waktu_mulai,
                'lokasi' => $lokasi,
                'link_kalender_ics' => $linkKalenderIcs,
            ], ['sidang_id' => $sidang->id, 'tahap' => $tahap]);
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => "Plotting jadwal & penguji {$tahap} berhasil.", 'data' => $sidang->load('pengujiSidangs')]);
        }

        return redirect()->route('dashboard')->with('success', "Plotting jadwal & dewan penguji {$tahap} berhasil disimpan.");
    }

    /**
     * Link naskah mahasiswa yang relevan sesuai tahap sidang — dipakai di
     * pesan undangan menguji supaya penguji langsung dapat naskah lengkap.
     */
    private function getLinkNaskah($tesis, string $tahap): string
    {
        return match ($tahap) {
            'sempro' => $tesis->pendaftaranSempro->naskah_proposal_url ?? '(belum diunggah)',
            'semhas' => $tesis->pendaftaranSemhas->naskah_bab_1_5_url ?? '(belum diunggah)',
            'ujian' => $tesis->pendaftaranUjian->naskah_tesis_lengkap_url ?? '(belum diunggah)',
            default => '-',
        };
    }

    /**
     * Link Surat Tugas & Undangan resmi (PDF) per tahap. Sempro & Semhas
     * memakai template generik yang sama (lihat surat-tugas-sempro.blade.php
     * & undangan-sempro.blade.php — sudah tidak hardcode teks tahap). Ujian
     * Tesis dibedakan (perlu template Wadek I terpisah — lihat generateBundle()
     * di DocumentGeneratorService untuk daftar lengkap dokumen per tahap).
     */
    private function getLinkDokumenResmi(AktivitasSidang $sidang, string $tahap): array
    {
        if ($tahap === 'sempro' || $tahap === 'semhas') {
            $sufiks = $tahap === 'sempro' ? 'SEMPRO' : 'SEMHAS';
            return [
                route('dokumen.cetak', ['kode' => "SURAT-TUGAS-{$sufiks}", 'id' => $sidang->id]),
                route('dokumen.cetak', ['kode' => "UNDANGAN-{$sufiks}", 'id' => $sidang->id]),
            ];
        }

        // Ujian Tesis: Surat Tugas Wadek I sudah ada, Undangan Ujian belum
        // punya template resmi tersendiri — fallback sementara ke dashboard.
        return [
            route('dokumen.cetak', ['kode' => 'SURAT-TUGAS-WADEK1', 'id' => $sidang->id]),
            route('dashboard'),
        ];
    }

    /**
     * Auto-numbering nomor surat resmi (Surat Tugas / Undangan) — format:
     * {PREFIX}/{TAHAP}/{URUTAN 3-digit}/{TAHUN}, urutan dihitung dari jumlah
     * sidang tahap yang sama di tahun berjalan (+1). Dipanggil SEBELUM
     * AktivitasSidang::create() sehingga hitungan tidak menyertakan sidang
     * yang sedang dibuat sendiri (bebas off-by-one).
     */
    private static function generateNomorDokumen(string $prefix, string $tahap): string
    {
        $tahun = now()->year;
        $urutan = AktivitasSidang::where('tahap_sidang', $tahap)
            ->whereYear('created_at', $tahun)
            ->count() + 1;

        return sprintf('%s/%s/%03d/%d', $prefix, strtoupper($tahap), $urutan, $tahun);
    }
}
