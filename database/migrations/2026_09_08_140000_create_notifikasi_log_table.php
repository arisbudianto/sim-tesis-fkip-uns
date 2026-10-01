<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_log', function (Blueprint $table) {
            $table->id();
            $table->uuid('penerima_id')->nullable();          // FK longgar ke users.id (nullable, penerima mungkin bukan user terdaftar)
            $table->string('penerima_nama')->nullable();
            $table->string('nomor_tujuan')->nullable();
            $table->enum('channel', ['whatsapp', 'email'])->default('whatsapp');
            $table->string('template_key');                    // referensi ke notifikasi_templates.key
            $table->text('pesan_terkirim');                    // isi pesan FINAL setelah placeholder diganti (snapshot)
            $table->enum('status', ['pending', 'terkirim', 'gagal'])->default('pending');
            $table->unsignedTinyInteger('percobaan_ke')->default(1);
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();                  // payload tambahan (mis. sidang_id, tahap, dsb)
            $table->timestamp('terkirim_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'percobaan_ke']);
            $table->index('template_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_log');
    }
};
