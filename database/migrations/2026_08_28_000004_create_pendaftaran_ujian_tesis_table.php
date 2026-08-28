<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // FR-07: Pendaftaran Ujian Tesis H-14, ACC 2 Pembimbing & 9 Dokumen Prasyarat
        Schema::create('pendaftaran_ujians', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pengajuan_tesis_id')->unique()->constrained('pengajuan_tesis')->cascadeOnDelete();
            $table->dateTime('jadwal_usulan_sidang'); // Wajib >= H+14
            
            // 9 Checklist Dokumen Prasyarat
            $table->string('naskah_tesis_lengkap_url'); // 1. Naskah Utuh
            $table->string('bukti_lulus_semhas_url')->nullable(); // 2. ACC Semhas
            $table->string('artikel_jurnal_url');       // 3. Sinta 1/2 / Scopus
            $table->string('prosiding_seminar_url');    // 4. Prosiding Int + Sertifikat
            $table->string('sertifikat_bahasa_url');    // 5. Sertifikat EAP / TOEFL
            $table->unsignedSmallInteger('skor_bahasa'); // Constraint: EAP >= 65 / TOEFL >= 475
            $table->string('bukti_spp_terakhir_url');   // 6. SPP Terakhir
            $table->string('khs_kumulatif_url');        // 8. KHS Kumulatif SKS
            $table->string('surat_bebas_plagiasi_url'); // 9. Surat Uji Kemiripan
            $table->decimal('similarity_score', 5, 2);   // Hard constraint: <= 25.00%
            
            $table->boolean('acc_tertulis_pembimbing_1')->default(false);
            $table->boolean('acc_tertulis_pembimbing_2')->default(false);
            $table->enum('status_verifikasi_admin', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_ujians');
    }
};
