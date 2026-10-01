<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Modul 9, Tugas 4: retry otomatis notifikasi WA yang gagal terkirim,
// tiap 15 menit. Server produksi wajib punya cron entry standar Laravel:
// * * * * * php /path/artisan schedule:run >> /dev/null 2>&1
Schedule::command('notifikasi:retry')->everyFifteenMinutes();
