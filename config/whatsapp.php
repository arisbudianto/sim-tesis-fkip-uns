<?php

// Kredensial WhatsApp Gateway (Wablas) untuk blast notifikasi (undangan
// menguji, dsb). Isi WHATSAPP_API_URL, WHATSAPP_API_TOKEN (Device Token,
// dari menu Device → Settings di dashboard Wablas), dan WHATSAPP_API_SECRET
// (Secret Key device, SELALU dibutuhkan Wablas — tanpa ini request akan
// ditolak dengan error otentikasi walau token-nya benar) di .env. Kalau
// url/token kosong, WhatsAppNotifierService otomatis melewati pengiriman
// tanpa error (fitur WA Blast dianggap belum aktif).
//
// Format URL Wablas: https://<domain-server-anda>.wablas.com/api/send-message
// (domain server beda-beda per akun, lihat dashboard Wablas Bapak — BUKAN
// selalu "deu.wablas.com", itu cuma salah satu contoh server).

return [
    'url' => env('WHATSAPP_API_URL'),
    'token' => env('WHATSAPP_API_TOKEN'),
    'secret' => env('WHATSAPP_API_SECRET'),
];
