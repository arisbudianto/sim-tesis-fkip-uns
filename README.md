# SIM-TESIS — FKIP Universitas Sebelas Maret

Repositori ini berisi dua deliverable teknis yang diturunkan dari
**Proposal Pengembangan SIM-TESIS (Kode Dokumen: PROP-SIMTESIS-4STAGE-2026)**:

1. **`database/schema.sql`** — skema basis data PostgreSQL untuk seluruh siklus 4 tahap tesis.
2. **`forms/`** — seluruh formulir (dokumen FPT-TI/FPT-PB/FPT-SH/FPT-UT) dalam bentuk HTML siap pakai, bisa dibuka langsung di browser atau di-print ke PDF, dan siap dijadikan basis komponen frontend (React/Vue/Blade, dsb).

## Struktur Folder

```
sim-tesis-fkip-uns/
├── database/
│   └── schema.sql                # DDL PostgreSQL lengkap + seed rubrik penilaian
├── forms/
│   ├── index.html                 # Daftar navigasi seluruh formulir
│   ├── assets/
│   │   └── style.css              # Style bersama (kop surat, tabel rubrik, dst.)
│   ├── tahap1-pembimbing/         # FPT-PB-01, FPT-PB-02
│   ├── tahap2-sempro/             # FPT-TI-01 s.d. FPT-TI-09 + Surat Tugas + Undangan
│   ├── tahap3-semhas/             # FPT-SH-01 s.d. FPT-SH-07
│   └── tahap4-ujian-tesis/        # FPT-UT-01 s.d. FPT-UT-07 + Surat Tugas + Undangan
└── README.md
```

## Basis Data (`database/schema.sql`)

Menggunakan **MySQL 8.0+** (skema ini sudah diuji-jalan penuh di MySQL 8.0.46:
seluruh 23 tabel terbentuk, seed data masuk, `DEFAULT (UUID())` berjalan, dan
`CONSTRAINT CHECK` untuk validasi H-14 terbukti aktif menolak data yang
melanggar). Cakupan tabel:

| Kelompok | Tabel |
|---|---|
| Aktor | `users`, `mahasiswa`, `dosen` |
| Tahap 1 — Pembimbing | `topik_penelitian`, `penetapan_pembimbing`, `logbook_bimbingan` |
| Sidang (generik Sempro/Semhas/Ujian) | `sidang`, `sidang_dokumen`, `sidang_validasi_khusus`, `dewan_penguji`, `jadwal_konflik_log`, `presensi_sidang` |
| Penilaian | `rubrik_penilaian`, `nilai_individu`, `rekap_nilai`, `berita_acara` |
| Revisi | `revisi_matriks`, `persetujuan_revisi` |
| Surat & Notifikasi | `surat_keluar`, `notifikasi_wa_log`, `kalender_sync` |
| Yudisium & State Machine | `yudisium`, `state_transition_log` |

Poin penting desain:

- **Lifecycle State Machine** (mitigasi risiko *State Inconsistency* pada Bab VI proposal) direalisasikan lewat `sidang.sidang_prasyarat_id`: sebelum membuat baris Semhas, aplikasi wajib memvalidasi baris Sempro terkait berstatus `selesai` dengan keputusan ACC.
- **Validasi H-14** dikunci di level database lewat `CONSTRAINT CHECK chk_h14` pada tabel `sidang` (aktif ditegakkan sejak MySQL 8.0.16), selain tetap divalidasi di application layer (hari kerja vs hari kalender).
- **Hard constraint** seperti TOEFL ≥ 475 / EAP ≥ 65 dan similarity Turnitin ≤ 25% ditampung generik di `sidang_validasi_khusus` agar mudah menambah jenis validasi baru tanpa migrasi skema.
- **Konfigurasi bobot penilaian** (`rubrik_penilaian.versi_konfigurasi`, `dikunci_oleh`) mendukung mitigasi risiko *Integritas Nilai* — bobot dikunci resmi oleh Komisi Tesis.
- **UUID sebagai Primary Key**: setiap tabel memakai `CHAR(36) DEFAULT (UUID())`. Butuh MySQL ≥ 8.0.13; jika target server MariaDB, generate UUID dari application layer sebelum `INSERT`.
- **`updated_at` auto-update**: memakai `ON UPDATE CURRENT_TIMESTAMP` bawaan MySQL (tidak perlu trigger manual).
- Kolom array (`lampiran_urls` pada `notifikasi_wa_log`) memakai tipe **JSON** bawaan MySQL.

Cara memuat skema:

```bash
mysql -u root -p < database/schema.sql
# skema otomatis membuat & memakai database "simtesis"
```

## Formulir (`forms/`)

Setiap formulir:

- Memakai kop surat FKIP UNS placeholder (ganti logo & data di `forms/assets/style.css` / tiap file HTML sesuai kebutuhan resmi).
- Berkode dokumen sesuai matriks pada proposal (FPT-TI-01 s.d. 09 untuk Sempro; penomoran FPT-SH-xx untuk Semhas dan FPT-UT-xx untuk Ujian Tesis dibuat mengikuti pola yang sama karena proposal belum memberi kode eksplisit untuk 2 tahap tersebut — silakan sesuaikan penomoran resmi bila prodi sudah menetapkannya).
- Memiliki tombol **Cetak/Simpan PDF** dan tombol submit yang tinggal dihubungkan ke endpoint backend (`POST` sesuai tabel terkait di atas).
- Siap dipakai sebagai acuan/skeleton saat membangun form React/Vue — struktur field, nama `name=`, dan validasi (required/H-14/upload) sudah merepresentasikan kebutuhan pada Bab II & III proposal.

Buka `forms/index.html` untuk navigasi lengkap ke seluruh dokumen.

## Pemetaan Modul ke Tabel Database (ringkas)

| Formulir | Tabel Utama |
|---|---|
| FPT-PB-01 | `topik_penelitian`, `penetapan_pembimbing` |
| FPT-PB-02 | `logbook_bimbingan` |
| FPT-TI-01 / FPT-SH-01 / FPT-UT-01 | `sidang`, `sidang_dokumen` |
| FPT-TI-02 / checklist administrasi | `sidang_dokumen`, `sidang_validasi_khusus` |
| FPT-TI-03 / FPT-SH-02 / FPT-UT-04 | `nilai_individu`, `rubrik_penilaian` |
| FPT-TI-04 / FPT-SH-03 | `nilai_individu.catatan_kualitatif` |
| FPT-TI-05/06 / FPT-SH-04/05 / FPT-UT-05 | `rekap_nilai`, `berita_acara` |
| FPT-TI-07/08 / FPT-SH-06 | `presensi_sidang` |
| FPT-TI-09 / FPT-SH-07 / FPT-UT-06 | `revisi_matriks`, `persetujuan_revisi` |
| Surat Tugas / Undangan (semua tahap) | `dewan_penguji`, `surat_keluar`, `notifikasi_wa_log`, `kalender_sync` |
| FPT-UT-07 | `yudisium`, `state_transition_log` |

## Lisensi & Atribusi

Dokumen ini disusun sebagai turunan teknis dari proposal internal
*"Pengembangan Sistem Informasi Manajemen Siklus Lengkap Tahapan Tesis
(SIM-TESIS)"* — Program Studi Magister FKIP Universitas Sebelas Maret.
Sesuaikan kembali dengan SOP resmi prodi sebelum digunakan secara produksi.
