# Konvensi Kontribusi — SIM-TESIS FKIP UNS

## Konvensi Commit

Gunakan format **Conventional Commits** agar histori mudah ditelusuri per modul bisnis:

```
<tipe>(<domain>): <ringkasan singkat, present tense>

[opsional: penjelasan lebih detail]
```

**Tipe yang dipakai:** `feat`, `fix`, `refactor`, `chore`, `docs`, `test`, `style`.

**Domain mengikuti nama folder di `app/Domain/`:** `pembimbing`, `sempro`, `semhas`,
`ujian-tesis`, `sidang`, `state-engine`, `notifikasi`, `dokumen`, atau `infra` untuk
perubahan lintas-domain (routing, middleware, config, dsb).

Contoh:
```
feat(sempro): tambah validasi H-14 saat pendaftaran seminar proposal
fix(sidang): cegah dosen ditugaskan dobel sebagai penguji
refactor(infra): pindahkan Models & Services ke struktur app/Domain per bounded-context
docs: perbarui README dengan panduan setup Docker Compose
```

## Linting

Proyek memakai [Laravel Pint](https://laravel.com/docs/pint) dengan preset `laravel`
(lihat `pint.json`). Jalankan sebelum commit:

```bash
composer lint          # otomatis rapikan format kode
composer lint:check     # cuma cek, tanpa mengubah file (dipakai di CI)
```

## Struktur Modular

Setiap domain bisnis (`app/Domain/{Nama}/`) berisi `Models/`, `Services/`, dan
opsional `Actions/` miliknya sendiri. Controller HTTP tetap di
`app/Http/Controllers/{Nama}/` — **bukan** di dalam folder domain — supaya
domain layer tetap independen dari lapisan HTTP (bisa dipakai ulang lewat
command/queue job di masa depan tanpa bergantung ke request/response).

Sebelum menambah kelas baru, tentukan dulu domainnya masuk yang mana:
- **Pembimbing** — FR-01 (pengajuan judul, alokasi & kuota pembimbing)
- **Sempro** — FR-03 (pendaftaran seminar proposal)
- **Semhas** — FR-05 (pendaftaran seminar hasil)
- **UjianTesis** — FR-07, FR-10 (pendaftaran ujian, revisi & yudisium)
- **Sidang** — FR-04/06/08/09 (plotting jadwal, dewan penguji, penilaian —
  dipakai lintas Sempro/Semhas/UjianTesis karena satu tabel `aktivitas_sidangs`)
- **StateEngine** — mesin status siklus tesis (dipakai semua domain di atas)
- **Notifikasi** — WhatsApp blast & sinkronisasi kalender
- **Dokumen** — generator PDF resmi & verifikasi QR Code
- **Audit** — pencatatan log approval/penolakan (siapa, kapan, aksi apa)
