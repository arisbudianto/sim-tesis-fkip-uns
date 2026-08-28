<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-01: Pengajuan Tesis & Alokasi Pembimbing 1 & 2
        Schema::create('pengajuan_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul_tesis', 500);
            $table->string('bidang_fokus');
            $table->text('abstrak_rencana')->nullable();
            
            // Dosen Pembimbing (Pembimbing 1: Studi, Pembimbing 2: Kependidikan)
            $table->foreignUuid('pembimbing_1_id')->nullable()->constrained('users');
            $table->foreignUuid('pembimbing_2_id')->nullable()->constrained('users');
            $table->string('nomor_sk_pembimbing')->nullable();
            $table->date('tanggal_sk_pembimbing')->nullable();
            
            // Lifecycle State Machine kaku (FR-01 s.d FR-10)
            $table->enum('status_tahap', [
                'tahap_1_bimbingan', 
                'tahap_2_sempro', 
                'tahap_3_semhas', 
                'tahap_4_ujian', 
                'selesai_yudisium'
            ])->default('tahap_1_bimbingan');
            
            $table->timestamps();
        });

        // FR-02: Logbook Bimbingan Digital & Digital Approval
        Schema::create('logbook_bimbingans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_tesis_id')->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('users');
            $table->date('tanggal_bimbingan');
            $table->text('materi_bimbingan');
            $table->text('catatan_dosen')->nullable();
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('qr_signature_hash')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_bimbingans');
        Schema::dropIfExists('pengajuan_tesis');
    }
};
