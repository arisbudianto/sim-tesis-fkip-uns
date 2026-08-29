<?php

namespace App\Http\Controllers;

use App\Models\PengajuanTesis;
use App\Models\AktivitasSidang;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function publicPage()
    {
        $stats = [
            'total_pengajuan' => PengajuanTesis::count(),
            'tahap_1_bimbingan' => PengajuanTesis::where('status_tahap', 'tahap_1_bimbingan')->count(),
            'tahap_2_sempro' => PengajuanTesis::where('status_tahap', 'tahap_2_sempro')->count(),
            'tahap_3_semhas' => PengajuanTesis::where('status_tahap', 'tahap_3_semhas')->count(),
            'tahap_4_ujian' => PengajuanTesis::where('status_tahap', 'tahap_4_ujian')->count(),
            'selesai_yudisium' => PengajuanTesis::where('status_tahap', 'selesai_yudisium')->count(),
        ];
        return view('public.index', compact('stats'));
    }

    public function panduan()
    {
        return view('public.panduan');
    }

    public function index()
    {
        $stats = [
            'total_pengajuan' => PengajuanTesis::count(),
            'tahap_1_bimbingan' => PengajuanTesis::where('status_tahap', 'tahap_1_bimbingan')->count(),
            'tahap_2_sempro' => PengajuanTesis::where('status_tahap', 'tahap_2_sempro')->count(),
            'tahap_3_semhas' => PengajuanTesis::where('status_tahap', 'tahap_3_semhas')->count(),
            'tahap_4_ujian' => PengajuanTesis::where('status_tahap', 'tahap_4_ujian')->count(),
            'selesai_yudisium' => PengajuanTesis::where('status_tahap', 'selesai_yudisium')->count(),
            'total_sidang' => AktivitasSidang::count()
        ];

        $pengajuans = PengajuanTesis::with(['mahasiswa', 'pembimbing1', 'pembimbing2', 'logbooks.dosen', 'aktivitasSidangs.pengujiSidangs.dosen'])->latest()->get();
        $dosens = User::where('role', 'dosen')->get();
        $komisi = User::where('is_komisi_tesis', true)->first();
        $mahasiswas = User::where('role', 'mahasiswa')->get();
        $sidangs = AktivitasSidang::with(['pengajuanTesis.mahasiswa', 'pengujiSidangs.dosen', 'manajemenNilai'])->latest()->get();

        return view('dashboard', compact('stats', 'pengajuans', 'dosens', 'komisi', 'mahasiswas', 'sidangs'));
    }
}
