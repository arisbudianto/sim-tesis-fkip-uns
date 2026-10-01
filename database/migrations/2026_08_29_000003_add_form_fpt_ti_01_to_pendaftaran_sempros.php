<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Berkas FPT-TI-01 (Permohonan Ujian Tesis 1) yang sudah ditandatangani
     * Pembimbing 1 & 2, diunggah mahasiswa saat mendaftar Sempro. Admin
     * Prodi/Komisi Tesis/Kaprodi meninjau file ini sebelum menyetujui
     * pendaftaran — inilah bukti pembimbing sudah setuju proposal diseminarkan.
     */
    public function up(): void
    {
        Schema::table('pendaftaran_sempros', function (Blueprint $table) {
            $table->string('form_fpt_ti_01_url')->nullable()->after('khs_url');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran_sempros', function (Blueprint $table) {
            $table->dropColumn('form_fpt_ti_01_url');
        });
    }
};
