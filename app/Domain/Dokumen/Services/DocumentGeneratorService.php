<?php

namespace App\Domain\Dokumen\Services;

use App\Domain\Dokumen\Models\DokumenCetak;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\Response;

class DocumentGeneratorService
{
    /**
     * Generate/ambil dokumen resmi (PDF) untuk kode dokumen & record sumber
     * tertentu.
     *
     * IDEMPOTEN (acceptance criteria): kalau dokumen versi aktif untuk
     * kombinasi kode+record ini SUDAH PERNAH dibuat dan file-nya masih ada
     * di storage, method ini mengembalikan FILE YANG SAMA — tidak membuat
     * hash/baris log baru, tidak render ulang. Regenerasi (versi baru)
     * HANYA terjadi kalau $forceRegenerate = true secara eksplisit.
     */
    public function generate(string $kodeDokumen, string $recordId, ?User $dicetakOleh = null, bool $forceRegenerate = false): Response
    {
        $config = config("dokumen.{$kodeDokumen}");

        if (! $config) {
            throw new InvalidArgumentException("Kode dokumen '{$kodeDokumen}' tidak dikenali di registry config/dokumen.php");
        }

        /** @var \Illuminate\Database\Eloquent\Model $record */
        $record = $config['model']::findOrFail($recordId);

        $existing = DokumenCetak::where('kode_dokumen', $kodeDokumen)
            ->where('dokumentable_type', $config['model'])
            ->where('dokumentable_id', $record->id)
            ->orderByDesc('versi')
            ->first();

        $draftTanpaCache = str_starts_with($kodeDokumen, 'SURAT-TUGAS') || $kodeDokumen === 'SK-PEMBIMBING';
        if ($existing && !$forceRegenerate && !$draftTanpaCache && $existing->file_path && Storage::disk('public')->exists($existing->file_path)) {
            return $this->responFileTersimpan($existing);
        }

        return $this->generateBaru($kodeDokumen, $config, $record, $dicetakOleh, $existing?->versi ?? 0);
    }

    /**
     * Paksa buat VERSI BARU (dipakai kalau data sumber berubah setelah
     * dokumen pertama kali dicetak). Versi lama TETAP tersimpan di storage
     * & tabel dokumen_cetaks — tidak ditimpa — demi jejak audit historis.
     */
    public function regenerate(string $kodeDokumen, string $recordId, ?User $dicetakOleh = null): Response
    {
        return $this->generate($kodeDokumen, $recordId, $dicetakOleh, forceRegenerate: true);
    }

