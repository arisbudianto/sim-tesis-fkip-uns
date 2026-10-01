<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-03: Pendaftaran Sempro H-14 & Berkas FPT-TI-01 & 02
        Schema::create('pendaftaran_sempros', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_tesis_id')->unique()->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->dateTime('jadwal_usulan_sidang'); // Validasi H-14
            $table->string('naskah_proposal_url');
            $table->string('bukti_spp_url');
            $table->string('khs_url');
            $table->boolean('approval_pembimbing_1')->default(false);
            $table->boolean('approval_pembimbing_2')->default(false);
            $table->enum('status_verifikasi_admin', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });

        // FR-05: Pendaftaran Seminar Hasil Riset & Luaran Publikasi
        Schema::create('pendaftaran_semhas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_tesis_id')->unique()->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->dateTime('jadwal_usulan_sidang'); // Validasi H-14
            $table->string('naskah_bab_1_5_url');
            $table->json('draf_artikel_ilmiah_urls'); // Wajib min. 2 berkas
            $table->string('bukti_status_under_review_url'); // Min. 1 Under Review
            $table->string('bukti_spp_url');
            $table->boolean('approval_pembimbing_1')->default(false);
            $table->boolean('approval_pembimbing_2')->default(false);
            $table->enum('status_verifikasi_admin', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_semhas');
        Schema::dropIfExists('pendaftaran_sempros');
    }
};
