# SIM-TESIS FKIP UNIVERSITAS SEBELAS MARET
## Sistem Informasi Manajemen Siklus Lengkap 4 Tahapan Tesis Magister

Aplikasi ini mengimplementasikan arsitektur berorientasi alur kerja (*Workflow & Lifecycle State Engine*) dengan Komisi Tesis sebagai pengendali operasional utama yang memenuhi seluruh **10 Kebutuhan Fungsional (FR-01 s.d. FR-10)** sesuai Dokumen Proposal No. `PROP-SIMTESIS-4STAGE-2026`.

### Matriks Pemenuhan 10 Functional Requirements (FR)

| Kode FR | Kebutuhan Proposal | Implementasi Relasi Database | Service / Engine Terkait |
| :--- | :--- | :--- | :--- |
| **FR-01** | Pengajuan Judul & Alokasi Pembimbing 1 & 2 | `users` -> `pengajuan_tesis` | `AdvisorQuotaEngine` |
| **FR-02** | Logbook Bimbingan Digital & Approval | `pengajuan_tesis` -> `logbook_bimbingans` | `LogbookBimbinganController` |
| **FR-03** | Pendaftaran Sempro H-14 & Berkas FPT-TI-01 & 02 | `pengajuan_tesis` -> `pendaftaran_sempros` | `PendaftaranSemproController` |
| **FR-04** | Plotting Tim Penguji Sempro & Jadwal | `aktivitas_sidangs` -> `penguji_sidangs` | `AntiConflictScheduler` |
| **FR-05** | Pendaftaran Semhas H-14 (2 Draf + 1 Under Review) | `pengajuan_tesis` -> `pendaftaran_semhas` | `LifecycleStateMachine` |
| **FR-06** | Plotting Jadwal & Rekap Hasil Riset Semhas | `aktivitas_sidangs` -> `penguji_sidangs` | `KomisiTesisController` |
| **FR-07** | Pendaftaran Ujian Tesis (9 Dokumen & H-14) | `pengajuan_tesis` -> `pendaftaran_ujians` | `PendaftaranUjianController` |
| **FR-08** | Plotting 4 Dewan Penguji Ujian (2 Pemb, 1 Studi, 1 Pend) | `aktivitas_sidangs` -> `penguji_sidangs` | `AntiConflictScheduler` |
| **FR-09** | Rubrik 4 Dimensi Nilai, Konversi Grade & BAP | `penguji_sidangs` -> `manajemen_nilai_sidangs` | `PenilaianSidangController` |
| **FR-10** | Matriks Revisi 4 Penguji, Pengesahan & Yudisium | `revisi_dokumens` -> `revisi_pengujis` | `LifecycleStateMachine` |

### Panduan Instalasi & Menjalankan Proyek

1. Ekstrak file zip proyek ke direktori server web / workspace Anda.
2. Salin `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
3. Pasang dependensi vendor via Composer:
   ```bash
   composer install
   ```
4. Jalankan migrasi dan seeder data awal:
   ```bash
   php artisan migrate:fresh --seed
   ```
5. Jalankan server lokal aplikasi:
   ```bash
   php artisan serve
   ```
