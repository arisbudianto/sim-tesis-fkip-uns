<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Pendaftaran Ujian Tesis (9 Syarat Administrasi)
        Schema::create('pendaftaran_ujian_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tesis_id')->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->text('judul_tesis');
            $table->date('usulan_tanggal');
            $table->time('usulan_waktu');
            $table->string('usulan_tempat');
            $table->string('zoom_meeting_id')->nullable();
            $table->string('zoom_passcode')->nullable();
            
            // 9 Dokumen Prasyarat
            $table->string('naskah_tesis_lengkap_url');
            $table->string('bukti_lulus_semhas_url');
            $table->string('bukti_jurnal_url');
            $table->string('bukti_prosiding_sertifikat_url');
            $table->string('bukti_sertifikat_bahasa_url');
            $table->integer('skor_bahasa'); // EAP >= 65 / TOEFL >= 475
            $table->enum('jenis_bahasa', ['EAP', 'TOEFL_ITP', 'TOEFL_IBT', 'IELTS']);
            $table->string('bukti_spp_url');
            $table->string('logbook_url');
            $table->string('khs_url');
            $table->string('bukti_bebas_plagiasi_url');
            $table->decimal('persentase_similarity', 4, 2); // <= 25%
            
            $table->boolean('approval_pembimbing_utama')->default(false);
            $table->boolean('approval_pembimbing_pendamping')->default(false);
            $table->enum('status', ['draft', 'diajukan', 'diverifikasi', 'terjadwal', 'sidang_selesai', 'lulus_wisuda'])->default('draft');
            $table->timestamps();
        });

        // Sidang Ujian Tesis & Penguji
        Schema::create('sidang_ujian_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_ujian_id')->constrained('pendaftaran_ujian_tesis')->cascadeOnDelete();
            $table->string('nomor_surat_tugas_dekan')->nullable();
            $table->string('nomor_undangan_prodi')->nullable();
            $table->dateTime('jadwal_definitif');
            $table->string('ruangan_atau_link');
            $table->foreignUuid('komisi_tesis_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('penguji_ujian_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_ujian_id')->constrained('sidang_ujian_tesis')->cascadeOnDelete();
            $table->foreignUuid('dosen_id')->constrained('users');
            $table->enum('jabatan_tim', ['ketua_penguji', 'sekretaris_penguji', 'anggota_1_pembimbing_1', 'anggota_2_pembimbing_2']);
            $table->timestamps();
        });

        // Penilaian 4 Dimensi Ujian Tesis
        Schema::create('penilaian_ujian_tesis_penguji', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('penguji_ujian_id')->constrained('penguji_ujian_tesis')->cascadeOnDelete();
            
            $table->decimal('skor_naskah_tesis', 5, 2);
            $table->decimal('skor_karya_publikasi', 5, 2);
            $table->decimal('skor_kualitas_presentasi', 5, 2);
            $table->decimal('skor_tanya_jawab_wawasan', 5, 2);
            $table->decimal('nilai_akhir_penguji', 5, 2);
            
            $table->text('komentar_umum')->nullable();
            $table->text('catatan_format_bahasa')->nullable();
            $table->text('catatan_substansi_pembahasan')->nullable();
            $table->string('qr_signature_penguji')->nullable();
            $table->timestamps();
        });

        // BAP Ujian Tesis
        Schema::create('bap_ujian_tesis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_ujian_id')->constrained('sidang_ujian_tesis')->cascadeOnDelete();
            $table->decimal('nilai_rata_rata', 5, 2);
            $table->string('grade_kelulusan', 5);
            $table->enum('status_kelulusan', ['lulus_tanpa_revisi', 'lulus_dengan_revisi', 'tidak_lulus_mengulang']);
            $table->integer('deadline_revisi_bulan')->default(1);
            $table->string('bap_pdf_url')->nullable();
            $table->timestamps();
        });

        // Persetujuan Revisi Akhir Menuju Wisuda
        Schema::create('revisi_ujian_tesis_final', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sidang_ujian_id')->constrained('sidang_ujian_tesis')->cascadeOnDelete();
            $table->text('judul_tesis_final');
            $table->string('naskah_tesis_final_pdf_url');
            $table->string('artikel_publikasi_final_pdf_url');
            $table->boolean('acc_penguji_1')->default(false);
            $table->boolean('acc_penguji_2')->default(false);
            $table->boolean('acc_penguji_3')->default(false);
            $table->boolean('acc_penguji_4')->default(false);
            $table->boolean('disahkan_kaprodi_wisuda')->default(false);
            $table->string('lembar_persetujuan_revisi_pdf_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('revisi_ujian_tesis_final');
        Schema::dropIfExists('bap_ujian_tesis');
        Schema::dropIfExists('penilaian_ujian_tesis_penguji');
        Schema::dropIfExists('penguji_ujian_tesis');
        Schema::dropIfExists('sidang_ujian_tesis');
        Schema::dropIfExists('pendaftaran_ujian_tesis');
    }
};