    protected function generateBaru(string $kodeDokumen, array $config, $record, ?User $dicetakOleh, int $versiSebelumnya): Response
    {
        // Semua dokumen resmi (Undangan, Surat Tugas, Berita Acara, Daftar
        // Hadir, dst.) memakai ->translatedFormat() di template Blade-nya
        // untuk menampilkan nama hari & bulan (mis. "Rabu, 7 Oktober 2026").
        // Tanpa baris ini, locale default Laravel/Carbon di server adalah
        // 'en', jadi translatedFormat() tetap keluar bahasa Inggris
        // ("Wednesday, 07 October 2026") walau formatnya sudah benar.
        // Diset di satu titik pusat ini (bukan per-view) supaya berlaku
        // otomatis ke SEMUA dokumen, termasuk yang dibuat belakangan.
        Carbon::setLocale('id');

        $versiBaru = $versiSebelumnya + 1;
        $hash = hash('sha256', $kodeDokumen . '|' . $record->id . '|v' . $versiBaru . '|' . Str::uuid());
        $nomorDokumen = $config['nomor'] ? ($config['nomor'])($record) : null;

        $urlVerifikasi = URL::to("/verifikasi/{$hash}");

        $qrBase64 = base64_encode(
            QrCode::format('png')->size(140)->margin(1)->generate($urlVerifikasi)
        );

        $dicetakAt = now();

        $tampilkanQr = !str_starts_with($kodeDokumen, 'SURAT-TUGAS') && $kodeDokumen !== 'SK-PEMBIMBING';

        $adalahUndangan = str_starts_with($kodeDokumen, 'UNDANGAN');
        $adalahSuratTugas = str_starts_with($kodeDokumen, 'SURAT-TUGAS');

        // Baris "Kode Dokumen / Nomor / Dicetak / Sistem" di bawah kop surat
        // sengaja disembunyikan khusus untuk dokumen Undangan (UNDANGAN-SEMPRO,
        // UNDANGAN-SEMHAS, dst) supaya tampilannya rapi seperti surat resmi
        // FKIP biasa — cukup kop + badan surat, tanpa baris metadata sistem.
        // QR verifikasi (tampilkanQr) TIDAK ikut dimatikan, tetap tampil.
        $tampilkanMeta = $tampilkanQr && !$adalahUndangan;

        // Undangan memakai kop resmi UNS (kuning-biru, lengkap alamat &
        // kontak) sesuai contoh surat undangan resmi FKIP yang diberikan —
        // berbeda dari kop generik dokumen lain. Judul form besar di tengah
        // juga disembunyikan karena surat resmi langsung ke Nomor/Lampiran/Hal
        // tanpa judul terpisah. QR verifikasi untuk Undangan ditaruh sendiri
        // oleh view di bawah label "Ketua Program Studi" (menyatu dengan
        // blok tanda tangan), bukan di blok QR generik pojok kanan bawah.
        $footerLegalText = $adalahUndangan ? [
            'Dokumen ini dicetak melalui Sistem Informasi Manajemen Tesis (SIM-TESIS) FKIP UNS dan dilengkapi kode QR untuk verifikasi keaslian.',
            'Pindai (scan) kode QR di atas untuk memeriksa keabsahan nomor dan data dokumen ini secara daring.',
        ] : null;

        // Surat Tugas diterbitkan atas nama Dekan (ditandatangani Wakil
        // Dekan I), bukan Ketua Program Studi — jadi pakai kop level
        // FAKULTAS (tanpa baris "Program Studi Magister Pendidikan Guru
        // Vokasi" & alamat kampus V Pabelan di kop Undangan), sesuai contoh
        // kop resmi yang diberikan: hanya "Fakultas Keguruan dan Ilmu
        // Pendidikan" + alamat Kentingan + fkip@mail.uns.ac.id.

        $pdf = Pdf::loadView("pdf.{$config['view']}", [
            'record' => $record,
            'judul' => $config['judul'],
            'kodeDokumen' => $kodeDokumen,
            'nomorDokumen' => $nomorDokumen,
            'qrBase64' => $tampilkanQr ? $qrBase64 : null,
            'hashVerifikasi' => $hash,
            'dicetakAt' => $dicetakAt,
            'tampilkanQr' => $tampilkanQr,
            'tampilkanMeta' => $tampilkanMeta,
            'kopAsset' => $adalahUndangan ? 'kop-undangan' : ($adalahSuratTugas ? 'kop-fakultas' : 'kop-fkip-pgv'),
            'tampilkanJudul' => !$adalahUndangan,
            'qrDitaruhDiKonten' => $adalahUndangan,
            'footerLegalText' => $footerLegalText,
        ])->setPaper('a4');

        $binary = $pdf->output();

        // SIMPAN ke storage — inilah inti perbaikan idempotent. Path
        // mengandung versi supaya versi lama tidak pernah tertimpa.
        $filePath = "dokumen/{$kodeDokumen}/{$record->id}-v{$versiBaru}.pdf";
        Storage::disk('public')->put($filePath, $binary);

        $dokumenCetak = DokumenCetak::create([
            'id' => (string) Str::uuid(),
            'kode_dokumen' => $kodeDokumen,
            'dokumentable_type' => $config['model'],
            'dokumentable_id' => $record->id,
            'nomor_dokumen' => $nomorDokumen,
            'dicetak_oleh_id' => $dicetakOleh?->id,
            'hash_verifikasi' => $hash,
            'file_path' => $filePath,
            'versi' => $versiBaru,
            'payload_snapshot' => $record->toArray(),
            'dicetak_at' => $dicetakAt,
        ]);

        return $this->responFileTersimpan($dokumenCetak, $binary);
    }

