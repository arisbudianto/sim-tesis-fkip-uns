<?php

namespace App\Http\Controllers;

use App\Models\PengajuanTesis;
use App\Models\AktivitasSidang;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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

        return view('dashboard', compact('stats'));
    }
}
