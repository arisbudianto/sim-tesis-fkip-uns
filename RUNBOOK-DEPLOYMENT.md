# RUNBOOK — Testing, CI/CD & Deployment SIM-TESIS FKIP UNS

> **Catatan konteks penting**: dokumen ini awalnya disusun mengasumsikan
> deployment ke VPS dengan akses root (Nginx, systemd/Supervisor, Certbot
> CLI). Kenyataannya, `pgv.speakverse.id` berjalan di **shared hosting
> cPanel** — beberapa poin di bawah disesuaikan ke cara yang benar-benar
> bisa dieksekusi di lingkungan ini, dengan versi "kalau nanti pindah ke
> VPS" tetap disertakan sebagai referensi.

---

## 0. ⚠️ Pelajaran dari Insiden Deployment

Pada suatu deployment, perintah `rsync --delete` dijalankan dengan folder
staging (`_staging3`) yang isinya tidak terverifikasi dulu — hasilnya,
`rsync` menghapus **hampir seluruh aplikasi produksi** karena sumbernya
kosong/salah. Database & `.env` selamat (backup manual + tidak ikut
`rsync`), tapi seluruh kode (`app/`, `bootstrap/`, `artisan`, dll) sempat
hilang sebelum dipulihkan.

**Aturan wajib mulai sekarang, sebelum menjalankan `rsync --delete`:**
```bash
# 1. Konfirmasi lokasi kerja
pwd

# 2. Konfirmasi SUMBER (staging) benar-benar berisi apa yang diharapkan
ls -la /home/speakver/_staging3/
test -f /home/speakver/_staging3/artisan && echo "artisan ADA di staging" || echo "!!! artisan TIDAK ADA — JANGAN LANJUT rsync !!!"

# 3. Baru jalankan rsync, HANYA setelah langkah 1-2 dikonfirmasi aman
```
Kalau langkah 2 menunjukkan staging kosong/tidak lengkap — **jangan
lanjutkan ke `rsync --delete`**, isi ulang staging dulu.

---

## 1. Strategi Testing

### 1.1 Unit & Feature Test (Modul 1-11)

Seluruh acceptance criteria Modul 1-11 sudah punya test otomatis di
`tests/Feature/`. **44 test** mencakup:

| File Test | Cakupan |
|---|---|
| `StateEngineTest.php` | Transisi valid, lompat tahap ditolak, rollback |
| `PenentuanPembimbingTest.php` | Alokasi pembimbing, validasi kuota |
| `RekapitulasiNilaiTest.php` | Rekap nilai wajib lengkap dulu |
| `ApprovalNaskahSemhasTest.php` | Approval pembimbing sebelum verifikasi admin |
| `PendaftaranUjianTesisTest.php` | EAP/TOEFL, similarity, komposisi penguji |
| `NotifikasiTerpusatTest.php` | Template DB, log, retry |
| `GeneratorDokumenTest.php` | Idempotent, versioning, verifikasi QR |
| `DashboardPerRoleTest.php` | Isolasi data per role (dashboard) |
| `AntiConflictSchedulerTest.php` | **Bentrok jadwal ruangan & dosen (kritikal)** |
| `OtorisasiMatrixTest.php` | **Matriks celah otorisasi (role X akses endpoint Y)** |
| `AlurLengkapIntegrationTest.php` | **Integration test end-to-end 1 mahasiswa penuh** |

Jalankan seluruh suite:
```bash
composer install
vendor/bin/phpunit
```

**Wajib hijau semua sebelum deploy ke produksi** (acceptance criteria Modul 12).

### 1.2 Security & Stress Testing

**Sudah tercakup via automated test:**
- `OtorisasiMatrixTest.php` — termasuk skenario mahasiswa mencoba
  mengajukan pengajuan tesis atas nama mahasiswa lain (celah yang pernah
  ditemukan & diperbaiki), plus matriks role-salah-akses-endpoint.
- `AntiConflictSchedulerTest.php` — termasuk pengujian constraint UNIQUE
  database sebagai lapisan pertahanan kedua kalau logic aplikasi ter-bypass
  race condition.
- Isolasi data per role (`DashboardPerRoleTest.php`) — mencakup beberapa
  putaran perbaikan setelah ditemukan kebocoran data lintas tab dashboard
  (bukan cuma tab Overview, tapi juga tab Pendaftaran, Sidang, dan
  Penilaian — termasuk skor nilai sempat terekspos ke mahasiswa lain
  sebelum diperbaiki).

**Perlu dijalankan manual (di luar PHPUnit)** — PHPUnit berjalan
single-thread, tidak bisa mensimulasikan beban HTTP konkuren sungguhan:

```bash
# Install k6 (https://k6.io) di komputer lokal Anda, BUKAN di server produksi
k6 run - <<'EOF'
import http from 'k6/http';
export const options = { vus: 20, duration: '10s' };
export default function () {
  http.post('https://pgv.speakverse.id/sempro/daftar/{pengajuan_id}', {
    // ... payload form + session cookie hasil login manual
  });
}
EOF
```

