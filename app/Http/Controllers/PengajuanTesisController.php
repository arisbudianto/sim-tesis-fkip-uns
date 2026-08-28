<?php

namespace App\Http\Controllers;

use App\Models\PengajuanTesis;
use App\Models\User;
use App\Services\AdvisorQuotaEngine;
use Illuminate\Http\Request;

class PengajuanTesisController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => 'required|uuid|exists:users,id',
            'judul_tesis' => 'required|string|max:500',
            'bidang_fokus' => 'required|string',
            'abstrak_rencana' => 'nullable|string'
        ]);

        $pengajuan = PengajuanTesis::create($validated);

        return response()->json(['status' => 'success', 'data' => $pengajuan], 201);
    }

    public function alokasiPembimbing(Request $request, $id)
    {
        $validated = $request->validate([
            'pembimbing_1_id' => 'required|uuid|exists:users,id',
            'pembimbing_2_id' => 'required|uuid|exists:users,id|different:pembimbing_1_id',
            'nomor_sk_pembimbing' => 'required|string',
            'tanggal_sk_pembimbing' => 'required|date'
        ]);

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_1_id'])) {
            return response()->json(['status' => 'error', 'message' => 'Pembimbing 1 melebihi kuota bimbingan!'], 422);
        }

        if (!AdvisorQuotaEngine::checkQuota($validated['pembimbing_2_id'])) {
            return response()->json(['status' => 'error', 'message' => 'Pembimbing 2 melebihi kuota bimbingan!'], 422);
        }

        $pengajuan = PengajuanTesis::findOrFail($id);
        $pengajuan->update($validated);

        return response()->json(['status' => 'success', 'message' => 'Dosen pembimbing 1 & 2 berhasil dialokasikan', 'data' => $pengajuan]);
    }
}