    /**
     * "1-Click Print Bundle": gabungkan seluruh dokumen resmi terkait SATU
     * sidang jadi satu file PDF siap cetak untuk arsip manual program studi.
     *
     * Memakai setasign/fpdi untuk MENGGABUNGKAN halaman PDF asli (bukan
     * menempel HTML mentah-mentah) — supaya format tiap dokumen (kop resmi,
     * QR TTE, dsb.) tetap presisi sama seperti versi cetak individualnya.
     *
     * Setiap dokumen komponen tetap melalui alur idempoten generate() yang
     * sama (tersimpan & bisa diverifikasi sendiri-sendiri) — bundle ini
     * murni gabungan hasil akhirnya, TIDAK disimpan/di-versioning sebagai
     * entitas dokumen tersendiri (selalu digabung ulang dari komponen yang
     * sudah tersimpan, murah secara komputasi karena komponen individualnya
     * sudah idempoten).
     */
    public function generateBundle(string $sidangId, ?User $dicetakOleh = null): Response
    {
        $sidang = \App\Domain\Sidang\Models\AktivitasSidang::with('revisiDokumen')->findOrFail($sidangId);
        $kodeList = $this->daftarDokumenBundle($sidang);

        if (empty($kodeList)) {
            throw new InvalidArgumentException("Belum ada dokumen yang bisa digabung untuk sidang tahap '{$sidang->tahap_sidang}' ini.");
        }

        $pdfMerger = new \setasign\Fpdi\Fpdi();

        foreach ($kodeList as [$kode, $recordId]) {
            if (!$recordId) continue; // record sumber belum ada (mis. revisi belum dibuat) — lewati diam-diam

            try {
                $this->generate($kode, $recordId, $dicetakOleh); // pastikan tersimpan (idempoten)
                $dokumen = DokumenCetak::where('kode_dokumen', $kode)
                    ->where('dokumentable_id', $recordId)
                    ->orderByDesc('versi')
                    ->first();

                if (!$dokumen || !Storage::disk('public')->exists($dokumen->file_path)) continue;

                $tempPath = Storage::disk('public')->path($dokumen->file_path);
                $jumlahHalaman = $pdfMerger->setSourceFile($tempPath);
                for ($p = 1; $p <= $jumlahHalaman; $p++) {
                    $tplId = $pdfMerger->importPage($p);
                    $ukuran = $pdfMerger->getTemplateSize($tplId);
                    $pdfMerger->AddPage($ukuran['orientation'], [$ukuran['width'], $ukuran['height']]);
                    $pdfMerger->useTemplate($tplId);
                }
            } catch (\Throwable $e) {
                // Satu dokumen komponen gagal digabung TIDAK BOLEH
                // menggagalkan seluruh bundle — lewati, catat ke log.
                \Illuminate\Support\Facades\Log::warning('[DocumentGeneratorService] Gagal menggabungkan dokumen ke bundle.', [
                    'kode' => $kode, 'record_id' => $recordId, 'error' => $e->getMessage(),
                ]);
            }
        }

        $binary = $pdfMerger->Output('S'); // 'S' = return sebagai string, bukan langsung output

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"bundle-{$sidang->tahap_sidang}-{$sidang->id}.pdf\"",
        ]);
    }

    /**
     * Daftar [kode_dokumen, record_id] yang relevan untuk satu sidang,
     * tergantung tahapnya. Dokumen yang belum tersedia templatenya untuk
     * tahap tertentu (lihat catatan Modul 9) otomatis dilewati.
     */
    protected function daftarDokumenBundle($sidang): array
    {
        $revisiId = $sidang->revisiDokumen?->id;

        return match ($sidang->tahap_sidang) {
            'sempro' => [
                ['SURAT-TUGAS-SEMPRO', $sidang->id],
                ['UNDANGAN-SEMPRO', $sidang->id],
                ['FPT-TI-02', $sidang->id],
                ['FPT-TI-03', $sidang->id],
                ['FPT-TI-07', $sidang->id],
                ['FPT-TI-08', $sidang->id],
                ['FPT-TI-09', $revisiId],
            ],
            'ujian' => [
                ['SURAT-TUGAS-WADEK1', $sidang->id],
                ['FPT-TI-02', $sidang->id],
                ['FPT-TI-03', $sidang->id],
                ['FPT-TI-07', $sidang->id],
                ['FPT-TI-08', $sidang->id],
                ['FPT-TI-09', $revisiId],
            ],
            // Semhas: sekarang pakai template generik yang sama dengan Sempro.
            default => [
                ['SURAT-TUGAS-SEMHAS', $sidang->id],
                ['UNDANGAN-SEMHAS', $sidang->id],
                ['FPT-TI-02', $sidang->id],
                ['FPT-TI-03', $sidang->id],
                ['FPT-TI-07', $sidang->id],
                ['FPT-TI-08', $sidang->id],
                ['FPT-TI-09', $revisiId],
            ],
        };
    }

    /**
     * Kembalikan file yang sudah tersimpan di storage sebagai response
     * tampil-di-browser — TANPA render ulang PDF (idempotent).
     */
    protected function responFileTersimpan(DokumenCetak $dokumen, ?string $binaryFallback = null): Response
    {
        $binary = $binaryFallback ?? Storage::disk('public')->get($dokumen->file_path);
        $namaFile = "{$dokumen->kode_dokumen}-{$dokumen->dokumentable_id}-v{$dokumen->versi}.pdf";

        return response($binary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$namaFile}\"",
        ]);
    }
}
