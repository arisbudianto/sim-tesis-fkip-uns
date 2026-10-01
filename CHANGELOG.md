# Changelog — SIM-TESIS FKIP UNS

## [Unreleased] / 2026-09-08

### Added
- **DemoDataSeeder**: data bisnis realistis untuk demo end-to-end 4 tahap tesis
  (5+ mahasiswa di tahap berbeda, aktivitas sidang terkunci, dewan penguji,
  nilai sample, revisi lengkap & pending, state transition log).
- Fixed UUID users di DatabaseSeeder + populasi `user_roles` agar konsisten.
- Bagian **Keputusan Scope** dan **Cara Demo 15 Menit** di README.

### Changed
- DatabaseSeeder sekarang memanggil `DemoDataSeeder` setelah user & template notifikasi.

### Scope Decision (Out-of-Scope)
- **FR-02 Logbook Bimbingan Digital** sengaja tidak diimplementasikan pada fase ini
  sesuai keputusan tim pengembang (migration `drop_logbook_bimbingans_table`).
  Fitur dapat ditambahkan pada fase berikutnya jika stakeholder menghendaki.
  Tidak ada model, controller, route, atau tabel logbook aktif.

### Notes for QA / Reviewer
- Setelah `php artisan migrate:fresh --seed`, alur 4 tahap dapat didemonstrasikan
  tanpa setup manual tambahan.
- Lihat tabel akun demo di README bagian “Cara Demo 15 Menit”.
