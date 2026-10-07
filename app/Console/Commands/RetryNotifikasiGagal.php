<?php

namespace App\Console\Commands;

use App\Domain\Notifikasi\Models\NotifikasiLog;
use App\Domain\Notifikasi\Services\EmailNotifierService;
use App\Domain\Notifikasi\Services\WhatsAppNotifierService;
use Illuminate\Console\Command;

/**
 * Mekanisme retry otomatis (Modul 9, Tugas 4) untuk notifikasi WA yang
 * gagal terkirim. Batas maksimal 5 percobaan supaya tidak retry selamanya
 * untuk nomor yang memang invalid/gateway down permanen.
 *
 * Dijadwalkan otomatis tiap 15 menit lewat routes/console.php.
 */
class RetryNotifikasiGagal extends Command
{
    protected $signature = 'notifikasi:retry {--max=5 : Batas maksimal percobaan per notifikasi}';
    protected $description = 'Retry notifikasi (email/WhatsApp) yang berstatus gagal (Modul 9)';

    public function handle(): int
    {
        $maxPercobaan = (int) $this->option('max');

        // Hanya channel yang sedang aktif yang di-retry, supaya log WA lama
        // tidak terus diulang saat WA dimatikan (NOTIFIKASI_CHANNELS=email).
        $gagal = NotifikasiLog::where('status', 'gagal')
            ->where('percobaan_ke', '<', $maxPercobaan)
            ->whereIn('channel', WhatsAppNotifierService::channelAktif())
            ->get();

        if ($gagal->isEmpty()) {
            $this->info('Tidak ada notifikasi gagal yang perlu di-retry.');
            return self::SUCCESS;
        }

        $this->info("Mencoba retry {$gagal->count()} notifikasi gagal...");

        $berhasil = 0;
        foreach ($gagal as $log) {
            $log->increment('percobaan_ke');
            $segar = $log->fresh();
            $hasil = $segar->channel === 'email'
                ? EmailNotifierService::kirimKeEmail($segar)
                : WhatsAppNotifierService::kirimKeGateway($segar);
            if ($hasil->status === 'terkirim') {
                $berhasil++;
            }
        }

        $this->info("Selesai: {$berhasil} dari {$gagal->count()} berhasil terkirim ulang.");
        return self::SUCCESS;
    }
}
