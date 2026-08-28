<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengajuan_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->text('judul_tesis');
            $table->text('bidang_fokus')->nullable();
            
            // Dosen Pembimbing yang ditetapkan Komisi Tesis
            $table->foreignUuid('pembimbing_1_id')->nullable()->constrained('users');
            $table->foreignUuid('pembimbing_2_id')->nullable()->constrained('users');
            $table->timestamp('tgl_sk_pembimbing')->nullable();
            $table->string('sk_pembimbing_url')->nullable();
            
            // State 4-Tahapan
            $table->enum('tahap_aktif', ['penentuan_pembimbing', 'sempro', 'riset_lapangan', 'semhas', 'ujian_tesis', 'yudisium'])->default('penentuan_pembimbing');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('pengajuan_tesis');
    }
};
