<?php

namespace App\Domain\Notifikasi\Services;

use App\Domain\Notifikasi\Models\NotifikasiLog;
use App\Domain\Notifikasi\Models\NotifikasiTemplate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Pengiriman notifikasi lewat email, pasangan WhatsAppNotifierService.
 *
 * Isi pesan memakai template DB yang SAMA dengan WA (sudah dirender dan
 * disimpan di notifikasi_log.pesan_terkirim), jadi satu template cukup
 * untuk kedua channel. Subjek email diambil dari kolom nama_template.
 *
 * Alamat email tujuan disimpan di kolom notifikasi_log.nomor_tujuan (kolom
 * itu bertipe string biasa) supaya tidak perlu migrasi tabel baru; channel
 * pada baris log menentukan apakah isinya nomor WA atau alamat email.
 *
 * Seperti WA: tidak pernah melempar exception, alur akademik utama tidak
 * boleh gagal gara-gara email.
 */
class EmailNotifierService
{
    public static function kirimKeEmail(NotifikasiLog $log): NotifikasiLog
    {
        $mailer = config('mail.default');

        // MAIL_MAILER=log / array hanya menulis ke file log, tidak mengirim
        // apa pun. Jangan dicatat "terkirim" supaya tidak menipu.
        if (in_array($mailer, ['log', 'array'], true)) {
            $log->update([
                'status' => 'gagal',
                'error_message' => "MAIL_MAILER masih '{$mailer}' di .env — email belum benar-benar dikirim. Set MAIL_MAILER=smtp beserta MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS.",
            ]);
            Log::info('[EmailNotifier] Dilewati: MAIL_MAILER belum dikonfigurasi.', ['log_id' => $log->id]);
            return $log;
        }

        $tujuan = trim((string) $log->nomor_tujuan);
        if (!filter_var($tujuan, FILTER_VALIDATE_EMAIL)) {
            $log->update([
                'status' => 'gagal',
                'error_message' => 'Alamat email tujuan kosong atau tidak valid.',
            ]);
            return $log;
        }

        try {
            $subjek = '[SIM-TESIS] ' . (
                NotifikasiTemplate::where('key', $log->template_key)->value('nama_template') ?: 'Notifikasi'
            );

            Mail::raw($log->pesan_terkirim, function ($message) use ($tujuan, $log, $subjek) {
                $message->to($tujuan, $log->penerima_nama)->subject($subjek);
            });

            $log->update(['status' => 'terkirim', 'terkirim_at' => now(), 'error_message' => null]);
            return $log;
        } catch (\Throwable $e) {
            $log->update(['status' => 'gagal', 'error_message' => substr($e->getMessage(), 0, 500)]);
            Log::error('[EmailNotifier] Exception saat mengirim.', ['log_id' => $log->id, 'error' => $e->getMessage()]);
            return $log;
        }
    }
}
