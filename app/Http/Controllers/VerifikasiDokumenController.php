<?php

namespace App\Http\Controllers;

use App\Models\DokumenCetak;

class VerifikasiDokumenController extends Controller
{
    /**
     * GET /verifikasi/{hash} — halaman publik (tanpa login) yang dibuka
     * saat seseorang men-scan QR TTE di pojok dokumen. Menampilkan apakah
     * dokumen itu benar tercatat sah oleh sistem, dan kapan/kode apa.
     */
    public function show(string $hash)
    {
        $dokumen = DokumenCetak::with('dicetakOleh')->where('hash_verifikasi', $hash)->first();

        return view('verifikasi', [
            'dokumen' => $dokumen,
            'valid' => $dokumen !== null,
        ]);
    }
}
