<?php

namespace App\Domain\Notifikasi\Services;

use App\Domain\Notifikasi\Models\NotifikasiLog;
use App\Domain\Notifikasi\Models\NotifikasiTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi WhatsApp Gateway terpusat (Modul 9) untuk seluruh event
 * notifikasi Modul 5-8: undangan menguji, status approval pembimbing,
 * jadwal terkunci, hasil kelulusan.
 *
 * Prinsip desain:
 * 1. Template pesan disimpan di tabel `notifikasi_templates` (BUKAN
 *    hardcode string PHP) — bisa diubah tanpa redeploy, sesuai
 *    acceptance criteria.
 * 2. SETIAP notifikasi (berhasil MAUPUN gagal) dicatat ke tabel
 *    `notifikasi_log` — untuk audit & basis mekanisme retry.
 * 3. Feature-flagged: kalau WHATSAPP_API_URL/TOKEN belum dikonfigurasi,
 *    tetap dicatat ke log (status 'gagal', error_message jelas) TANPA
 *    melempar exception — alur akademik utama (plotting, verifikasi,
 *    penilaian) tidak boleh pernah gagal gara-gara WA gateway.
 */
class WhatsAppNotifierService
{
    /**
     * Kirim notifikasi berbasis template DB. Ini SATU-SATUNYA method
     * yang boleh dipanggil dari controller — jangan pernah membangun
     * string pesan manual di controller (langgar acceptance criteria
     * "template dapat dikonfigurasi tanpa redeploy").
     */
    public static function kirim(string $templateKey, ?User $penerima, array $data, array $meta = [], ?string $nomorManual = null): NotifikasiLog
    {
        $nomorTujuan = $nomorManual ?? $penerima?->nomor_wa;

        $template = NotifikasiTemplate::where('key', $templateKey)->where('is_active', true)->first();

        if (!$template) {
            return self::catatLog($templateKey, $penerima, $nomorTujuan, "[Template '{$templateKey}' tidak ditemukan/tidak aktif]", 'gagal', $meta, "Template '{$templateKey}' tidak ditemukan di notifikasi_templates atau is_active = false.");
        }

        $pesan = $template->render($data);

        if (!$nomorTujuan) {
            return self::catatLog($templateKey, $penerima, null, $pesan, 'gagal', $meta, 'Nomor WhatsApp tujuan kosong (penerima belum mengisi nomor_wa di profil).');
        }

        $log = self::catatLog($templateKey, $penerima, $nomorTujuan, $pesan, 'pending', $meta, null);

        return self::kirimKeGateway($log);
    }

    /**
     * Eksekusi pengiriman aktual ke gateway WA untuk satu baris log yang
     * sudah ada (dipanggil dari kirim() untuk percobaan pertama, atau
     * dari command retry:notifikasi untuk percobaan ulang).
     */
    public static function kirimKeGateway(NotifikasiLog $log): NotifikasiLog
    {
        $url = config('whatsapp.url');
        $token = config('whatsapp.token');

        if (!$url || !$token) {
            $log->update([
                'status' => 'gagal',
                'error_message' => 'WHATSAPP_API_URL/WHATSAPP_API_TOKEN belum dikonfigurasi di .env.',
            ]);
            Log::info('[WhatsAppNotifier] Dilewati: gateway belum dikonfigurasi.', ['log_id' => $log->id]);
            return $log;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->post($url, [
                    'target' => self::normalisasiNomor($log->nomor_tujuan),
                    'message' => $log->pesan_terkirim,
                ]);

            if (!$response->successful()) {
                $log->update([
                    'status' => 'gagal',
                    'error_message' => "HTTP {$response->status()}: " . substr($response->body(), 0, 500),
                ]);
                return $log;
            }

            $log->update(['status' => 'terkirim', 'terkirim_at' => now(), 'error_message' => null]);
            return $log;
        } catch (\Throwable $e) {
            $log->update(['status' => 'gagal', 'error_message' => substr($e->getMessage(), 0, 500)]);
            Log::error('[WhatsAppNotifier] Exception saat mengirim.', ['log_id' => $log->id, 'error' => $e->getMessage()]);
            return $log;
        }
    }

    protected static function catatLog(string $templateKey, ?User $penerima, ?string $nomorTujuan, string $pesan, string $status, array $meta, ?string $errorMessage): NotifikasiLog
    {
        return NotifikasiLog::create([
            'penerima_id' => $penerima?->id,
            'penerima_nama' => $penerima?->name,
            'nomor_tujuan' => $nomorTujuan,
            'channel' => 'whatsapp',
            'template_key' => $templateKey,
            'pesan_terkirim' => $pesan,
            'status' => $status,
            'percobaan_ke' => 1,
            'error_message' => $errorMessage,
            'meta' => $meta,
        ]);
    }

    /**
     * Normalisasi nomor ke format E.164 tanpa tanda "+" (mis. 62812xxxxxxx).
     */
    protected static function normalisasiNomor(string $nomor): string
    {
        $nomor = preg_replace('/[^0-9]/', '', $nomor);
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        }
        return $nomor;
    }
}
