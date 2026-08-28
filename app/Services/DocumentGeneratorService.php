<?php

namespace App\Services;

use App\Models\AktivitasSidang;
use App\Models\PengajuanTesis;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentGeneratorService
{
    // FR-04, FR-08: Auto-generate Surat Tugas Dekan & Undangan Menguji
    public static function generateSuratTugas(AktivitasSidang $sidang)
    {
        $qrCode = base64_encode(QrCode::format('svg')->size(90)->generate("VALID-UNS-ST-{$sidang->id}"));
        $pdf = Pdf::loadView('pdf.surat_tugas', compact('sidang', 'qrCode'));
        return $pdf;
    }

    // FR-09: Berita Acara Ujian Tesis (BAP) & Rekapitulasi Nilai
    public static function generateBAP(AktivitasSidang $sidang)
    {
        $qrCode = base64_encode(QrCode::format('svg')->size(90)->generate("VALID-UNS-BAP-{$sidang->id}"));
        $pdf = Pdf::loadView('pdf.berita_acara', compact('sidang', 'qrCode'));
        return $pdf;
    }

    // FR-10: Lembar Persetujuan Revisi Akhir Menuju Wisuda
    public static function generateLembarRevisi(AktivitasSidang $sidang)
    {
        $qrCode = base64_encode(QrCode::format('svg')->size(90)->generate("VALID-UNS-YUDISIUM-{$sidang->id}"));
        $pdf = Pdf::loadView('pdf.lembar_revisi', compact('sidang', 'qrCode'));
        return $pdf;
    }
}
