<?php

// Channel notifikasi yang AKTIF saat ini, diatur lewat .env tanpa ubah kode:
//
//   NOTIFIKASI_CHANNELS=email,whatsapp     -> email dan WhatsApp sekaligus (default)
//   NOTIFIKASI_CHANNELS=email              -> hanya email
//   NOTIFIKASI_CHANNELS=whatsapp           -> hanya WhatsApp
//
// Channel yang tidak tercantum di sini dilewati total (tidak mencatat log
// "gagal" yang menyesatkan). Nilai yang tidak dikenal diabaikan; kalau
// hasilnya kosong, sistem jatuh ke 'email'. Kredensial WhatsApp ada di
// config/whatsapp.php; kalau belum diisi, pengiriman WA dicatat 'gagal'
// di log tanpa mengganggu pengiriman email.

return [
    'channels' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('NOTIFIKASI_CHANNELS', 'email,whatsapp'))
    ))),
];
