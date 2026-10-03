<?php

// Konfigurasi terpusat penilaian rubrik sidang (FR-09).
//
// Prinsip "Integritas Nilai" dari proposal SIM-TESIS: bobot penilaian
// dikonfigurasi terpusat di SATU tempat ini, bukan hardcode di controller,
// dan hanya boleh diubah lewat deployment terkendali (bukan lewat form UI
// biasa) — inilah bentuk "dikunci resmi oleh Komisi Tesis" pada level kode.

return [

    /*
    |--------------------------------------------------------------------------
    | Bobot per Dimensi Rubrik
    |--------------------------------------------------------------------------
    | Total bobot WAJIB berjumlah 1.0 (100%). Empat dimensi ini sesuai FR-09:
    | Kualitas Naskah, Luaran Karya Publikasi, Kualitas Presentasi, Tanya Jawab.
    */
    /*
    |--------------------------------------------------------------------------
    | Rubrik 10 Indikator Penilaian (FPT-TI-03)
    |--------------------------------------------------------------------------
    | Total bobot WAJIB berjumlah 1.0 (100%). Dipakai untuk Sempro, Semhas,
    | maupun Ujian Tesis — templatenya generik, bobot sama rata 10% per
    | indikator supaya sederhana dan mudah diaudit Komisi Tesis.
    */
    // Label & pengelompokan SAMA PERSIS dengan form resmi FPT-TI-03
    // ("PENILAIAN UJIAN TESIS 1 — SEMINAR DAN UJIAN PROPOSAL"): 10 indikator
    // terbagi 2 aspek — I. Kualitas Rencana Penelitian (indikator 1-7) dan
    // II. Kualitas Presentasi (indikator 8-10). Dipakai juga untuk Semhas &
    // Ujian Tesis supaya satu rubrik generik, tapi label & pengelompokan
    // aspeknya mengikuti dokumen resmi Sempro.
    'aspek_indikator' => [
        'I. Kualitas Rencana Penelitian' => [1, 2, 3, 4, 5, 6, 7],
        'II. Kualitas Presentasi' => [8, 9, 10],
    ],

    'label_indikator' => [
        1  => 'Bahasa, ketepatan, dan kejelasan redaksi',
        2  => 'Sistematika dan format penulisan',
        3  => 'Perumusan masalah dan tujuan penelitian',
        4  => 'Kedalaman kajian teori',
        5  => 'Metodologi penelitian',
        6  => 'Keaslian dan kebaruan penelitian',
        7  => 'Kemanfaatan penelitian',
        8  => 'Efektivitas presentasi proposal',
        9  => 'Kemampuan menangkap dan menjawab pertanyaan',
        10 => 'Kedalaman dan keluasan wawasan keilmuan',
    ],

    'bobot_indikator' => [
        1 => 0.10, 2 => 0.10, 3 => 0.10, 4 => 0.10, 5 => 0.10,
        6 => 0.10, 7 => 0.10, 8 => 0.10, 9 => 0.10, 10 => 0.10,
    ],

    /*
    |--------------------------------------------------------------------------
    | (Deprecated) Bobot 4 Dimensi — dipertahankan untuk kompatibilitas
    | mundur, TIDAK dipakai lagi oleh PenilaianSidangController.
    |--------------------------------------------------------------------------
    */
    'bobot_dimensi' => [
        'nilai_dimensi_1_naskah'     => 0.25,
        'nilai_dimensi_2_publikasi'  => 0.25,
        'nilai_dimensi_3_presentasi' => 0.25,
        'nilai_dimensi_4_tanyajawab' => 0.25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ambang Batas Konversi Grade
    |--------------------------------------------------------------------------
    | Diurutkan dari nilai minimum tertinggi ke terendah. Nilai rata-rata
    | dicocokkan ke ambang batas pertama yang terpenuhi.
    */
    'ambang_grade' => [
        ['min' => 85, 'grade' => 'A'],
        ['min' => 80, 'grade' => 'A-'],
        ['min' => 75, 'grade' => 'B+'],
        ['min' => 70, 'grade' => 'B'],
        ['min' => 65, 'grade' => 'C+'],
        ['min' => 0,  'grade' => 'TIDAK_LULUS'],
    ],

];
