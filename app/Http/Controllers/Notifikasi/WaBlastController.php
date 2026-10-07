<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;
use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Models\NotifikasiLog;
use App\Domain\Notifikasi\Services\SidangNotifikasiData;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\Sidang\Models\AktivitasSidang;
use Illuminate\Http\Request;

class WaBlastController extends Controller
{
    public function blast(Request $request)
    {
        $this->authorizeKomisi($request);

        $validated = $request->validate([
            'sidang_id' => 'required|uuid|exists:aktivitas_sidangs,id',
            'target' => 'required|in:penguji,mahasiswa,semua',
        ]);

        $sidang = AktivitasSidang::with([
            'pengujiSidangs.dosen',
            'pengajuanTesis.mahasiswa',
            'pengajuanTesis.pendaftaranSempro',
            'pengajuanTesis.pendaftaranSemhas',
            'pengajuanTesis.pendaftaranUjian',
        ])->findOrFail($validated['sidang_id']);

        $tahap = $sidang->tahap_sidang;
        $mahasiswa = $sidang->pengajuanTesis?->mahasiswa;
        $namaMahasiswa = $mahasiswa?->name ?? '-';

        $hasil = [];

        if (in_array($validated['target'], ['penguji', 'semua'], true)) {
            foreach ($sidang->pengujiSidangs as $ps) {
                if (!$ps->dosen) {
                    continue;
                }
                $logs = WhatsAppNotifierService::kirimSemuaChannel(
                    'undangan_menguji',
                    $ps->dosen,
                    SidangNotifikasiData::untukUndangan($sidang, $ps->dosen),
                    ['sidang_id' => $sidang->id, 'tahap' => $tahap, 'manual_blast' => true]
                );

                foreach ($logs as $log) {
                    $hasil[] = $this->barisHasil($ps->dosen->name, $ps->peran_penguji, $log);
                }
            }
        }

        if (in_array($validated['target'], ['mahasiswa', 'semua'], true) && $mahasiswa) {
            $logs = WhatsAppNotifierService::kirimSemuaChannel(
                'jadwal_terkunci',
                $mahasiswa,
                SidangNotifikasiData::untukMahasiswa($sidang),
                ['sidang_id' => $sidang->id, 'tahap' => $tahap, 'manual_blast' => true]
            );

            foreach ($logs as $log) {
                $hasil[] = $this->barisHasil($mahasiswa->name, 'mahasiswa', $log);
            }
        }

        AuditLogger::log(
            $request->user(),
            'notifikasi.wa_blast',
            'AktivitasSidang',
            $sidang->id,
            "WA Blast {$tahap} {$namaMahasiswa} target={$validated['target']}.",
            ['target' => $validated['target'], 'jumlah' => count($hasil)]
        );

        $sukses = collect($hasil)->where('status', 'terkirim')->count();
        $gagal = collect($hasil)->where('status', '!=', 'terkirim')->count();
        $kanal = implode(' + ', array_map('ucfirst', WhatsAppNotifierService::channelAktif()));
        $pesan = "Notifikasi ({$kanal}) dikirim: {$sukses} pengiriman berhasil, {$gagal} gagal/pending (dihitung per channel per penerima). Cek log jika email/nomor WA kosong atau pengiriman belum dikonfigurasi.";

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => $pesan, 'hasil' => $hasil]);
        }

        return back()->with('success', $pesan)->with('wa_blast_hasil', $hasil);
    }

    /** Satu baris hasil per (penerima, channel) untuk ditampilkan di layar. */
    protected function barisHasil(string $nama, string $peran, $log): array
    {
        return [
            'nama' => $nama,
            'peran' => $peran,
            'kanal' => $log->channel === 'email' ? 'Email' : 'WhatsApp',
            'tujuan' => $log->nomor_tujuan,
            'status' => $log->status,
            'error' => $log->error_message,
        ];
    }

    protected function authorizeKomisi(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi'], true), 403);
    }
}
