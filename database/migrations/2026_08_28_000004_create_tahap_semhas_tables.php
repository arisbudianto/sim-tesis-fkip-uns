<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Pendaftaran Semhas Riset & Publikasi
        Schema::create('pendaftaran_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tesis_id')->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->text('judul_tesis');
            $table->date('usulan_tanggal');
            $table->time('usulan_waktu');
            $table->string('usulan_tempat');
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_passcode')->nullable();
            
            // Berkas Upload Semhas
            $table->string('naskah_bab1_5_url');
            $table->string('draft_artikel_1_url');
            $table->string('draft_artikel_2_url');
            $table->string('bukti_status_publikasi_url'); // LoA / Under review
            $table->string('bukti_spp_url');
            $table->string('khs_url');
            $table->string('logbook_url');
            
            $table->boolean('approval_pembimbing_utama')->default(false);
            $table->enum('status', ['draft', 'diajukan', 'diverifikasi', 'terjadwal', 'sidang_selesai', 'revisi_approved'])->default('draft');
            $table->timestamps();
        });

        // Sidang Semhas & Penguji
        Schema::create('sidang_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_semhas_id')->constrained('pendaftaran_semhas')->cascadeOnDelete();
            $table->string('nomor_surat_tugas_dekan')->nullable();
            $table->string('nomor_undangan_prodi')->nullable();
            $table->dateTime('jadwal_definitif');
            $table->string('ruangan_atau_link');
            $table->foreignUuid('komisi_tesis_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('penguji_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_semhas_id')->constrained('sidang_semhas')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('users');
            $table->enum('jabatan_tim', ['ketua_penguji', 'sekretaris_penguji', 'anggota_1_pembimbing_1', 'anggota_2_pembimbing_2']);
            $table->timestamps();
        });

        // Penilaian 4 Dimensi & Status Publikasi
        Schema::create('penilaian_semhas_penguji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penguji_semhas_id')->constrained('penguji_semhas')->cascadeOnDelete();
            
            $table->decimal('skor_dimensi_a_draft', 6, 2);
            $table->decimal('skor_dimensi_b_capaian', 6, 2);
            $table->decimal('skor_dimensi_c_tesis', 6, 2);
            $table->decimal('skor_dimensi_d_presentasi', 6, 2);
            $table->decimal('nilai_akhir_penguji', 5, 2);
            
            $table->enum('status_artikel', ['draft', 'submitted', 'under_review', 'accepted', 'published']);
            $table->string('kategori_jurnal')->nullable();
            $table->enum('status_makalah_seminar', ['draft', 'submitted', 'under_review', 'accepted', 'published']);
            $table->string('kategori_seminar')->nullable();
            
            $table->text('komentar_umum')->nullable();
            $table->text('catatan_format_kemajuan')->nullable();
            $table->text('catatan_substansi_pembahasan')->nullable();
            $table->string('qr_signature_penguji')->nullable();
            $table->timestamps();
        });

        // BAP Semhas & Rekomendasi
        Schema::create('bap_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_semhas_id')->constrained('sidang_semhas')->cascadeOnDelete();
            $table->decimal('nilai_rata_rata', 5, 2);
            $table->string('grade_huruf', 5);
            $table->enum('rekomendasi', ['layak_tanpa_revisi_atau_terbatas', 'layak_revisi_menyeluruh', 'belum_layak']);
            $table->integer('deadline_revisi_bulan')->default(1);
            $table->string('bap_pdf_url')->nullable();
            $table->timestamps();
        });

        // Persetujuan Revisi Semhas
        Schema::create('revisi_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_semhas_id')->constrained('sidang_semhas')->cascadeOnDelete();
            $table->text('judul_tesis_revisi');
            $table->text('judul_naskah_publikasi_revisi');
            $table->string('naskah_tesis_revisi_url');
            $table->string('artikel_publikasi_revisi_url');
            $table->boolean('acc_penguji_1')->default(false);
            $table->boolean('acc_penguji_2')->default(false);
            $table->boolean('acc_penguji_3')->default(false);
            $table->boolean('acc_penguji_4')->default(false);
            $table->boolean('disahkan_kaprodi')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('revisi_semhas');
        Schema::dropIfExists('bap_semhas');
        Schema::dropIfExists('penilaian_semhas_penguji');
        Schema::dropIfExists('penguji_semhas');
        Schema::dropIfExists('sidang_semhas');
        Schema::dropIfExists('pendaftaran_semhas');
    }
};
