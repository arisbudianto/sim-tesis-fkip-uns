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
        $logs = self::kirimSemuaChannel($templateKey, $penerima, $data, $meta, $nomorManual);

        // Callers lama hanya butuh satu hasil ringkas: utamakan channel yang
        // berhasil, kalau tidak ada yang berhasil kembalikan yang pertama.
        return collect($logs)->firstWhere('status', 'terkirim') ?? $logs[0];
    }

    /**
     * Kirim ke SEMUA channel aktif (email dan/atau WhatsApp) untuk satu
     * penerima. Tiap channel dicoba sendiri-sendiri dan hasilnya dicatat
     * sendiri-sendiri di notifikasi_log: gagalnya satu channel (mis. WA
     * belum dikonfigurasi) tidak menghalangi channel lain. Pintu tunggal
     * multi-channel; channel diatur config/notifikasi.php (env
     * NOTIFIKASI_CHANNELS). Nama kelas dipertahankan supaya seluruh
     * controller pemanggil tidak perlu diubah.
     *
     * @return array<int, NotifikasiLog> satu log per channel aktif (tidak pernah kosong)
     */
    public static function kirimSemuaChannel(string $templateKey, ?User $penerima, array $data, array $meta = [], ?string $nomorManual = null): array
    {
        $channels = self::channelAktif();

        $template = NotifikasiTemplate::where('key', $templateKey)->where('is_active', true)->first();

        if (!$template) {
            return [self::catatLog($templateKey, $penerima, self::tujuan($channels[0], $penerima, $nomorManual), "[Template '{$templateKey}' tidak ditemukan/tidak aktif]", 'gagal', $meta, "Template '{$templateKey}' tidak ditemukan di notifikasi_templates atau is_active = false.", $channels[0])];
        }

        $pesan = $template->render($data);

        $logs = [];
        foreach ($channels as $channel) {
            $tujuan = self::tujuan($channel, $penerima, $nomorManual);

            if (!$tujuan) {
                $logs[] = self::catatLog($templateKey, $penerima, null, $pesan, 'gagal', $meta, $channel === 'email'
                    ? 'Email tujuan kosong (penerima belum memiliki email di profil).'
                    : 'Nomor WhatsApp tujuan kosong (penerima belum mengisi nomor_wa di profil).', $channel);
                continue;
            }

            $log = self::catatLog($templateKey, $penerima, $tujuan, $pesan, 'pending', $meta, null, $channel);

            $logs[] = $channel === 'email'
                ? EmailNotifierService::kirimKeEmail($log)
                : self::kirimKeGateway($log);
        }

        return $logs;
    }

    /**
     * Channel yang aktif menurut config/notifikasi.php, sudah divalidasi.
     * Tidak pernah kosong (fallback ke 'email').
     *
     * @return array<int, string>
     */
    public static function channelAktif(): array
    {
        $channels = array_values(array_unique(array_intersect(
            (array) config('notifikasi.channels', ['email']),
            ['email', 'whatsapp']
        )));

        return $channels ?: ['email'];
    }

    protected static function tujuan(string $channel, ?User $penerima, ?string $nomorManual): ?string
    {
        if ($channel === 'email') {
            return $penerima?->email ?: null;
        }

        return $nomorManual ?? $penerima?->nomor_wa;
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
        $secret = config('whatsapp.secret');

        if (!$url || !$token) {
            $log->update([
                'status' => 'gagal',
                'error_message' => 'WHATSAPP_API_URL/WHATSAPP_API_TOKEN belum dikonfigurasi di .env.',
            ]);
            Log::info('[WhatsAppNotifier] Dilewati: gateway belum dikonfigurasi.', ['log_id' => $log->id]);
            return $log;
        }

        if (!$secret) {
            $log->update([
                'status' => 'gagal',
                'error_message' => 'WHATSAPP_API_SECRET belum dikonfigurasi di .env — Wablas WAJIB mengirim Token + Secret Key sekaligus, request akan ditolak Wablas kalau cuma token.',
            ]);
            Log::info('[WhatsAppNotifier] Dilewati: WHATSAPP_API_SECRET belum diisi.', ['log_id' => $log->id]);
            return $log;
        }

        try {
            // Otentikasi Wablas BUKAN Bearer token biasa — formatnya
            // "{token}.{secret_key}" sebagai nilai mentah header
            // Authorization (tanpa prefiks "Bearer "), dan body-nya
            // form-encoded dengan field "phone" (bukan "target"/JSON),
            // sesuai dokumentasi resmi Wablas.
            $response = Http::withHeaders([
                    'Authorization' => "{$token}.{$secret}",
                ])
                ->asForm()
                ->timeout(10)
                ->post($url, [
                    'phone' => self::normalisasiNomor($log->nomor_tujuan),
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

    protected static function catatLog(string $templateKey, ?User $penerima, ?string $nomorTujuan, string $pesan, string $status, array $meta, ?string $errorMessage, string $channel = 'whatsapp'): NotifikasiLog
    {
        return NotifikasiLog::create([
            'penerima_id' => $penerima?->id,
            'penerima_nama' => $penerima?->name,
            'nomor_tujuan' => $nomorTujuan, // nomor WA ATAU alamat email, tergantung channel
            'channel' => $channel,
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
