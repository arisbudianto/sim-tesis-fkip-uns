<?php

namespace App\Http\Controllers;

use App\Models\LogbookBimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LogbookBimbinganController extends Controller
{
    public function store(Request $request, $pengajuanId)
    {
        $validated = $request->validate([
            'dosen_id' => 'required|uuid|exists:users,id',
            'tanggal_bimbingan' => 'required|date',
            'materi_bimbingan' => 'required|string'
        ]);

        $logbook = LogbookBimbingan::create([
            'pengajuan_tesis_id' => $pengajuanId,
            'dosen_id' => $validated['dosen_id'],
            'tanggal_bimbingan' => $validated['tanggal_bimbingan'],
            'materi_bimbingan' => $validated['materi_bimbingan'],
            'status_approval' => 'pending'
        ]);

        return response()->json(['status' => 'success', 'data' => $logbook], 201);
    }

    public function approve(Request $request, $id)
    {
        $logbook = LogbookBimbingan::findOrFail($id);
        $logbook->update([
            'status_approval' => 'approved',
            'catatan_dosen' => $request->input('catatan_dosen', 'Disetujui.'),
            'approved_at' => now(),
            'qr_signature_hash' => Str::random(40)
        ]);

        return response()->json(['status' => 'success', 'message' => 'Logbook disetujui.', 'data' => $logbook]);
    }
}
