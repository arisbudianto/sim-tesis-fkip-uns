<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Modul "Daftarkan Langsung oleh Komisi Tesis" — untuk mahasiswa lama
     * yang Sempro/Semhas/Ujian Tesis-nya tidak melalui alur pendaftaran
     * mandiri (upload dokumen) di sistem ini. Kolom berkas yang sebelumnya
     * WAJIB (NOT NULL) dilonggarkan menjadi boleh kosong, supaya Komisi
     * Tesis bisa membuat entri pendaftaran "verified" langsung tanpa
     * dokumen di-upload lebih dulu, lalu melanjutkan ke plotting jadwal
     * sidang seperti biasa.
     *
     * Dipakai raw SQL (bukan ->change()) karena paket doctrine/dbal tidak
     * terpasang di proyek ini — Laravel mewajibkannya untuk memodifikasi
     * kolom lewat Schema Builder.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE pendaftaran_sempros MODIFY naskah_proposal_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_sempros MODIFY bukti_spp_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_sempros MODIFY khs_url VARCHAR(255) NULL');

        DB::statement('ALTER TABLE pendaftaran_semhas MODIFY naskah_bab_1_5_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_semhas MODIFY draf_artikel_ilmiah_urls JSON NULL');
        DB::statement('ALTER TABLE pendaftaran_semhas MODIFY bukti_status_under_review_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_semhas MODIFY bukti_spp_url VARCHAR(255) NULL');

        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY naskah_tesis_lengkap_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY artikel_jurnal_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY prosiding_seminar_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY sertifikat_bahasa_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY skor_bahasa SMALLINT UNSIGNED NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY bukti_spp_terakhir_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY khs_kumulatif_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY surat_bebas_plagiasi_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE pendaftaran_ujians MODIFY similarity_score DECIMAL(5,2) NULL');
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan ke NOT NULL — kalau sudah ada baris
        // "daftar langsung" dengan kolom ini NULL, rollback otomatis akan
        // gagal karena melanggar constraint lama. Perbaiki data dulu secara
        // manual kalau benar-benar perlu mengembalikan constraint ini.
    }
};
