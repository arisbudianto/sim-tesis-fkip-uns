<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration KONSOLIDASI — menggantikan 2 file migration duplikat yang
     * tidak sengaja terbuat di sesi berbeda (2026_09_08_150000 dan
     * 2026_09_08_160000, keduanya sudah dihapus) untuk tujuan yang sama:
     * menambah kolom `file_path` & `versi` ke `dokumen_cetaks` — dasar
     * dari perilaku idempotent (PDF disimpan sekali, di-download ulang
     * tanpa regenerate) dan sistem versioning di DocumentGeneratorService.
     *
     * Ditulis ULANG memakai pengecekan Schema::hasColumn()/hasIndex()
     * eksplisit supaya AMAN dijalankan berapa kali pun dan di kondisi
     * database apa pun — termasuk kalau salah satu migration duplikat
     * yang lama itu SEMPAT ter-apply sebagian di server sebelum
     * ditemukan & dihapus (idempotent terhadap dirinya sendiri, bukan
     * cuma idempotent terhadap data aplikasi).
     */
    public function up(): void
    {
        Schema::table('dokumen_cetaks', function (Blueprint $table) {
            if (!Schema::hasColumn('dokumen_cetaks', 'file_path')) {
                $table->string('file_path')->nullable()->after('hash_verifikasi');
            }
            if (!Schema::hasColumn('dokumen_cetaks', 'versi')) {
                $table->unsignedTinyInteger('versi')->default(1)->after('file_path');
            }
        });

        // Idempotent lookup: satu kombinasi dokumen+record+versi cuma boleh
        // ada satu baris. Versi lama TETAP tersimpan (tidak dihapus) demi
        // histori/audit, "versi aktif" = versi bernomor tertinggi.
        if (!$this->indexExists('dokumen_cetaks', 'unique_dokumen_versi')) {
            Schema::table('dokumen_cetaks', function (Blueprint $table) {
                $table->unique(['kode_dokumen', 'dokumentable_type', 'dokumentable_id', 'versi'], 'unique_dokumen_versi');
            });
        }
    }

    public function down(): void
    {
        Schema::table('dokumen_cetaks', function (Blueprint $table) {
            if ($this->indexExists('dokumen_cetaks', 'unique_dokumen_versi')) {
                $table->dropUnique('unique_dokumen_versi');
            }
            if (Schema::hasColumn('dokumen_cetaks', 'versi')) {
                $table->dropColumn('versi');
            }
            if (Schema::hasColumn('dokumen_cetaks', 'file_path')) {
                $table->dropColumn('file_path');
            }
        });
    }

    /**
     * Cek keberadaan index/constraint secara portable (MySQL & SQLite) —
     * Laravel tidak punya Schema::hasIndex() bawaan yang portable lintas
     * driver, jadi dicek manual lewat information_schema/pragma.
     */
    protected function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('{$table}')");
            foreach ($indexes as $idx) {
                if ($idx->name === $indexName) return true;
            }
            return false;
        }

        // MySQL / MariaDB
        $result = DB::select(
            "SELECT COUNT(1) as jumlah FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?",
            [$table, $indexName]
        );

        return ($result[0]->jumlah ?? 0) > 0;
    }
};
