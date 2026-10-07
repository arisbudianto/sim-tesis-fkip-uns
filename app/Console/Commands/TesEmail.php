<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Alat diagnosa pengiriman email: menampilkan konfigurasi SMTP yang
 * BENAR-BENAR terbaca aplikasi (bukan yang tertulis di .env, karena bisa
 * berbeda akibat cache config, baris ganda, atau karakter khusus di
 * password), lalu mengirim satu email uji dan menampilkan galat aslinya.
 *
 * Pemakaian:  php artisan notifikasi:tes-email alamat@contoh.com
 */
class TesEmail extends Command
{
    protected $signature = 'notifikasi:tes-email {tujuan : Alamat email penerima uji}';
    protected $description = 'Kirim email uji dan tampilkan konfigurasi SMTP yang terbaca aplikasi (password disamarkan)';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $smtp = (array) config('mail.mailers.smtp', []);
        $password = (string) ($smtp['password'] ?? '');

        $this->info('Konfigurasi yang terbaca aplikasi:');
        $this->table(['Item', 'Nilai'], [
            ['MAIL_MAILER', $mailer],
            ['MAIL_HOST', $smtp['host'] ?? '-'],
            ['MAIL_PORT', $smtp['port'] ?? '-'],
            ['MAIL_ENCRYPTION', ($smtp['encryption'] ?? '') !== '' ? $smtp['encryption'] : '(kosong)'],
            ['MAIL_USERNAME', $smtp['username'] ?? '-'],
            ['MAIL_PASSWORD', $password === ''
                ? '(KOSONG)'
                : 'panjang ' . strlen($password) . ' karakter, diawali "' . substr($password, 0, 1) . '", diakhiri "' . substr($password, -1) . '"'],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
        ]);

        if (in_array($mailer, ['log', 'array'], true)) {
            $this->error("MAIL_MAILER masih '{$mailer}' sehingga email tidak benar-benar dikirim. Set MAIL_MAILER=smtp di .env lalu jalankan php artisan config:clear.");
            return self::FAILURE;
        }

        $tujuan = (string) $this->argument('tujuan');

        try {
            Mail::raw(
                'Email uji dari SIM-TESIS, dikirim pada ' . now()->format('d/m/Y H:i:s') . '.',
                fn ($message) => $message->to($tujuan)->subject('[SIM-TESIS] Email uji')
            );
        } catch (\Throwable $e) {
            $this->error('GAGAL: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Berhasil dikirim ke {$tujuan}. Cek kotak masuk (dan folder spam).");
        return self::SUCCESS;
    }
}
