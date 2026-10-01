<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sama seperti form_fpt_ti_01_url di pendaftaran_sempros: berkas FPT-SH-01
     * (Permohonan Seminar Hasil) yang sudah ditandatangani Pembimbing 1 & 2,
     * diunggah mahasiswa saat mendaftar Semhas, ditinjau Admin Prodi/Komisi
     * Tesis/Kaprodi sebelum menyetujui pendaftaran.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_semhas', function (Blueprint $table) {
            $table->string('form_fpt_sh_01_url')->nullable()->after('jadwal_usulan_sidang');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_semhas', function (Blueprint $table) {
            $table->dropColumn('form_fpt_sh_01_url');
        });
    }
};
