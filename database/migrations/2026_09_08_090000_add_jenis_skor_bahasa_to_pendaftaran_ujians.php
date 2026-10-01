<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom skor_bahasa lama tidak membedakan jenis tes (EAP vs TOEFL),
     * padahal ambang lulusnya beda: EAP >= 65 ATAU TOEFL >= 475. Validasi
     * lama (`min:475`) keliru menolak skor EAP valid yang di bawah 475
     * tapi sebenarnya lolos (>= 65). Kolom ini memungkinkan validasi
     * hard constraint yang benar per jenis tes.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_ujians', function (Blueprint $table) {
            $table->enum('jenis_skor_bahasa', ['EAP', 'TOEFL'])->default('TOEFL')->after('sertifikat_bahasa_url');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_ujians', function (Blueprint $table) {
            $table->dropColumn('jenis_skor_bahasa');
        });
    }
};
