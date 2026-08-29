<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu mahasiswa hanya boleh punya satu pengajuan tesis. Constraint ini
     * jadi jaring pengaman terakhir di level database, melengkapi validasi
     * yang sudah ditambahkan di PengajuanTesisController::store().
     *
     * PENTING: kalau di database sudah ada data duplikat (mahasiswa_id yang
     * sama muncul di lebih dari satu baris pengajuan_tesis), migration ini
     * akan GAGAL saat dijalankan. Bersihkan duplikat itu dulu secara manual
     * sebelum migrate — lihat instruksi di chat.
     */
    public function up(): void
    {
        Schema::table('pengajuan_tesis', function (Blueprint $table) {
            $table->unique('mahasiswa_id', 'unique_mahasiswa_pengajuan');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_tesis', function (Blueprint $table) {
            $table->dropUnique('unique_mahasiswa_pengajuan');
        });
    }
};
