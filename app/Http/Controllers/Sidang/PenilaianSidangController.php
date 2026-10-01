<?php

namespace App\Http\Controllers\Sidang;

use App\Http\Controllers\Controller;

use App\Domain\Audit\Services\AuditLogger;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\Sidang\Models\ManajemenNilaiSidang;
use App\Domain\Sidang\Models\PengujiSidang;
use App\Domain\StateEngine\Services\LifecycleStateMachine;
use App\Domain\UjianTesis\Models\RevisiDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PenilaianSidangController extends Controller
{
    /**
     * Rubrik penilaian BERBEDA tergantung tahap sidang (koreksi asumsi
     * keliru sebelumnya yang menyeragamkan semua tahap ke 10 indikator):
     * - Sempro/Semhas (FPT-TI-03): rubrik 10 indikator, 10% per indikator.
     * - Ujian Tesis (FR-09): rubrik 4 dimensi (I. Naskah, II. Publikasi,
     *   III. Presentasi, IV. Tanya Jawab), 25% per dimensi.
     */
    public function showPenilaian(Request $request, $sidangId)
    {
        $sidang = AktivitasSidang::with([
            'pengujiSidangs.dosen',
            'pengajuanTesis.mahasiswa',
            'pengajuanTesis.pembimbing1',
            'pengajuanTesis.pembimbing2',
            'manajemenNilai',
        ])->findOrFail($sidangId);

        $user = $request->user();
        $isPengendali = $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
        $isDosenPenguji = $sidang->pengujiSidangs->contains('dosen_id', $user->id);

        abort_unless($isPengendali || $isDosenPenguji || $user->hasRole('mahasiswa'), 403);

        return view('sidang.penilaian', [
            'sidang' => $sidang,
            'isPengendali' => $isPengendali,
            'isDosenPenguji' => $isDosenPenguji,
        ]);
    }

    public function submitNilaiPenguji(Request $request, $sidangId)
    {
        $sidang = AktivitasSidang::findOrFail($sidangId);
        $user = $request->user();
        $isPengendali = $user->hasAnyRole(['komisi_tesis', 'kaprodi', 'admin_prodi']);
        $pakai4Dimensi = $sidang->tahap_sidang === 'ujian';

        $rules = [
            // dosen_id di body HANYA dipakai kalau aktornya pengendali akademik
            // (override administratif). Untuk dosen biasa, diabaikan — identitas
            // diambil paksa dari user yang login (lihat $dosenId di bawah),
            // supaya seorang dosen TIDAK BISA submit nilai atas nama dosen lain
            // hanya dengan mengubah field ini di form.
            'dosen_id' => 'nullable|uuid|exists:users,id',
            'catatan_revisi' => 'nullable|string',
        ];

        if ($pakai4Dimensi) {
            $rules['nilai_dimensi_1_naskah'] = 'required|numeric|min:0|max:100';
            $rules['nilai_dimensi_2_publikasi'] = 'required|numeric|min:0|max:100';
            $rules['nilai_dimensi_3_presentasi'] = 'required|numeric|min:0|max:100';
            $rules['nilai_dimensi_4_tanyajawab'] = 'required|numeric|min:0|max:100';
        } else {
            for ($i = 1; $i <= 10; $i++) {
                $rules["nilai_indikator_{$i}"] = 'required|numeric|min:0|max:100';
            }
        }

        $validated = $request->validate($rules);

        $dosenId = ($isPengendali && !empty($validated['dosen_id']))
            ? $validated['dosen_id']
            : $user->id;

        $penguji = PengujiSidang::where('sidang_id', $sidangId)
            ->where('dosen_id', $dosenId)
            ->first();

        if (!$penguji) {
            $msg = $isPengendali
                ? 'Dosen yang dipilih tidak tercatat sebagai penguji pada sidang ini.'
                : 'Anda tidak tercatat sebagai penguji pada sidang ini, sehingga tidak bisa memberi nilai.';
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 403)
                : back()->withErrors(['error' => $msg]);
        }

        $dataNilai = [];
        $total = 0;

        if ($pakai4Dimensi) {
            $bobot = config('penilaian.bobot_dimensi');
            foreach (['nilai_dimensi_1_naskah', 'nilai_dimensi_2_publikasi', 'nilai_dimensi_3_presentasi', 'nilai_dimensi_4_tanyajawab'] as $kolom) {
                $dataNilai[$kolom] = $validated[$kolom];
                $total += $validated[$kolom] * $bobot[$kolom];
            }
        } else {
            $bobot = config('penilaian.bobot_indikator');
            for ($i = 1; $i <= 10; $i++) {
                $nilai = $validated["nilai_indikator_{$i}"];
                $dataNilai["nilai_indikator_{$i}"] = $nilai;
                $total += $nilai * $bobot[$i];
            }
        }

        $penguji->update(array_merge($dataNilai, [
            'nilai_total_angka' => $total,
            'catatan_revisi' => $validated['catatan_revisi'] ?? null,
            'presensi_kehadiran' => true,
            'qr_signature_hash' => Str::random(40)
        ]));

        AuditLogger::log(
            $user,
            'sidang.inputNilai',
            'PengujiSidang',
            $penguji->id,
            "Nilai sidang #{$sidangId} diisi untuk dosen {$penguji->dosen_id}" . ($isPengendali && $dosenId !== $user->id ? ' (override oleh pengendali akademik)' : ''),
            ['nilai_total' => $total, 'rubrik' => $pakai4Dimensi ? '4_dimensi' : '10_indikator']
        );

        $penguji->load('dosen');

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Nilai penguji tersimpan.', 'data' => $penguji]);
        }

        return redirect()
            ->route('sidang.penilaian', $sidangId)
            ->with('success', 'Nilai ' . ($penguji->dosen->name ?? 'penguji') . ' berhasil disimpan. Nilai dapat diedit ulang jika ada salah input.');
    }

    public function rekapNilaiKomisi(Request $request, $sidangId)
    {
        $this->authorize('rekap', AktivitasSidang::findOrFail($sidangId));

        $validated = $request->validate([
            'komisi_tesis_validator_id' => 'required|uuid|exists:users,id',
            'keputusan_sidang' => 'required|in:lulus_tanpa_revisi,lulus_revisi_ringan,lulus_revisi_berat,ujian_ulang',
            'batas_waktu_revisi' => 'nullable|date'
        ]);

        $sidang = AktivitasSidang::with('pengujiSidangs')->findOrFail($sidangId);

        // WAJIB: seluruh penguji harus sudah mengisi nilai sebelum rekap
        // dihitung. Tanpa cek ini, rata-rata bisa keliru rendah (nilai
        // kosong ikut dihitung seolah 0) atau bahkan menghasilkan rekap
        // untuk sidang yang belum benar-benar selesai dinilai.
        $belumMenilai = $sidang->pengujiSidangs->whereNull('nilai_total_angka');
        if ($belumMenilai->isNotEmpty()) {
            $namaBelum = $belumMenilai->map(fn ($p) => $p->dosen->name ?? $p->dosen_id)->implode(', ');
            $msg = "Rekapitulasi belum bisa dihitung: {$belumMenilai->count()} dari {$sidang->pengujiSidangs->count()} "
                 . "penguji belum mengisi nilai — {$namaBelum}.";
            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors(['error' => $msg]);
        }

        $avg = $sidang->pengujiSidangs->avg('nilai_total_angka');

        $grade = 'TIDAK_LULUS';
        foreach (config('penilaian.ambang_grade') as $ambang) {
            if ($avg >= $ambang['min']) {
                $grade = $ambang['grade'];
                break;
            }
        }

        $rekap = ManajemenNilaiSidang::updateOrCreate(
            ['sidang_id' => $sidangId],
            [
                'komisi_tesis_validator_id' => $validated['komisi_tesis_validator_id'],
                'nilai_rata_rata' => $avg,
                'grade_kelulusan' => $grade,
                'keputusan_sidang' => $validated['keputusan_sidang'],
                'batas_waktu_revisi' => $validated['batas_waktu_revisi'] ?? null,
                'qr_bap_hash' => Str::random(40)
            ]
        );

        AuditLogger::log(
            $request->user(),
            'sidang.rekapKomisi',
            'ManajemenNilaiSidang',
            $rekap->id,
            "Rekap BAP sidang #{$sidangId}: {$validated['keputusan_sidang']} (grade {$grade}).",
            ['keputusan' => $validated['keputusan_sidang'], 'grade' => $grade, 'rata_rata' => $avg]
        );

        // Modul 9: notifikasi hasil sidang/kelulusan ke mahasiswa.
        $sidang->loadMissing('pengajuanTesis.mahasiswa');
        if ($sidang->pengajuanTesis?->mahasiswa) {
            WhatsAppNotifierService::kirim('hasil_kelulusan', $sidang->pengajuanTesis->mahasiswa, [
                'nama_mahasiswa' => $sidang->pengajuanTesis->mahasiswa->name,
                'tahap_sidang' => strtoupper($sidang->tahap_sidang),
                'keputusan_sidang' => str_replace('_', ' ', $validated['keputusan_sidang']),
                'grade' => $grade,
                'nilai_rata_rata' => number_format($avg, 2),
            ], ['sidang_id' => $sidangId, 'rekap_id' => $rekap->id]);
        }

        $catatanTransisi = $this->setelahRekapMajuTahap($sidang, $validated['keputusan_sidang'], $request->user());

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Rekapitulasi BAP selesai.', 'data' => $rekap]);
        }

        $pesan = 'Rekapitulasi & keputusan sidang disimpan (grade '.$grade.', rata-rata '.number_format($avg, 2).').';
        if ($catatanTransisi) {
            $pesan .= ' '.$catatanTransisi;
        }

        return redirect()
            ->route('sidang.penilaian', $sidangId)
            ->with('success', $pesan);
    }

    protected function setelahRekapMajuTahap(AktivitasSidang $sidang, string $keputusan, $actor): ?string
    {
        $sidang->loadMissing('pengajuanTesis', 'revisiDokumen');

        RevisiDokumen::updateOrCreate(
            ['sidang_id' => $sidang->id],
            [
                'naskah_revisi_final_url' => $sidang->revisiDokumen->naskah_revisi_final_url ?? '-',
                'status_approval_semua' => $keputusan === 'lulus_tanpa_revisi',
                'pengesahan_kaprodi' => $keputusan !== 'ujian_ulang',
                'disahkan_kaprodi_at' => $keputusan !== 'ujian_ulang' ? now() : null,
            ]
        );

        if ($keputusan === 'ujian_ulang') {
            return 'Keputusan ujian ulang: mahasiswa tetap di tahap ini.';
        }

        $tesis = $sidang->pengajuanTesis;
        if (!$tesis) {
            return null;
        }

        $target = match ($sidang->tahap_sidang) {
            'sempro' => 'tahap_3_semhas',
            'semhas' => 'tahap_4_ujian',
            'ujian' => 'selesai_yudisium',
            default => null,
        };

        if (!$target || $tesis->status_tahap === $target) {
            return null;
        }

        try {
            $tesis->refresh();
            LifecycleStateMachine::transition($tesis, $target, $actor);
            $label = match ($target) {
                'tahap_3_semhas' => 'Tahap 3 Seminar Hasil',
                'tahap_4_ujian' => 'Tahap 4 Ujian Tesis',
                'selesai_yudisium' => 'Yudisium',
                default => $target,
            };
            return "Status mahasiswa dipindah ke {$label}.";
        } catch (\Throwable $e) {
            return 'Rekap tersimpan, tetapi transisi tahap tertahan: '.$e->getMessage();
        }
    }
}
