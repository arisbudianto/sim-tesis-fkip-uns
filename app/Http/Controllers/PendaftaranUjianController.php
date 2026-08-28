<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranUjian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranUjianController extends Controller
{
    public function store(Request $request, $pengajuanId)
    {
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_tesis_lengkap_url' => 'required|string',
            'bukti_lulus_semhas_url' => 'nullable|string',
            'artikel_jurnal_url' => 'required|string',
            'prosiding_seminar_url' => 'required|string',
            'sertifikat_bahasa_url' => 'required|string',
            'skor_bahasa' => 'required|integer|min:475',
            'bukti_spp_terakhir_url' => 'required|string',
            'khs_kumulatif_url' => 'required|string',
            'surat_bebas_plagiasi_url' => 'required|string',
            'similarity_score' => 'required|numeric|max:25.00'
        ]);

        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pendaftaran Ujian Tesis wajib diajukan minimal H-14.'
            ], 422);
        }

        $ujian = PendaftaranUjian::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $validated
        );

        return response()->json(['status' => 'success', 'data' => $ujian]);
    }
}
