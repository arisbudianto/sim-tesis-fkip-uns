<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Template pesan WA disimpan di DB (bukan hardcode string di kode PHP)
     * — acceptance criteria eksplisit: "Template pesan WA dapat
     * dikonfigurasi tanpa redeploy". Admin Prodi/Komisi Tesis bisa
     * mengubah isi pesan lewat halaman pengaturan tanpa sentuh kode.
     *
     * Placeholder ditulis dengan format {nama_variabel} di kolom `body`,
     * diganti runtime oleh NotifikasiService sebelum dikirim.
     */
    public function up(): void
    {
        Schema::create('notifikasi_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // mis. 'undangan_menguji', 'approval_pembimbing'
            $table->string('nama_template');  // label ramah-baca untuk UI pengaturan
            $table->string('deskripsi_placeholder')->nullable(); // dokumentasi placeholder yang tersedia
            $table->text('body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_templates');
    }
};
