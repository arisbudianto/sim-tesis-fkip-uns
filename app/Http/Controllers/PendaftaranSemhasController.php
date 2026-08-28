<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSemhas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PendaftaranSemhasController extends Controller
{
    public function store(Request $request, $pengajuanId)
    {
        $validated = $request->validate([
            'jadwal_usulan_sidang' => 'required|date',
            'naskah_bab_1_5_url' => 'required|string',
            'draf_artikel_ilmiah_urls' => 'required|array|min:2',
            'bukti_status_under_review_url' => 'required|string',
            'bukti_spp_url' => 'required|string'
        ]);

        $minDate = Carbon::now()->addDays(14);
        if (Carbon::parse($validated['jadwal_usulan_sidang'])->lt($minDate)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pendaftaran Semhas wajib diajukan minimal H-14.'
            ], 422);
        }

        $semhas = PendaftaranSemhas::updateOrCreate(
            ['pengajuan_tesis_id' => $pengajuanId],
            $validated
        );

        return response()->json(['status' => 'success', 'data' => $semhas]);
    }
}
