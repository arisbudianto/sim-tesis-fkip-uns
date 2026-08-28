<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aktivitas Sidang (Sempro, Semhas, Ujian) - FR-04, FR-06, FR-08
        Schema::create('aktivitas_sidangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_tesis_id')->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->enum('tahap_sidang', ['sempro', 'semhas', 'ujian']);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->string('ruangan')->nullable();
            $table->string('link_zoom')->nullable();
            $table->foreignUuid('komisi_tesis_id')->constrained('users'); // Pengatur Sidang
            $table->string('nomor_surat_tugas')->nullable(); 
            $table->string('nomor_undangan')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            // Anti-Conflict Constraint Ruangan
            $table->unique(['ruangan', 'waktu_mulai'], 'unique_ruangan_sidang_waktu');
        });

        // Plotting Dewan Penguji Sidang (FR-04, FR-08)
        Schema::create('penguji_sidangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_id')->constrained('aktivitas_sidangs')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('users');
            
            // Peran: ketua_penguji, sekretaris_penguji, penguji_studi, penguji_pendidikan, pembimbing_1, pembimbing_2
            $table->string('peran_penguji'); 
            $table->boolean('presensi_kehadiran')->default(false);
            
            // FR-09: Penilaian Rubrik 4 Dimensi & Nilai Individu (0-100)
            $table->decimal('nilai_dimensi_1_naskah', 5, 2)->nullable();
            $table->decimal('nilai_dimensi_2_publikasi', 5, 2)->nullable();
            $table->decimal('nilai_dimensi_3_presentasi', 5, 2)->nullable();
            $table->decimal('nilai_dimensi_4_tanyajawab', 5, 2)->nullable();
            $table->decimal('nilai_total_angka', 5, 2)->nullable();
            $table->text('catatan_revisi')->nullable();
            $table->string('qr_signature_hash')->nullable();
            $table->timestamps();

            $table->unique(['sidang_id', 'dosen_id'], 'unique_penguji_per_sidang');
        });

        // FR-09: Manajemen Nilai Komisi, Konversi Grade & BAP
        Schema::create('manajemen_nilai_sidangs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_id')->unique()->constrained('aktivitas_sidangs')->cascadeOnDelete();
            $table->foreignUuid('komisi_tesis_validator_id')->constrained('users');
            $table->decimal('nilai_rata_rata', 5, 2);
            $table->enum('grade_kelulusan', ['A', 'A-', 'B+', 'B', 'C+', 'TIDAK_LULUS']);
            $table->enum('keputusan_sidang', ['lulus_tanpa_revisi', 'lulus_revisi_ringan', 'lulus_revisi_berat', 'ujian_ulang']);
            $table->date('batas_waktu_revisi')->nullable();
            $table->string('bap_pdf_url')->nullable();
            $table->string('qr_bap_hash')->nullable();
            $table->timestamps();
        });

        // FR-10: Matriks Revisi Pasca Ujian & Unlock Yudisium
        Schema::create('revisi_dokumens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_id')->unique()->constrained('aktivitas_sidangs')->cascadeOnDelete();
            $table->string('naskah_revisi_final_url');
            $table->string('bukti_luaran_final_url')->nullable();
            $table->boolean('status_approval_semua')->default(false);
            $table->boolean('pengesahan_kaprodi')->default(false);
            $table->timestamp('disahkan_kaprodi_at')->nullable();
            $table->timestamps();
        });

        // FR-10: Detail Matriks Komparasi 4 Penguji
        Schema::create('revisi_pengujis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('revisi_dokumen_id')->constrained('revisi_dokumens')->cascadeOnDelete();
            $table->foreignUuid('dosen_penguji_id')->constrained('users');
            $table->text('uraian_hasil_perbaikan');
            $table->string('bukti_halaman_perbaikan');
            $table->enum('status_acc', ['pending', 'acc', 'perlu_perbaikan_lagi'])->default('pending');
            $table->text('feedback_penguji')->nullable();
            $table->timestamp('acc_at')->nullable();
            $table->timestamps();

            $table->unique(['revisi_dokumen_id', 'dosen_penguji_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisi_pengujis');
        Schema::dropIfExists('revisi_dokumens');
        Schema::dropIfExists('manajemen_nilai_sidangs');
        Schema::dropIfExists('penguji_sidangs');
        Schema::dropIfExists('aktivitas_sidangs');
    }
};
