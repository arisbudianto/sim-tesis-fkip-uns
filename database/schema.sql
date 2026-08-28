-- =====================================================================
-- SIM-TESIS FKIP UNS — Skema Basis Data (MySQL 8.0+)
-- Cakupan: Tahap 1 Pembimbing, Tahap 2 Sempro, Tahap 3 Semhas,
--          Tahap 4 Ujian Tesis, Pasca Ujian & Yudisium
-- Kode Dokumen: PROP-SIMTESIS-4STAGE-2026 (turunan teknis)
--
-- Catatan versi: memerlukan MySQL 8.0.16+ agar CONSTRAINT CHECK aktif
-- ditegakkan (bukan hanya di-parse), dan MySQL 8.0.13+ agar
-- `DEFAULT (UUID())` pada PRIMARY KEY berfungsi. Jika memakai MariaDB,
-- ganti `DEFAULT (UUID())` dengan pengisian UUID dari application layer.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS simtesis
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simtesis;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. USERS & PROFIL AKTOR
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id              CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    nama_lengkap    VARCHAR(150) NOT NULL,
    email           VARCHAR(190) NOT NULL UNIQUE,   -- collation tabel bersifat case-insensitive
    password_hash   TEXT NOT NULL,
    no_hp           VARCHAR(20),
    role            ENUM('mahasiswa','dosen_pembimbing','komisi_tesis','admin_prodi',
                          'kaprodi','dewan_penguji','dekanat','super_admin') NOT NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mahasiswa (
    user_id         CHAR(36) NOT NULL PRIMARY KEY,
    nim             VARCHAR(20) NOT NULL UNIQUE,
    program_studi   VARCHAR(120) NOT NULL DEFAULT 'S2 Pendidikan Guru Vokasi FKIP UNS',
    angkatan        SMALLINT NOT NULL,
    status_studi    VARCHAR(30) NOT NULL DEFAULT 'aktif',
    tanggal_masuk   DATE NOT NULL,
    CONSTRAINT fk_mhs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE dosen (
    user_id             CHAR(36) NOT NULL PRIMARY KEY,
    nidn                VARCHAR(20) NOT NULL UNIQUE,
    nip                 VARCHAR(30),
    gelar_akademik      VARCHAR(60),
    bidang_keahlian     VARCHAR(200) NOT NULL,
    kategori_bidang     VARCHAR(50),   -- 'bidang_studi' | 'bidang_kependidikan'
    kuota_bimbingan_max SMALLINT NOT NULL DEFAULT 8,
    jabatan_struktural  VARCHAR(100),  -- Wadek I, Kaprodi, Ketua Komisi Tesis, dst
    CONSTRAINT fk_dsn_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. TAHAP 1 — PENENTUAN PEMBIMBING
-- ---------------------------------------------------------------------
CREATE TABLE topik_penelitian (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    mahasiswa_id        CHAR(36) NOT NULL,
    judul_topik         TEXT NOT NULL,
    bidang_minat        VARCHAR(150) NOT NULL,
    ringkasan_topik     TEXT,
    tanggal_ajuan       DATE NOT NULL DEFAULT (CURRENT_DATE),
    status              ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_topik_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE penetapan_pembimbing (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    topik_id            CHAR(36) NOT NULL,
    mahasiswa_id        CHAR(36) NOT NULL,
    pembimbing_1_id     CHAR(36) NOT NULL,  -- Spesialis Bidang Ilmu
    pembimbing_2_id     CHAR(36) NOT NULL,  -- Spesialis Metodologi/Kependidikan
    ditetapkan_oleh     CHAR(36),           -- Komisi Tesis
    nomor_sk            VARCHAR(80),
    tanggal_sk          DATE,
    file_sk_url         TEXT,
    status              ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pp_topik FOREIGN KEY (topik_id) REFERENCES topik_penelitian(id),
    CONSTRAINT fk_pp_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id),
    CONSTRAINT fk_pp_pemb1 FOREIGN KEY (pembimbing_1_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_pp_pemb2 FOREIGN KEY (pembimbing_2_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_pp_penetap FOREIGN KEY (ditetapkan_oleh) REFERENCES users(id),
    CONSTRAINT chk_pembimbing_beda CHECK (pembimbing_1_id <> pembimbing_2_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE logbook_bimbingan (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    mahasiswa_id        CHAR(36) NOT NULL,
    dosen_id            CHAR(36) NOT NULL,
    tahap_terkait       ENUM('sempro','semhas','ujian_tesis'),  -- NULL jika bimbingan umum
    tanggal_bimbingan   DATE NOT NULL,
    media_bimbingan     VARCHAR(50) DEFAULT 'tatap_muka',  -- tatap_muka/daring
    catatan_mahasiswa   TEXT NOT NULL,
    catatan_dosen       TEXT,
    status_approval     ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    tanggal_approval    DATETIME,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lb_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id),
    CONSTRAINT fk_lb_dsn FOREIGN KEY (dosen_id) REFERENCES dosen(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. SIDANG (GENERIK: SEMPRO / SEMHAS / UJIAN TESIS)
-- ---------------------------------------------------------------------
CREATE TABLE sidang (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    mahasiswa_id        CHAR(36) NOT NULL,
    jenis_sidang        ENUM('sempro','semhas','ujian_tesis') NOT NULL,
    judul_tesis_saat_ini TEXT NOT NULL,
    tanggal_pengajuan   DATE NOT NULL DEFAULT (CURRENT_DATE),
    tanggal_sidang      DATE,
    waktu_mulai         TIME,
    waktu_selesai       TIME,
    mode_pelaksanaan    VARCHAR(20) DEFAULT 'luring',  -- luring/daring/hybrid
    ruang_atau_link     TEXT,
    zoom_meeting_id     VARCHAR(50),
    zoom_passcode       VARCHAR(20),
    status              ENUM('draft','diajukan','verifikasi_berkas','terjadwal','terlaksana',
                              'dibatalkan','lulus_revisi_kecil','lulus_revisi_besar',
                              'tidak_lulus','selesai') NOT NULL DEFAULT 'draft',
    sidang_prasyarat_id CHAR(36),  -- rujukan tahap sebelumnya (state machine)
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_sidang_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id),
    CONSTRAINT fk_sidang_prasyarat FOREIGN KEY (sidang_prasyarat_id) REFERENCES sidang(id),
    CONSTRAINT chk_h14 CHECK (
        tanggal_sidang IS NULL OR tanggal_sidang >= tanggal_pengajuan + INTERVAL 14 DAY
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_sidang_mahasiswa ON sidang(mahasiswa_id, jenis_sidang);

-- Dokumen prasyarat / unggahan per sidang (menampung 5 berkas ujian tesis,
-- 9 checklist persyaratan, draf artikel multi-file Semhas, dll.)
CREATE TABLE sidang_dokumen (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    jenis_dokumen       ENUM('naskah_proposal','naskah_tesis_bab1_5','naskah_tesis_final',
                              'draf_artikel_ilmiah','bukti_status_publikasi','bukti_spp',
                              'logbook_bimbingan','kartu_hasil_studi','sertifikat_bahasa',
                              'surat_bebas_plagiasi','lembar_persetujuan_revisi',
                              'bukti_seminar_prosiding','lainnya') NOT NULL,
    nama_file           VARCHAR(255) NOT NULL,
    file_url            TEXT NOT NULL,
    ukuran_bytes        BIGINT,
    diunggah_oleh       CHAR(36),
    status_verifikasi   ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    diverifikasi_oleh   CHAR(36),
    catatan_verifikasi  TEXT,
    uploaded_at         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sd_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_sd_uploader FOREIGN KEY (diunggah_oleh) REFERENCES users(id),
    CONSTRAINT fk_sd_verifikator FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Validasi kelayakan khusus (skor EAP/TOEFL, similarity Turnitin, dst.)
CREATE TABLE sidang_validasi_khusus (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    jenis_validasi      VARCHAR(60) NOT NULL,  -- 'skor_toefl','skor_eap','similarity_turnitin','jumlah_draf_artikel'
    nilai_numerik       DECIMAL(6,2),
    nilai_teks          VARCHAR(100),
    ambang_batas        VARCHAR(50),   -- deskripsi threshold, mis. '>=475' atau '<=25%'
    lolos_validasi      BOOLEAN,
    diverifikasi_oleh   CHAR(36),
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_svk_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_svk_verifikator FOREIGN KEY (diverifikasi_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dewan Penguji per sidang (2 Pembimbing + 2 penguji untuk ujian tesis,
-- komposisi lebih ringkas untuk sempro/semhas)
CREATE TABLE dewan_penguji (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    dosen_id            CHAR(36) NOT NULL,
    peran               ENUM('ketua_penguji','sekretaris_penguji','penguji_bidang_studi',
                              'penguji_bidang_pendidikan','pembimbing_1','pembimbing_2') NOT NULL,
    status_konfirmasi   ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    ditetapkan_oleh     CHAR(36),  -- Komisi Tesis
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dp_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_dp_dosen FOREIGN KEY (dosen_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_dp_penetap FOREIGN KEY (ditetapkan_oleh) REFERENCES users(id),
    CONSTRAINT uq_dp UNIQUE (sidang_id, dosen_id, peran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log deteksi bentrok jadwal (Real-time Conflict Detection)
CREATE TABLE jadwal_konflik_log (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    dosen_id            CHAR(36) NOT NULL,
    sidang_id_baru      CHAR(36) NOT NULL,
    sidang_id_bentrok   CHAR(36) NOT NULL,
    terdeteksi_pada     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolusi            VARCHAR(30) DEFAULT 'ditolak_sistem',
    CONSTRAINT fk_jkl_dosen FOREIGN KEY (dosen_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_jkl_baru FOREIGN KEY (sidang_id_baru) REFERENCES sidang(id),
    CONSTRAINT fk_jkl_bentrok FOREIGN KEY (sidang_id_bentrok) REFERENCES sidang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Presensi (penguji & audiens, termasuk absensi barcode audiens FPT-TI-07/08)
CREATE TABLE presensi_sidang (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    user_id             CHAR(36),
    nama_audiens_manual VARCHAR(150),  -- jika audiens non-user terdaftar
    jenis_kehadiran     VARCHAR(20) NOT NULL,  -- 'penguji' | 'audiens'
    metode_presensi     VARCHAR(20) DEFAULT 'digital',  -- digital/barcode
    waktu_hadir         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ps_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_ps_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. RUBRIK PENILAIAN & NILAI
-- ---------------------------------------------------------------------
CREATE TABLE rubrik_penilaian (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    jenis_sidang        ENUM('sempro','semhas','ujian_tesis') NOT NULL,
    kode_dimensi        VARCHAR(20) NOT NULL,  -- I, II, III, IV
    nama_dimensi        VARCHAR(150) NOT NULL,
    deskripsi_indikator TEXT,
    skala_min           DECIMAL(5,2) NOT NULL DEFAULT 0,
    skala_max           DECIMAL(5,2) NOT NULL DEFAULT 100,
    bobot_persen        DECIMAL(5,2) NOT NULL DEFAULT 25,
    versi_konfigurasi   VARCHAR(20) NOT NULL DEFAULT 'v1',
    dikunci_oleh        CHAR(36),  -- Komisi Tesis mengunci bobot
    tanggal_dikunci     DATETIME,
    CONSTRAINT fk_rp_pengunci FOREIGN KEY (dikunci_oleh) REFERENCES users(id),
    CONSTRAINT uq_rp UNIQUE (jenis_sidang, kode_dimensi, versi_konfigurasi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nilai_individu (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    penguji_id          CHAR(36) NOT NULL,
    rubrik_id           CHAR(36) NOT NULL,
    skor                DECIMAL(5,2) NOT NULL,
    catatan_kualitatif  TEXT,
    submitted_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ni_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_ni_penguji FOREIGN KEY (penguji_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_ni_rubrik FOREIGN KEY (rubrik_id) REFERENCES rubrik_penilaian(id),
    CONSTRAINT uq_ni UNIQUE (sidang_id, penguji_id, rubrik_id),
    CONSTRAINT chk_skor CHECK (skor >= 0 AND skor <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rekap_nilai (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL UNIQUE,
    nilai_rata_rata     DECIMAL(5,2),
    predikat            VARCHAR(5),  -- A, A-, B+, B, C+
    keputusan_kelulusan VARCHAR(30), -- lulus_tanpa_revisi, lulus_revisi_kecil, lulus_revisi_besar, tidak_lulus
    batas_waktu_revisi  DATE,
    disahkan_oleh       CHAR(36),  -- Ketua Sidang / Komisi Tesis
    tanggal_pengesahan  DATETIME,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rn_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_rn_pengesah FOREIGN KEY (disahkan_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE berita_acara (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL UNIQUE,
    nomor_ba            VARCHAR(80) NOT NULL UNIQUE,
    file_url            TEXT,
    qr_code_tte         TEXT,
    ditandatangani_oleh CHAR(36),
    tanggal_terbit      DATE NOT NULL DEFAULT (CURRENT_DATE),
    CONSTRAINT fk_ba_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_ba_ttd FOREIGN KEY (ditandatangani_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. REVISI & PERSETUJUAN
-- ---------------------------------------------------------------------
CREATE TABLE revisi_matriks (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    penguji_id          CHAR(36) NOT NULL,
    nomor_urut          SMALLINT NOT NULL,
    catatan_revisi      TEXT NOT NULL,
    halaman_rujukan     VARCHAR(30),
    hasil_revisi_mhs    TEXT,
    nomor_halaman_hasil VARCHAR(30),
    status              ENUM('pending','disetujui','ditolak','revisi') NOT NULL DEFAULT 'pending',
    tanggal_acc         DATETIME,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rm_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_rm_penguji FOREIGN KEY (penguji_id) REFERENCES dosen(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE persetujuan_revisi (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    sidang_id           CHAR(36) NOT NULL,
    naskah_final_url    TEXT,
    status_lengkap_acc  BOOLEAN NOT NULL DEFAULT FALSE,  -- true jika 4/4 (atau seluruh) penguji ACC
    disahkan_kaprodi_id CHAR(36),
    tanggal_pengesahan  DATETIME,
    qr_code_tte         TEXT,
    file_pdf_url        TEXT,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pr_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id) ON DELETE CASCADE,
    CONSTRAINT fk_pr_kaprodi FOREIGN KEY (disahkan_kaprodi_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. SURAT-MENYURAT & NOTIFIKASI
-- ---------------------------------------------------------------------
CREATE TABLE surat_keluar (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    jenis_surat         ENUM('surat_tugas_penguji','undangan_menguji','sk_pembimbing',
                              'sk_kelulusan','lembar_persetujuan_revisi','berkas_wisuda') NOT NULL,
    sidang_id           CHAR(36),
    nomor_surat         VARCHAR(100) NOT NULL UNIQUE,
    penandatangan_id    CHAR(36),
    file_url            TEXT,
    qr_code_tte         TEXT,
    tanggal_terbit      DATE NOT NULL DEFAULT (CURRENT_DATE),
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sk_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id),
    CONSTRAINT fk_sk_ttd FOREIGN KEY (penandatangan_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- lampiran_urls memakai JSON (pengganti ARRAY di PostgreSQL), mis.
-- '["https://.../a.pdf", "https://.../b.pdf"]'
CREATE TABLE notifikasi_wa_log (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    surat_id            CHAR(36),
    sidang_id           CHAR(36),
    nomor_tujuan        VARCHAR(20) NOT NULL,
    penerima_user_id    CHAR(36),
    isi_pesan           TEXT,
    lampiran_urls       JSON,
    status_kirim        ENUM('terkirim','gagal','menunggu') NOT NULL DEFAULT 'menunggu',
    waktu_kirim         DATETIME,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nwl_surat FOREIGN KEY (surat_id) REFERENCES surat_keluar(id),
    CONSTRAINT fk_nwl_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id),
    CONSTRAINT fk_nwl_penerima FOREIGN KEY (penerima_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE kalender_sync (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    dosen_id            CHAR(36) NOT NULL,
    sidang_id           CHAR(36) NOT NULL,
    ics_file_url        TEXT,
    google_calendar_event_id VARCHAR(150),
    synced_at           DATETIME,
    CONSTRAINT fk_ks_dosen FOREIGN KEY (dosen_id) REFERENCES dosen(user_id),
    CONSTRAINT fk_ks_sidang FOREIGN KEY (sidang_id) REFERENCES sidang(id),
    CONSTRAINT uq_ks UNIQUE (dosen_id, sidang_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. YUDISIUM & STATE MACHINE
-- ---------------------------------------------------------------------
CREATE TABLE yudisium (
    id                      CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    mahasiswa_id            CHAR(36) NOT NULL UNIQUE,
    status                  ENUM('belum_memenuhi','siap_yudisium','terdaftar_wisuda','lulus')
                                NOT NULL DEFAULT 'belum_memenuhi',
    nomor_sk_kelulusan      VARCHAR(100),
    file_sk_kelulusan_url   TEXT,
    tanggal_lulus           DATE,
    file_berkas_wisuda_url  TEXT,
    tersinkron_portal_wisuda BOOLEAN NOT NULL DEFAULT FALSE,
    bebas_pustaka           BOOLEAN NOT NULL DEFAULT FALSE,
    updated_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_yd_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lifecycle State Engine: tahap berikutnya hanya aktif jika tahap
-- sebelumnya berstatus ACC (mitigasi risiko "State Inconsistency")
CREATE TABLE state_transition_log (
    id                  CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
    mahasiswa_id        CHAR(36) NOT NULL,
    dari_state          VARCHAR(40),
    ke_state            VARCHAR(40) NOT NULL,  -- pembimbing/sempro/semhas/ujian_tesis/yudisium
    sidang_terkait_id   CHAR(36),
    actor_id            CHAR(36),
    catatan             TEXT,
    created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stl_mhs FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(user_id),
    CONSTRAINT fk_stl_sidang FOREIGN KEY (sidang_terkait_id) REFERENCES sidang(id),
    CONSTRAINT fk_stl_actor FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. SEED DATA MINIMAL (rubrik penilaian ujian tesis, sesuai proposal 3.D.4)
-- ---------------------------------------------------------------------
INSERT INTO rubrik_penilaian (jenis_sidang, kode_dimensi, nama_dimensi, deskripsi_indikator, bobot_persen)
VALUES
 ('ujian_tesis', 'I',   'Kualitas Naskah Tesis',
  'Kejelasan bahasa, sistematika penulisan, kedalaman kajian teori, ketepatan metode, pembahasan, kebaruan (novelty), & dampak nyata riset.', 25),
 ('ujian_tesis', 'II',  'Luaran Karya Publikasi',
  'Capaian artikel jurnal (Sinta 1/2 / Scopus) dan prosiding seminar internasional bereputasi.', 25),
 ('ujian_tesis', 'III', 'Kualitas Presentasi',
  'Kelancaran penyampaian, efektivitas media presentasi, penguasaan materi, dan alokasi waktu.', 25),
 ('ujian_tesis', 'IV',  'Tanya Jawab & Penguasaan Teori',
  'Kemampuan argumentasi ilmiah, penguasaan metodologi, ketepatan menjawab sanggahan penguji, etika akademik.', 25);

-- =====================================================================
-- CATATAN IMPLEMENTASI (KHUSUS MYSQL)
-- 1. Semua ID memakai CHAR(36) berisi UUID v4, di-generate lewat
--    `DEFAULT (UUID())`. Jika target server adalah MariaDB atau MySQL
--    < 8.0.13, generate UUID di application layer sebelum INSERT.
-- 2. `updated_at` memakai `ON UPDATE CURRENT_TIMESTAMP` bawaan MySQL,
--    sehingga trigger manual (seperti di versi PostgreSQL) tidak
--    diperlukan lagi.
-- 3. CONSTRAINT CHECK (chk_h14, chk_pembimbing_beda, chk_skor) baru
--    benar-benar ditegakkan di MySQL 8.0.16 ke atas; pada versi lebih
--    lama constraint ini diterima tapi diam-diam diabaikan — tetap
--    validasi juga di application layer sebagai jaring pengaman.
-- 4. `sidang.sidang_prasyarat_id` merealisasikan State Machine: sebelum
--    membuat sidang Semhas, sistem memvalidasi sidang Sempro terkait
--    berstatus 'selesai' & keputusan_kelulusan ACC.
-- 5. `sidang_validasi_khusus` menampung syarat hard constraint seperti
--    TOEFL >= 475 / EAP >= 65 dan Similarity <= 25%.
-- 6. Kolom array PostgreSQL (`lampiran_urls TEXT[]`) dikonversi menjadi
--    tipe `JSON` di MySQL — simpan sebagai array string JSON.
-- =====================================================================
