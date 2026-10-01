<?php

namespace App\Domain\Notifikasi\Services;

use App\Domain\Sidang\Models\AktivitasSidang;
use Carbon\Carbon;

/**
 * Generator berkas iCalendar (.ics) untuk satu jadwal sidang, supaya dosen
 * bisa impor langsung ke Google Calendar/Outlook/Apple Calendar tanpa perlu
 * integrasi OAuth penuh ke Google Calendar API.
 */
class CalendarIcsService
{
    public static function generate(AktivitasSidang $sidang): string
    {
        $sidang->loadMissing('pengajuanTesis.mahasiswa');

        $uid = $sidang->id . '@' . parse_url(config('app.url'), PHP_URL_HOST);
        $dtstamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $dtstart = Carbon::parse($sidang->waktu_mulai)->timezone('UTC')->format('Ymd\THis\Z');
        $dtend = Carbon::parse($sidang->waktu_selesai)->timezone('UTC')->format('Ymd\THis\Z');

        $mahasiswa = $sidang->pengajuanTesis->mahasiswa->name ?? '-';
        $summary = 'Sidang ' . strtoupper($sidang->tahap_sidang) . ' — ' . $mahasiswa;
        $location = $sidang->ruangan ?: ($sidang->link_zoom ?: 'Daring');
        $description = $sidang->link_zoom
            ? 'Link Zoom: ' . $sidang->link_zoom
            : 'Sidang tesis SIM-TESIS FKIP UNS.';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//SIM-TESIS FKIP UNS//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $dtstamp,
            'DTSTART:' . $dtstart,
            'DTEND:' . $dtend,
            'SUMMARY:' . self::escape($summary),
            'DESCRIPTION:' . self::escape($description),
            'LOCATION:' . self::escape($location),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        // iCal wajib line-ending CRLF.
        return implode("\r\n", $lines) . "\r\n";
    }

    protected static function escape(string $text): string
    {
        return str_replace(["\\", "\n", ",", ";"], ["\\\\", "\\n", "\\,", "\\;"], $text);
    }
}
