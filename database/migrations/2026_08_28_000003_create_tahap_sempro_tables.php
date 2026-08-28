<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // FPT-TI-01 & FPT-TI-02 Permohonan & Syarat Sempro
        Schema::create('pendaftaran_sempro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tesis_id')->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->text('judul_proposal');
            $table->date('usulan_tanggal');
            $table->time('usulan_waktu');
            $table->string('usulan_tempat');
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_passcode')->nullable();
            
            // Berkas Upload FPT-TI-02
            $table->string('naskah_proposal_url');
            $table->string('bukti_spp_url');
            $table->string('khs_url');
            $table->string('logbook_url');
            
            // Dual Approval Pembimbing
            $table->boolean('approval_pembimbing_1')->default(false);
            $table->boolean('approval_pembimbing_2')->default(false);
            $table->string('qr_approval_pembimbing_1')->nullable();
            $table->string('qr_approval_pembimbing_2')->nullable();
            
            $table->enum('status', ['draft', 'diajukan', 'diverifikasi', 'terjadwal', 'sidang_selesai', 'revisi_approved'])->default('draft');
            $table->timestamps();
        });

        // Aktivitas Sidang & Surat Tugas/Undangan
        Schema::create('sidang_sempro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_sempro_id')->constrained('pendaftaran_sempro')->cascadeOnDelete();
            $table->string('nomor_surat_tugas_dekan')->nullable();
            $table->string('nomor_undangan_prodi')->nullable();
            $table->dateTime('jadwal_definitif');
            $table->string('ruangan_atau_link');
            $table->foreignUuid('komisi_tesis_id')->constrained('users');
            $table->timestamps();
        });

        // 4 Dewan Penguji Sempro
        Schema::create('penguji_sempro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_sempro_id')->constrained('sidang_sempro')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('users');
            $table->enum('jabatan_tim', ['ketua_penguji', 'sekretaris_penguji', 'anggota_1_pembimbing_1', 'anggota_2_pembimbing_2']);
            $table->timestamps();
        });

        // FPT-TI-03 & FPT-TI-04: Penilaian Rubrik & Catatan Penguji
        Schema::create('penilaian_sempro_penguji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penguji_sempro_id')->constrained('penguji_sempro')->cascadeOnDelete();
            
            // 10 Indikator (0-100)
            $table->decimal('skor_bahasa', 5, 2);
            $table->decimal('skor_sistematika', 5, 2);
            $table->decimal('skor_rumusan_masalah', 5, 2);
            $table->decimal('skor_kajian_teori', 5, 2);
            $table->decimal('skor_metodologi', 5, 2);
            $table->decimal('skor_kebaruan', 5, 2);
            $table->decimal('skor_kemanfaatan', 5, 2);
            $table->decimal('skor_presentasi', 5, 2);
            $table->decimal('skor_tanya_jawab', 5, 2);
            $table->decimal('skor_wawasan', 5, 2);
            $table->decimal('nilai_akhir_individu', 5, 2);
            
            // FPT-TI-04 Catatan
            $table->text('komentar_umum')->nullable();
            $table->text('catatan_format_bahasa')->nullable();
            $table->text('catatan_substansi')->nullable();
            $table->string('qr_signature_penguji')->nullable();
            $table->timestamps();
        });

        // FPT-TI-05 & FPT-TI-06: Rekap & Rekomendasi
        Schema::create('bap_sempro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_sempro_id')->constrained('sidang_sempro')->cascadeOnDelete();
            $table->decimal('nilai_rata_rata', 5, 2);
            $table->string('grade_huruf', 5);
            $table->enum('rekomendasi', ['lulus_tanpa_revisi', 'lulus_dengan_revisi', 'tidak_lulus']);
            $table->integer('deadline_revisi_bulan')->default(1);
            $table->string('bap_pdf_url')->nullable();
            $table->timestamps();
        });

        // FPT-TI-09 / Form Revisi Sempro
        Schema::create('revisi_sempro', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_sempro_id')->constrained('sidang_sempro')->cascadeOnDelete();
            $table->text('judul_naskah_publikasi');
            $table->string('naskah_revisi_url');
            $table->string('draft_publikasi_url');
            $table->boolean('acc_penguji_1')->default(false);
            $table->boolean('acc_penguji_2')->default(false);
            $table->boolean('acc_penguji_3')->default(false);
            $table->boolean('acc_penguji_4')->default(false);
            $table->boolean('disahkan_kaprodi')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('revisi_sempro');
        Schema::dropIfExists('bap_sempro');
        Schema::dropIfExists('penilaian_sempro_penguji');
        Schema::dropIfExists('penguji_sempro');
        Schema::dropIfExists('sidang_sempro');
        Schema::dropIfExists('pendaftaran_sempro');
    }
};
