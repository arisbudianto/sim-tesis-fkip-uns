<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('actor_id')->nullable();       // siapa: user yang melakukan aksi (nullable utk sistem)
            $table->string('actor_name')->nullable();   // salinan nama, tetap terbaca kalau user dihapus
            $table->string('actor_role')->nullable();   // peran aktor saat aksi dilakukan
            $table->string('action');                   // apa: mis. 'sempro.verifikasi', 'pengajuan.alokasi'
            $table->string('subject_type')->nullable(); // entitas yang kena aksi, mis. 'PendaftaranSempro'
            $table->string('subject_id')->nullable();
            $table->text('description')->nullable();    // ringkasan human-readable
            $table->json('meta')->nullable();            // detail tambahan (payload sebelum/sesudah, dsb)
            $table->timestamp('created_at')->useCurrent(); // kapan

            $table->index(['subject_type', 'subject_id']);
            $table->index('actor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
