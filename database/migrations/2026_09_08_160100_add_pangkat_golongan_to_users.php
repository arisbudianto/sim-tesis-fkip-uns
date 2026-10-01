<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Dipakai khusus dosen — ditampilkan di Surat Tugas Penguji
            // resmi bersama nama & NIP (kolom `identifier`).
            $table->string('pangkat_golongan')->nullable()->after('bidang_keahlian');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pangkat_golongan');
        });
    }
};
