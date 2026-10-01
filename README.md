# SIM-TESIS FKIP UNIVERSITAS SEBELAS MARET
## Sistem Informasi Manajemen Siklus Lengkap 4 Tahapan Tesis Magister

Aplikasi ini mengimplementasikan arsitektur berorientasi alur kerja (*Workflow &
Lifecycle State Engine*) dengan Komisi Tesis sebagai pengendali operasional
utama. Monolith Laravel, dimodularisasi secara internal per **bounded-context
bisnis** (bukan microservices terpisah).

**Stack**: Laravel 12 (PHP 8.2+), MySQL 8.x, Blade + Alpine.js + Tailwind (via Vite),
session-based auth dengan RBAC berbasis middleware peran.

---

## Matriks Pemenuhan Kebutuhan Fungsional (FR)

| Kode FR | Kebutuhan | Domain | Service/Controller Utama |
| :--- | :--- | :--- | :--- |
| **FR-01** | Pengajuan Judul & Alokasi Pembimbing 1 & 2 | `Pembimbing` | `AdvisorQuotaEngine` |
| **FR-03** | Pendaftaran Sempro H-14 & Berkas FPT-TI-01/02 | `Sempro` | `PendaftaranSemproController` |
| **FR-04** | Plotting Tim Penguji Sempro & Jadwal | `Sidang` | `AntiConflictScheduler` |
| **FR-05** | Pendaftaran Semhas H-14 (2 Draf + 1 Under Review) | `Semhas` | `PendaftaranSemhasController` |
| **FR-06** | Plotting Jadwal & Rekap Hasil Riset Semhas | `Sidang` | `KomisiTesisController` |
| **FR-07** | Pendaftaran Ujian Tesis (8 Dokumen & H-14) | `UjianTesis` | `PendaftaranUjianController` |
| **FR-08** | Plotting 4 Dewan Penguji Ujian (2 Pemb, 1 Studi, 1 Pend) | `Sidang` | `AntiConflictScheduler` |
| **FR-09** | Rubrik 4 Dimensi Nilai, Konversi Grade & BAP | `Sidang` | `PenilaianSidangController` |
| **FR-10** | Matriks Revisi 4 Penguji, Pengesahan & Yudisium | `UjianTesis` | `RevisiDokumenController` |

> **Catatan:** FR-02 (Logbook Bimbingan Digital & Approval Berkala) sengaja
> **tidak diimplementasikan** — di luar cakupan pengembangan sesuai keputusan
> tim. Tidak ada model, controller, route, atau tabel logbook di sistem ini.

---

## Keputusan Scope (Out-of-Scope)

**FR-02 Logbook Bimbingan Digital** sengaja **tidak diimplementasikan** pada fase pengembangan ini sesuai keputusan tim pengembang (lihat migration `2026_09_06_120000_drop_logbook_bimbingans_table`). 

Alasan: fokus fase 1 diarahkan pada 4 tahap resmi sidang (Pembimbing → Sempro → Semhas → Ujian) + yudisium, validasi H-14, plotting anti-bentrok, penilaian digital, dan matriks revisi. Fitur logbook bimbingan dapat ditambahkan pada fase berikutnya jika stakeholder (Kaprodi / Komisi Tesis) menghendaki.

Tidak ada model, controller, route, atau tabel logbook aktif di sistem ini.

---

## Cara Demo 15 Menit

Setelah `php artisan migrate:fresh --seed` (atau `php artisan db:seed`):

| Akun | Email | Password | Yang bisa dilihat |
|------|-------|----------|-------------------|
| Mahasiswa (Tahap 1) | mhs.budi@student.uns.ac.id | password | Pengajuan + status bimbingan |
| Mahasiswa demo lain | mhs.andi@… / mhs.citra@… / mhs.doni@… / mhs.eka@… / mhs.fajar@… | password | Berbagai tahap (lihat Dashboard) |
| Dosen Pembimbing | herman@fkip.uns.ac.id / siti.rahma@fkip.uns.ac.id | password | Bimbingan + tugas penguji |
| Komisi Tesis | komisi.tesis@fkip.uns.ac.id | password | Plotting, rekap nilai, overview pipeline |
| Kaprodi | kaprodi.pgv@fkip.uns.ac.id | password | Pengesahan revisi, monitoring |
| Admin Prodi | admin.pasca@fkip.uns.ac.id | password | Verifikasi berkas pendaftaran |
| Penguji | ketua.penguji@… / sekretaris.penguji@… / penguji.studi@… / penguji.pendidikan@… | password | Tugas sidang & ACC revisi |

**Urutan demo disarankan:**
1. Login sebagai **Komisi Tesis** → lihat pipeline 5 tahap di dashboard.
2. Login sebagai **Dosen** (herman) → section “Tugas Sidang Saya” & revisi pending.
3. Login sebagai **Mahasiswa** (mhs.eka / mhs.citra) → status tahap & form pendaftaran.
4. Login sebagai **Admin** → antrian verifikasi.
5. Login sebagai **Kaprodi** → pengesahan revisi menuju yudisium.

