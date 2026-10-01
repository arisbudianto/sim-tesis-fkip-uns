<?php

namespace App\Http\Controllers\Dokumen;

use App\Http\Controllers\Controller;

use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Notifikasi\Services\CalendarIcsService;
use App\Domain\Dokumen\Services\DocumentGeneratorService;
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
     *
     * ?regenerate=1 — paksa buat versi baru (mis. data sumber berubah
     * setelah dokumen pertama kali dicetak). Hanya pengendali akademik
     * (Komisi Tesis/Kaprodi/Admin Prodi) yang boleh melakukan ini —
     * mahasiswa/dosen hanya bisa melihat dokumen yang sudah ada (idempoten).
     */
    public function show(Request $request, string $kode, string $id)
    {
        $forceRegenerate = $request->boolean('regenerate');

        if ($forceRegenerate && !$request->user()?->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi'])) {
            abort(403, 'Hanya Komisi Tesis/Kaprodi/Admin Prodi yang boleh membuat ulang (regenerate) dokumen resmi.');
        }

        return $forceRegenerate
            ? $this->generator->regenerate($kode, $id, $request->user())
            : $this->generator->generate($kode, $id, $request->user());
    }

    /**
     * GET /dokumen/bundle/{sidangId} — "1-Click Print Bundle": gabungkan
     * seluruh dokumen resmi terkait SATU sidang jadi satu file PDF siap
     * cetak untuk arsip manual program studi.
     */
    public function bundle(Request $request, string $sidangId)
    {
        return $this->generator->generateBundle($sidangId, $request->user());
    }

    /**
     * GET /sidang/{sidangId}/kalender.ics
     * Sinkronisasi jadwal sidang ke Google Calendar/Outlook/Apple Calendar
     * dosen — alternatif ringan dari integrasi OAuth Google Calendar API
     * penuh, cukup file .ics yang bisa diimpor/di-subscribe langsung.
     */
    public function downloadIcs(string $sidangId)
    {
        $sidang = AktivitasSidang::findOrFail($sidangId);
        $ics = CalendarIcsService::generate($sidang);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="sidang-' . $sidang->tahap_sidang . '.ics"',
        ]);
    }
}
