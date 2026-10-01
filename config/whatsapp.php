<?php

// Kredensial WhatsApp Gateway untuk blast notifikasi (undangan menguji, dsb).
// Isi WHATSAPP_API_URL & WHATSAPP_API_TOKEN di .env sesuai provider yang
// dipakai tim (mis. Fonnte, WhatsApp Business API resmi, dsb). Kalau kosong,
// WhatsAppNotifierService otomatis melewati pengiriman tanpa error.

return [
    'url' => env('WHATSAPP_API_URL'),
    'token' => env('WHATSAPP_API_TOKEN'),
];