Data seed mencakup: 5+ mahasiswa di tahap berbeda, sidang terkunci, nilai sample, revisi lengkap & pending.

---

## Struktur Modular per Bounded-Context

```
app/
├── Domain/                        <- Logic bisnis murni, TIDAK tahu soal HTTP
│   ├── Pembimbing/{Models,Services}      FR-01
│   ├── Sempro/Models                     FR-03
│   ├── Semhas/Models                     FR-05
│   ├── UjianTesis/Models                 FR-07, FR-10
│   ├── Sidang/{Models,Services}          FR-04, FR-06, FR-08, FR-09
│   │                                     (lintas-tahap: satu tabel
│   │                                      aktivitas_sidangs dgn kolom
│   │                                      tahap_sidang, bukan 3 tabel terpisah)
│   ├── StateEngine/Services              Lifecycle State Machine (Modul 4)
│   ├── Notifikasi/Services               WhatsApp blast & sinkronisasi kalender
│   └── Dokumen/{Models,Services}         Generator PDF resmi & QR TTE
│
├── Http/Controllers/
│   ├── Pembimbing/, Sempro/, Semhas/, UjianTesis/, Sidang/, Dokumen/
│   │   (controller HTTP TERPISAH dari Domain — supaya logic bisnis di
│   │    Domain/ bisa dipakai ulang lewat command/queue tanpa bergantung
│   │    ke Request/Response)
│   ├── AuthController.php         (cross-cutting: auth)
│   └── DashboardController.php    (cross-cutting: agregasi lintas domain)
│
└── Http/Middleware/RoleMiddleware.php   (RBAC — lihat alias 'role' di bootstrap/app.php)

resources/views/
├── pembimbing/, sempro/, semhas/, ujian-tesis/   <- Blade per modul
├── dashboard/tabs/                                <- Partial dashboard per modul
├── components/ui/                                 <- Blade Components reusable
│   (status-badge, stat-card, progress-pips, avatar, card, alert, dst)
└── components/layouts/                            <- Layout bersama (app, guest)

routes/web.php   <- Dikelompokkan per modul dengan prefix + name + middleware role
```

**Kenapa domain `Sidang` ada padahal tidak disebut eksplisit di scope awal?**
FR-04/06/08/09 (plotting jadwal, dewan penguji, penilaian rubrik) memakai satu
mekanisme yang identik untuk ketiga tahap sidang (Sempro/Semhas/Ujian) via satu
tabel `aktivitas_sidangs` + kolom `tahap_sidang`. Memaksakan model/controller ini
ke salah satu domain (mis. taruh semua di `UjianTesis`) akan menyesatkan — jadi
dipisah jadi domain `Sidang` tersendiri yang dipakai bersama oleh tiga domain
tahap di atasnya.

---

## Setup & Menjalankan Proyek

### Opsi A — Docker Compose (disarankan untuk pengembangan lokal)

```bash
cp .env.example .env
docker compose up -d
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Akses di `http://localhost:8000`. Vite dev server (hot-reload) otomatis jalan
di container `vite` pada port `5173`.

### Opsi B — Laravel Sail

```bash
cp .env.example .env
composer install
php artisan sail:install   # pilih service: mysql
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
```

### Opsi C — Manual (PHP & MySQL lokal sudah terpasang)

```bash
cp .env.example .env
composer install
npm install && npm run build      # atau `npm run dev` untuk mode watch
php artisan key:generate
# Sesuaikan DB_* di .env dengan MySQL lokal Anda, lalu:
php artisan migrate --seed
php artisan serve
```

### Menjalankan Ulang di Server Sudah Ada Data (Produksi)

**Jangan pernah** pakai `migrate:fresh` di server yang sudah ada data nyata —
itu akan **menghapus semua tabel**. Gunakan `php artisan migrate` biasa
(idempotent, cuma menjalankan migration baru yang belum pernah dijalankan).

