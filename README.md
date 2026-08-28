# SIM-TESIS: Sistem Informasi Manajemen Tesis 4 Tahap
### Magister Pendidikan Guru Vokasi — FKIP Universitas Sebelas Maret

Aplikasi berbasis **Laravel 13 & PHP 8.3+** untuk mendigitalkan seluruh siklus pengelolaan tugas akhir magister (Tesis) secara *paperless*, transparan, dan terintegrasi penuh dengan WhatsApp API & Tanda Tangan Elektronik (TTE QR Code).

---

## 🚀 4 Tahapan Utama yang Didukung
1. **Tahap 1: Penetapan Dosen Pembimbing** (Pembimbing 1 Bidang Studi & Pembimbing 2 Bidang Kependidikan oleh Komisi Tesis).
2. **Tahap 2: Seminar Proposal (Sempro)**:
   - Formulir `FPT-TI-01` (Permohonan Ujian Tesis 1)
   - Formulir `FPT-TI-02` (Checklist Administrasi H-14)
   - Surat Tugas Dekanat & Undangan Resmi Prodi (WA Blast)
   - Formulir `FPT-TI-03` (Rubrik Penilaian 10 Aspek) & `FPT-TI-04` (Catatan Revisi)
   - Formulir `FPT-TI-05 & 06` (Rekapitulasi & Berita Acara Rekomendasi)
   - Formulir `FPT-TI-07 & 08` (Daftar Hadir Penguji & Audiens)
   - Formulir `FPT-TI-09` (Persetujuan Revisi Sempro)
3. **Tahap 3: Penelitian Lapangan, Olah Data & Seminar Hasil (Semhas)**:
   - Permohonan Seminar Hasil Riset & Luaran Publikasi
   - Surat Tugas Dekan & Undangan Semhas
   - Rubrik Penilaian 4 Dimensi (21 Aspek & Validasi Luaran Jurnal/Prosiding)
   - Persetujuan Revisi Seminar Hasil (Syarat Maju Ujian Tesis)
4. **Tahap 4: Ujian Tesis (Sidang Akhir) & Kelulusan Yudisium**:
   - Permohonan Ujian Tesis & Validasi 9 Dokumen Prasyarat (EAP >= 65 / TOEFL >= 475 & Similarity <= 25%)
   - Surat Tugas Dekan & Undangan Ujian Tesis
   - Rubrik Evaluasi Digital & Berita Acara Ujian Tesis (BAP)
   - Formulir Persetujuan Revisi Tesis Akhir Menuju Wisuda & Ijazah

---

## 🛠 Instalasi & Setup Basis Data

```bash
# 1. Clone repository
git clone https://github.com/username/sim-tesis-fkip-uns.git
cd sim-tesis-fkip-uns

# 2. Install dependencies
composer install
npm install && npm run build

# 3. Konfigurasi Environment (.env)
cp .env.example .env
php artisan key:generate

# 4. Migrasi & Seeding Database
php artisan migrate:fresh --seed

# 5. Jalankan Antrean Background & Server
php artisan queue:work &
php artisan serve
```

---

## 📂 Struktur Repositori
- `database/migrations/` : Skema relasional PostgreSQL/MySQL 4 tahap tesis.
- `database/seeders/` : Data awal pengguna (Kaprodi, Dekanat, Komisi Tesis, Mahasiswa).
- `resources/views/forms/` : Template cetak resmi berstandar format FKIP UNS (Blade HTML/PDF).
- `app/Models/` : Model data Eloquent ORM.
