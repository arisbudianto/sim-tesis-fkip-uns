<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * FPT-TI-03 (Lembar Penilaian Individu) memakai rubrik 10 indikator,
     * bukan 4 dimensi seperti implementasi awal. Kolom lama nilai_dimensi_*
     * SENGAJA dipertahankan (nullable, tidak dihapus) — non-destruktif,
     * tidak memutus data yang mungkin sudah terlanjur terisi di produksi.
     */
    public function up(): void
    {
        Schema::table('penguji_sidangs', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->decimal("nilai_indikator_{$i}", 5, 2)->nullable()->after('nilai_dimensi_4_tanyajawab');
            }
        });
    }

    public function down(): void
    {
        Schema::table('penguji_sidangs', function (Blueprint $table) {
            for ($i = 1; $i <= 10; $i++) {
                $table->dropColumn("nilai_indikator_{$i}");
            }
        });
    }
};
