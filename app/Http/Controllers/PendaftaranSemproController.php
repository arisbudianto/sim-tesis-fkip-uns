<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSempro;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranSemproController extends Controller
{
    public function store(Request $request, $pengajuanId)
    {
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_proposal_url' => 'required|string',
            'bukti_spp_url' => 'required|string',
            'khs_url' => 'required|string'
        ]);

        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pendaftaran Sempro wajib diajukan minimal H-14 (14 hari sebelum pelaksanaan sidang).'
            ], 422);
        }

        $sempro = PendaftaranSempro::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $validated
        );

        return response()->json(['status' => 'success', 'data' => $sempro]);
    }
}