⚠️ **Jangan jalankan uji beban langsung ke `pgv.speakverse.id` produksi**
tanpa koordinasi dengan pihak hosting.

---

## 2. Pipeline CI/CD

### 2.1 GitHub Actions

File `.github/workflows/ci.yml` menjalankan build → test otomatis setiap
push/PR. Deploy ke server tetap manual (lihat §3) — hasil CI jadi sinyal
aman-tidaknya kode sebelum deploy manual.

### 2.2 Alur "Build → Test → Staging → Promote ke Produksi"

1. **Build & Test** — `vendor/bin/phpunit` wajib hijau di lokal/CI.
2. **Staging** — pakai `_staging3` sebagai staging area. **Verifikasi isi
   staging dulu** (lihat §0) sebelum promote.
3. **Promote ke Produksi** — `rsync` dari staging ke folder live (lihat
   `deploy.sh`), lalu migration + clear cache.

---

## 3. Deployment Produksi — `pgv.speakverse.id`

### 3.1 Virtual Host / Document Root
cPanel → **Domains** → `pgv.speakverse.id` → Document Root harus mengarah
ke `/home/speakver/pgv.speakverse.id/public`.

### 3.2 Sertifikat SSL
cPanel → **SSL/TLS Status** → AutoSSL (Let's Encrypt, auto-renewal
otomatis, tidak perlu cron manual).

### 3.3 Environment Produksi
`.env`: `APP_URL=https://pgv.speakverse.id`, `APP_ENV=production`,
`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`. Cache untuk produksi:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3.4 Script Deploy Terpadu
```bash
bash deploy.sh
```
Merangkum: composer install → npm build → migrate → cache clear+cache →
test suite (situs tetap maintenance kalau test gagal) → up.

### 3.5 Queue Worker
cPanel Cron Jobs (Supervisor tidak tersedia di shared hosting):
```
* * * * * cd /home/speakver/pgv.speakverse.id && php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

### 3.6 Scheduler
```
* * * * * cd /home/speakver/pgv.speakverse.id && php artisan schedule:run >> /dev/null 2>&1
```

### 3.7 Storage & Backup
```bash
php artisan storage:link
```
Backup 2 lapis: cPanel Backup bawaan (mingguan) + `scripts/backup.sh`
(harian via Cron Jobs, database + storage upload).

### 3.8 Checklist Keamanan Sebelum Go-Live
- [x] `APP_DEBUG=false`
- [x] `SESSION_SECURE_COOKIE=true`
- [x] Rate limiting login/register/reset-password (`throttle:5,1`)
- [x] Rate limiting upload Sempro/Semhas/Ujian (`throttle:10,1`)
- [x] `mahasiswa_id` tidak bisa dipilih bebas oleh role mahasiswa (dipaksa `Auth::id()`)
- [x] Dashboard per role diverifikasi tidak bocor data lintas tab (lihat §1.2)
- [ ] Ganti password akun seeder/demo sebelum data mahasiswa sungguhan masuk
- [ ] **Selalu verifikasi isi staging sebelum `rsync --delete`** (lihat §0)

---

## 4. Runbook Sosialisasi & Pelatihan

### 4.1 Komisi Tesis (1 sesi, ~90 menit)
Login & navigasi dashboard, alokasi pembimbing + cek kuota, plotting
jadwal & dewan penguji, rekap nilai & keputusan sidang, notifikasi WA.

### 4.2 Dosen (1 sesi, ~60 menit)
Dashboard bimbingan & jadwal penguji, approve naskah, input nilai
(rubrik 10 indikator vs 4 dimensi), ACC matriks revisi.

### 4.3 Admin Prodi & Kaprodi (1 sesi gabungan, ~60 menit)
Antrean verifikasi, pengesahan revisi (gateway yudisium), metrik capaian
masa studi & tingkat kelulusan, unduh dokumen resmi & Print Bundle.

### 4.4 Mahasiswa (materi tertulis/video)
Video tutorial singkat, FAQ tertulis (validasi H-14, syarat dokumen per
tahap, kontak Admin Prodi).

---

## 5. Ringkasan File yang Disertakan di Paket Ini

| File | Fungsi |
|---|---|
| `RUNBOOK-DEPLOYMENT.md` | Dokumen ini |
| `.github/workflows/ci.yml` | Pipeline CI (build → test) |
| `scripts/backup.sh` | Backup harian database + storage |
| `deploy.sh` | Ringkasan langkah deploy jadi satu script |
| `tests/Feature/AntiConflictSchedulerTest.php` | Test bentrok jadwal (kritikal) |
| `tests/Feature/OtorisasiMatrixTest.php` | Test celah otorisasi |
| `tests/Feature/AlurLengkapIntegrationTest.php` | Integration test end-to-end |
