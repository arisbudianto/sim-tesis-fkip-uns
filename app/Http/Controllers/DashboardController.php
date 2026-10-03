<?php

namespace App\Http\Controllers;

use App\Domain\Dokumen\Models\DokumenCetak;
use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use App\Models\AuditLog;
use App\Models\User;
use App\Domain\StateEngine\Models\StateTransitionLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function publicPage()
    {
        $stats = $this->statistikPipeline();
        return view('public.index', compact('stats'));
    }

    public function panduan()
    {
        return view('public.panduan');
    }

    /**
     * Modul 11: dashboard utama — data yang dikirim ke view SELALU
     * di-scope sesuai role user yang login (acceptance criteria eksplisit
     * "Setiap dashboard hanya menampilkan data sesuai lingkup akses peran").
     * Statistik pipeline (angka agregat, bukan nama individu) tetap sama
     * untuk semua role karena bersifat program-wide, bukan data pribadi.
     */
    public function index(Request $request)
    {
        // "/" tidak lagi di-gate oleh middleware 'auth' (lihat routes/web.php),
        // supaya tamu yang belum login diarahkan ke /beranda, bukan /login.
        if (!$request->user()) {
            return redirect()->route('public.index');
        }

        $user = $request->user();
        $stats = $this->statistikPipeline();

        // Data operasional lama (dipakai tab Pengajuan/Pendaftaran/Sidang/
        // Penilaian/Revisi yang sudah punya gating aksi per role sendiri).
        // Tetap disediakan untuk role pengendali akademik (mereka memang
        // perlu melihat seluruh data untuk menjalankan tugasnya) — TAPI
        // konten ringkasan "Overview" (lihat resources/views/dashboard/roles/*)
        // sekarang selalu di-scope spesifik per role, bukan menampilkan
        // tabel semua mahasiswa ke siapa pun yang login.
        $pengajuans = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'usulanPembimbing1', 'usulanPembimbing2', 'aktivitasSidangs.pengujiSidangs.dosen', 'pendaftaranSempro', 'pendaftaranSemhas', 'pendaftaranUjian'])->latest()->get();
        $dosens = User::where('role', 'dosen')->get();
        $komisi = User::where('role', 'komisi_tesis')->first();
        $mahasiswas = User::where('role', 'mahasiswa')->orderBy('identifier')->get();
        $stafs = User::whereIn('role', ['dosen', 'kaprodi', 'komisi_tesis', 'admin_prodi'])->orderBy('name')->get();
        $sidangs = AktivitasSidang::with(['pengajuanTesis.mahasiswa', 'pengujiSidangs.dosen', 'manajemenNilai'])->latest()->get();

        // Data ringkasan KHUSUS role user yang login (inti Modul 11).
        $roleData = match (true) {
            $user->hasRole('mahasiswa') => $this->dataMahasiswa($user),
            $user->hasRole('komisi_tesis') => $this->dataKomisiTesis(),
            $user->hasRole('admin_prodi') => $this->dataAdminProdi(),
            $user->hasRole('kaprodi') => $this->dataKaprodi($user),
            $user->hasRole('dosen') => $this->dataDosen($user),
            default => [],
        };

        $auditLogs = collect();
        $stateLogs = collect();
        if ($user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            $auditLogs = AuditLog::orderByDesc('created_at')->limit(20)->get();
            $stateLogs = StateTransitionLog::orderByDesc('created_at')->limit(20)->get();
        }

        return view('dashboard', array_merge(
            compact('stats', 'pengajuans', 'dosens', 'komisi', 'mahasiswas', 'stafs', 'sidangs', 'auditLogs', 'stateLogs'),
            $roleData
        ));
    }

    protected function statistikPipeline(): array
    {
        return [
            'total_pengajuan' => PengajuanTesis::count(),
            'tahap_1_bimbingan' => PengajuanTesis::where('status_tahap', 'tahap_1_bimbingan')->count(),
            'tahap_2_sempro' => PengajuanTesis::where('status_tahap', 'tahap_2_sempro')->count(),
            'tahap_3_semhas' => PengajuanTesis::where('status_tahap', 'tahap_3_semhas')->count(),
            'tahap_4_ujian' => PengajuanTesis::where('status_tahap', 'tahap_4_ujian')->count(),
            'selesai_yudisium' => PengajuanTesis::where('status_tahap', 'selesai_yudisium')->count(),
            'total_sidang' => AktivitasSidang::count(),
        ];
    }

    /**
     * Mahasiswa: riwayat proses per tahap, status sidang berikutnya,
     * dokumen yang perlu diunggah — HANYA milik dirinya sendiri.
     */
    protected function dataMahasiswa(User $user): array
    {
        $myPengajuan = PengajuanTesis::with([
            'pembimbing1', 'pembimbing2', 'usulanPembimbing1', 'usulanPembimbing2', 'stateTransitionLogs',
            'pendaftaranSempro', 'pendaftaranSemhas', 'pendaftaranUjian',
            'aktivitasSidangs.pengujiSidangs.dosen', 'aktivitasSidangs.manajemenNilai',
        ])->where('mahasiswa_id', $user->id)->first();

        return ['myPengajuan' => $myPengajuan];
    }

    /**
     * Dosen Pembimbing & Dewan Penguji (satu akun 'dosen' bisa berperan
     * sebagai keduanya sekaligus — lihat catatan multi-peran Modul 3):
     * daftar mahasiswa bimbingan & status tiap tahap, approval pending,
     * PLUS jadwal sidang yang ditugaskan sebagai penguji & status
     * penilaian yang sudah/belum diisi.
     */
    protected function dataDosen(User $user): array
    {
        $myBimbingan = PengajuanTesis::with(['mahasiswa', 'pendaftaranSempro', 'pendaftaranSemhas', 'pendaftaranUjian'])
            ->where(function ($q) use ($user) {
                $q->where('pembimbing_1_id', $user->id)->orWhere('pembimbing_2_id', $user->id);
            })->get();

        $myTugasPenguji = PengujiSidang::with(['sidang.pengajuanTesis.mahasiswa'])
            ->where('dosen_id', $user->id)
            ->whereHas('sidang', fn ($q) => $q->where('waktu_mulai', '>=', now()->subDays(30)))
            ->get();

        return ['myBimbingan' => $myBimbingan, 'myTugasPenguji' => $myTugasPenguji];
    }

    /**
     * Komisi Tesis: kuota bimbingan per dosen, jadwal sidang keseluruhan
     * (kalender), rekap nilai lintas tahap.
     */
    protected function dataKomisiTesis(): array
    {
        $dosenKuota = User::where('role', 'dosen')->orderBy('name')->get()->map(function ($dosen) {
            $terpakai = PengajuanTesis::where(function ($q) use ($dosen) {
                $q->where('pembimbing_1_id', $dosen->id)->orWhere('pembimbing_2_id', $dosen->id);
            })->where('status_tahap', '!=', 'selesai_yudisium')->count();

            return [
                'name' => $dosen->name,
                'kuota_maksimum' => $dosen->kuota_bimbingan_maks,
                'kuota_terpakai' => $terpakai,
                'sisa_kuota' => max(0, $dosen->kuota_bimbingan_maks - $terpakai),
            ];
        });

        $jadwalSidangMendatang = AktivitasSidang::with(['pengajuanTesis.mahasiswa'])
            ->where('waktu_mulai', '>=', now())
            ->orderBy('waktu_mulai')
            ->get();

        $rekapNilaiLintasTahap = AktivitasSidang::with('manajemenNilai')
            ->whereHas('manajemenNilai')
            ->get()
            ->groupBy('tahap_sidang')
            ->map(fn ($grup) => [
                'jumlah' => $grup->count(),
                'rata_rata' => round($grup->avg(fn ($s) => $s->manajemenNilai->nilai_rata_rata), 2),
            ]);

        return [
            'dosenKuota' => $dosenKuota,
            'jadwalSidangMendatang' => $jadwalSidangMendatang,
            'rekapNilaiLintasTahap' => $rekapNilaiLintasTahap,
        ];
    }

    /**
     * Admin Prodi: antrean verifikasi berkas, status penerbitan surat.
     */
    protected function dataAdminProdi(): array
    {
        $antreanVerifikasiSempro = PengajuanTesis::with('mahasiswa', 'pendaftaranSempro')
            ->whereHas('pendaftaranSempro', fn ($q) => $q->where('status_verifikasi_admin', 'pending'))
            ->get();

        $antreanVerifikasiSemhas = PengajuanTesis::with('mahasiswa', 'pendaftaranSemhas')
            ->whereHas('pendaftaranSemhas', fn ($q) => $q->where('status_verifikasi_admin', 'pending'))
            ->get();

        $antreanVerifikasiUjian = PengajuanTesis::with('mahasiswa', 'pendaftaranUjian')
            ->whereHas('pendaftaranUjian', fn ($q) => $q->where('status_verifikasi_admin', 'pending'))
            ->get();

        $dokumenTerbaru = DokumenCetak::with('dicetakOleh')->latest('dicetak_at')->limit(15)->get();

        return [
            'antreanVerifikasiSempro' => $antreanVerifikasiSempro,
            'antreanVerifikasiSemhas' => $antreanVerifikasiSemhas,
            'antreanVerifikasiUjian' => $antreanVerifikasiUjian,
            'dokumenTerbaru' => $dokumenTerbaru,
        ];
    }

    /**
     * Kaprodi: capaian masa studi, tingkat kelulusan per tahap, legalitas
     * dewan penguji (baca: pengesahan revisi/gateway yudisium) yang
     * menunggu tanda tangan Kaprodi.
     */
    protected function dataKaprodi(?User $user = null): array
    {
        $user = $user ?: request()->user();
        $dosenData = $user && $user->hasRole('dosen') ? $this->dataDosen($user) : ['myBimbingan' => collect(), 'myTugasPenguji' => collect()];

        $lulus = PengajuanTesis::where('status_tahap', 'selesai_yudisium')->get();
        $totalAktif = PengajuanTesis::count();

        $tingkatKelulusan = $totalAktif > 0 ? round(($lulus->count() / $totalAktif) * 100, 1) : 0;

        $capaianMasaStudi = $lulus->isNotEmpty()
            ? round($lulus->avg(fn ($p) => $p->created_at->diffInMonths(
                $p->stateTransitionLogs()->where('to_state', 'selesai_yudisium')->value('created_at') ?? now()
            )), 1)
            : null;

        $legalitasPending = RevisiDokumen::with(['sidang.pengajuanTesis.mahasiswa'])
            ->where('status_approval_semua', true)
            ->where('pengesahan_kaprodi', false)
            ->get();

        return array_merge($dosenData, [
            'tingkatKelulusan' => $tingkatKelulusan,
            'capaianMasaStudiBulan' => $capaianMasaStudi,
            'legalitasPending' => $legalitasPending,
        ]);
    }
}
