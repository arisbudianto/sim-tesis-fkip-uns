<?php

namespace App\Http\Controllers;

use App\Services\DocumentGeneratorService;
use Illuminate\Http\Request;

class DokumenCetakController extends Controller
{
    public function __construct(protected DocumentGeneratorService $generator)
    {
    }

    /**
     * GET /dokumen/cetak/{kode}/{id}
     * Contoh: /dokumen/cetak/FPT-TI-01/{aktivitas_sidang_id}
     *         /dokumen/cetak/BAP/{manajemen_nilai_sidang_id}
     */
    public function show(Request $request, string $kode, string $id)
    {
        return $this->generator->generate($kode, $id, $request->user());
    }
}