Untuk deploy ke `pgv.speakverse.id`, lihat checklist produksi di `.env.example`
(bagian `APP_URL & PROFIL ENVIRONMENT`) — pastikan `APP_ENV=production`,
`APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.

---

## Akun Contoh (dari Seeder)

Jalankan `php artisan db:seed` (atau `migrate --seed`) untuk membuat akun contoh
tiap peran (mahasiswa, dosen, komisi_tesis, kaprodi, admin_prodi) — lihat
`database/seeders/DatabaseSeeder.php` untuk daftar identifier & password-nya.

---

## Linting & Konvensi Commit

```bash
composer lint          # rapikan format kode otomatis (Laravel Pint)
composer lint:check    # cek saja, tanpa mengubah file — cocok untuk CI
```

Lihat `CONTRIBUTING.md` untuk konvensi commit dan panduan menempatkan kelas
baru ke domain yang tepat.

---

## RBAC — Autentikasi & Otorisasi (Modul 3)

**Auth**: session-based (login via NIM/NIP/email), tabel `password_reset_tokens`
mendukung flow lupa password (`Illuminate\Support\Facades\Password` broker
bawaan Laravel — fungsinya setara Breeze meski bukan package Breeze literal).

**Multi-peran**: kolom `users.role` tetap ada sebagai "peran utama" (badge
tampilan), tapi keputusan otorisasi sesungguhnya bersumber dari tabel pivot
`user_roles` via `User::hasRole()` / `hasAnyRole()` — mendukung satu user
punya lebih dari satu peran sekaligus (mis. dosen yang juga Kaprodi).

**Gate & Policy** (`app/Policies/`, didaftarkan di `AppServiceProvider`):

| Policy | Model | Method Kunci |
|---|---|---|
| `PenugasanPembimbingPolicy` | `PengajuanTesis` | `allocate` (alokasi pembimbing — Komisi/Kaprodi/Admin), `daftarSidang` (mahasiswa HANYA untuk pengajuannya sendiri) |
| `SidangPolicy` | `AktivitasSidang` | `plot` (plotting — Komisi/Kaprodi/Admin), `nilai` (HANYA dosen yang tercatat sebagai penguji sidang tsb) |
| `RevisiPengujiPolicy` | `RevisiPenguji` | `acc` (HANYA dosen penguji ybs atau pengendali akademik) |

**Middleware role** (`role:komisi_tesis,kaprodi,...`) dipasang di grup route
per modul di `routes/web.php` sebagai lapisan pertama (cepat, di level HTTP);
Policy sebagai lapisan kedua untuk kepemilikan data spesifik per baris.

**Audit log** (`audit_logs` table, `App\Domain\Audit\Services\AuditLogger`):
mencatat siapa/kapan/aksi-apa untuk setiap approval/penolakan — verifikasi
Sempro/Semhas, alokasi pembimbing, plotting sidang, input nilai, ACC revisi,
pengesahan Kaprodi.

---

## StateEngine — Lifecycle Status Tesis (Modul 4)

**Enum resmi**: `App\Domain\StateEngine\TahapTesis` — 5 state linear
(`TAHAP_1_BIMBINGAN` → `TAHAP_2_SEMPRO` → `TAHAP_3_SEMHAS` → `TAHAP_4_UJIAN`
→ `SELESAI_YUDISIUM`).

**Service**: `App\Domain\StateEngine\Services\LifecycleStateMachine`
- `canTransitionTo($tesis, $target)` — validasi murni, tidak mengubah data.
- `transition($tesis, $target, $actor)` — **satu-satunya jalur sah** untuk
  mengubah `status_tahap` di seluruh sistem. Otomatis mencatat histori.
- `canTransition($mahasiswaId, $fromState, $toState)` / `transitionByMahasiswaId(...)`
  — varian sesuai signature spesifikasi Modul 4, untuk caller yang cuma
  punya ID (bukan instance model).
- `rollback($tesis, $target, $actor, $reason)` — **HANYA** `$actor` dengan
  role `komisi_tesis`, WAJIB menyertakan alasan. Tercatat sebagai
  `is_override = true` di histori, dibedakan dari transisi normal.

**Histori/Audit**: setiap transisi (maju maupun rollback) tercatat di tabel
`state_transition_log` (siapa, kapan, dari state apa ke state apa). Laporan
per mahasiswa bisa dilihat di halaman `pengajuan.historiStatus`
(`/pengajuan/{id}/histori-status`).

**Testing**: `tests/Feature/StateEngineTest.php` — 4 skenario (3 wajib +
1 kasus positif rollback), jalan di SQLite in-memory (lihat `phpunit.xml`),
**tidak pernah menyentuh MySQL produksi**:
```bash
composer test          # atau: php artisan test / vendor/bin/phpunit
```

⚠️ Kalau server belum punya ekstensi PHP `pdo_sqlite`, testing akan gagal
di tahap koneksi database — install dulu (`php-sqlite3` di Ubuntu/Debian,
biasanya sudah aktif secara default di kebanyakan hosting cPanel/WHM).

---

## Integrasi Eksternal

- **WhatsApp Gateway** (`App\Domain\Notifikasi\Services\WhatsAppNotifierService`):
  feature-flagged via `WHATSAPP_API_URL` & `WHATSAPP_API_TOKEN` di `.env`. Kalau
  kosong, notifikasi dilewati diam-diam (log-only) tanpa menggagalkan alur akademik.
- **Sinkronisasi Kalender** (`App\Domain\Notifikasi\Services\CalendarIcsService`):
  generate file `.ics` per sidang, diunduh dosen untuk diimpor ke Google
  Calendar/Outlook/Apple Calendar — lihat route `sidang.kalenderIcs`.
- **SIAKAD**: belum diimplementasikan — menunggu detail API resmi dari kampus.
