<?php

namespace App\Http\Controllers\Notifikasi;

use App\Http\Controllers\Controller;
use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Models\NotifikasiLog;
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
        $lokasi = $sidang->ruangan
            ? 'Ruang: '.$sidang->ruangan
            : ($sidang->link_zoom ? 'Link Zoom: '.$sidang->link_zoom : 'Lokasi menyusul');
        $waktu = optional($sidang->waktu_mulai)?->translatedFormat('d F Y H:i') ?? '-';

        $sufiks = match ($tahap) {
            'semhas' => 'SEMHAS',
            'ujian' => 'WADEK1',
            default => 'SEMPRO',
        };
        $linkSurat = route('dokumen.cetak', ['kode' => $tahap === 'ujian' ? 'SURAT-TUGAS-WADEK1' : "SURAT-TUGAS-{$sufiks}", 'id' => $sidang->id]);
        $linkUndangan = $tahap === 'ujian'
            ? route('dashboard')
            : route('dokumen.cetak', ['kode' => "UNDANGAN-{$sufiks}", 'id' => $sidang->id]);

        $hasil = [];

        if (in_array($validated['target'], ['penguji', 'semua'], true)) {
            foreach ($sidang->pengujiSidangs as $ps) {
                if (!$ps->dosen) {
                    continue;
                }
                $log = WhatsAppNotifierService::kirim('undangan_menguji', $ps->dosen, [
                    'nama_dosen' => $ps->dosen->name,
                    'nama_mahasiswa' => $namaMahasiswa,
                    'tahap_sidang' => strtoupper($tahap),
                    'waktu_mulai' => $waktu,
                    'lokasi' => $lokasi,
                    'link_surat_tugas' => $linkSurat,
                    'link_undangan' => $linkUndangan,
                    'link_naskah' => $this->linkNaskah($sidang, $tahap),
                    'link_form_penilaian' => route('dashboard'),
                    'link_kalender_ics' => route('sidang.kalenderIcs', $sidang->id),
                ], ['sidang_id' => $sidang->id, 'tahap' => $tahap, 'manual_blast' => true]);

                $hasil[] = [
                    'nama' => $ps->dosen->name,
                    'peran' => $ps->peran_penguji,
                    'nomor' => $ps->dosen->nomor_wa,
                    'status' => $log->status,
                    'error' => $log->error_message,
                ];
            }
        }

        if (in_array($validated['target'], ['mahasiswa', 'semua'], true) && $mahasiswa) {
            $log = WhatsAppNotifierService::kirim('jadwal_terkunci', $mahasiswa, [
                'nama_mahasiswa' => $namaMahasiswa,
                'tahap_sidang' => strtoupper($tahap),
                'waktu_mulai' => $waktu,
                'lokasi' => $lokasi,
                'link_kalender_ics' => route('sidang.kalenderIcs', $sidang->id),
            ], ['sidang_id' => $sidang->id, 'tahap' => $tahap, 'manual_blast' => true]);

            $hasil[] = [
                'nama' => $mahasiswa->name,
                'peran' => 'mahasiswa',
                'nomor' => $mahasiswa->nomor_wa,
                'status' => $log->status,
                'error' => $log->error_message,
            ];
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
        $pesan = "WA Blast dikirim: {$sukses} berhasil, {$gagal} gagal/pending. Cek log jika nomor WA kosong atau gateway belum dikonfigurasi.";

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => $pesan, 'hasil' => $hasil]);
        }

        return back()->with('success', $pesan)->with('wa_blast_hasil', $hasil);
    }

    protected function linkNaskah(AktivitasSidang $sidang, string $tahap): string
    {
        $tesis = $sidang->pengajuanTesis;
        return match ($tahap) {
            'sempro' => $tesis?->pendaftaranSempro?->naskah_proposal_url ?? '(belum diunggah)',
            'semhas' => $tesis?->pendaftaranSemhas?->naskah_bab_1_5_url ?? '(belum diunggah)',
            'ujian' => $tesis?->pendaftaranUjian?->naskah_tesis_lengkap_url ?? '(belum diunggah)',
            default => '-',
        };
    }

    protected function authorizeKomisi(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['komisi_tesis', 'kaprodi', 'admin_prodi'], true), 403);
    }
}
