<?php

use App\Models\AktivitasSidang;
use App\Models\RevisiDokumen;
use App\Models\ManajemenNilaiSidang;
use App\Models\PendaftaranSempro;

/**
 * Registry seluruh dokumen resmi yang bisa digenerate sistem sebagai PDF
 * dengan stempel QR TTE. Satu template 'view' bisa dipakai ulang untuk
 * tahap sempro/semhas/ujian sekaligus — templatenya generik, isi & label
 * per tahap dibedakan lewat data $sidang->tahap_sidang di dalam view.
 *
 * 'model'  => kelas Eloquent yang jadi sumber data utama (dokumentable)
 * 'view'   => nama view blade (tanpa prefix "pdf.")
 * 'judul'  => judul resmi dokumen
 * 'nomor'  => cara mengambil nomor surat dari record (nullable)
 */
return [
    'FPT-TI-01' => [
        'model' => PendaftaranSempro::class,
        'view' => 'fpt-ti-01-permohonan-ujian',
        'judul' => 'Permohonan Ujian Tesis 1 (Seminar dan Ujian Proposal)',
        'nomor' => fn ($sempro) => null,
    ],
    'SURAT-TUGAS-SEMPRO' => [
        'model' => AktivitasSidang::class,
        'view' => 'surat-tugas-sempro',
        'judul' => 'Surat Tugas Ujian Tesis 1 (Seminar dan Ujian Proposal)',
        'nomor' => fn ($sidang) => $sidang->nomor_surat_tugas,
    ],
    'UNDANGAN-SEMPRO' => [
        'model' => AktivitasSidang::class,
        'view' => 'undangan-sempro',
        'judul' => 'Undangan Menguji Tesis 1 (Seminar dan Ujian Proposal)',
        'nomor' => fn ($sidang) => $sidang->nomor_undangan,
    ],
    'FPT-TI-02' => [
        'model' => AktivitasSidang::class,
        'view' => 'fpt-ti-02-syarat-administrasi',
        'judul' => 'Persyaratan Administrasi Ujian',
        'nomor' => fn ($sidang) => $sidang->nomor_surat_tugas,
    ],
    'FPT-TI-03' => [
        'model' => AktivitasSidang::class,
        'view' => 'fpt-ti-03-penilaian-individu',
        'judul' => 'Lembar Penilaian Individu',
        'nomor' => fn ($sidang) => $sidang->nomor_surat_tugas,
    ],
    'FPT-TI-04' => [
        'model' => RevisiDokumen::class,
        'view' => 'fpt-ti-04-catatan-penilaian-revisi',
        'judul' => 'Catatan Penilaian & Revisi',
        'nomor' => fn ($revisi) => $revisi->sidang->nomor_surat_tugas ?? null,
    ],
    'FPT-TI-05' => [
        'model' => ManajemenNilaiSidang::class,
        'view' => 'fpt-ti-05-rekapitulasi-nilai',
        'judul' => 'Rekapitulasi Nilai Sidang',
        'nomor' => fn ($nilai) => $nilai->sidang->nomor_surat_tugas ?? null,
    ],
    'FPT-TI-06' => [
        'model' => ManajemenNilaiSidang::class,
        'view' => 'fpt-ti-06-berita-acara-sidang',
        'judul' => 'Berita Acara Sidang',
        'nomor' => fn ($nilai) => $nilai->sidang->nomor_surat_tugas ?? null,
    ],
    'FPT-TI-07' => [
        'model' => AktivitasSidang::class,
        'view' => 'fpt-ti-07-daftar-hadir-penguji',
        'judul' => 'Daftar Hadir Dewan Penguji',
        'nomor' => fn ($sidang) => $sidang->nomor_undangan,
    ],
    'FPT-TI-08' => [
        'model' => AktivitasSidang::class,
        'view' => 'fpt-ti-08-daftar-hadir-audiens',
        'judul' => 'Daftar Hadir Audiens',
        'nomor' => fn ($sidang) => $sidang->nomor_undangan,
    ],
    'FPT-TI-09' => [
        'model' => RevisiDokumen::class,
        'view' => 'fpt-ti-09-persetujuan-revisi',
        'judul' => 'Persetujuan Revisi Tesis',
        'nomor' => fn ($revisi) => $revisi->sidang->nomor_surat_tugas ?? null,
    ],
    'SURAT-TUGAS-WADEK1' => [
        'model' => AktivitasSidang::class,
        'view' => 'surat-tugas-wadek1',
        'judul' => 'Surat Tugas Wakil Dekan I',
        'nomor' => fn ($sidang) => $sidang->nomor_surat_tugas,
    ],
    'BAP' => [
        'model' => ManajemenNilaiSidang::class,
        'view' => 'berita-acara-pelaksanaan',
        'judul' => 'Berita Acara Pelaksanaan (BAP)',
        'nomor' => fn ($nilai) => $nilai->bap_pdf_url ? null : null,
    ],
];
