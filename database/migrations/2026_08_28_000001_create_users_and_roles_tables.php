<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('identifier')->unique(); // NIM (Mahasiswa) atau NIP (Dosen/Admin)
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['mahasiswa', 'dosen', 'komisi_tesis', 'admin_prodi', 'kaprodi'])->default('mahasiswa');
            $table->string('bidang_keahlian')->nullable(); // studi atau pendidikan (Khusus Dosen)
            $table->unsignedInteger('kuota_bimbingan_maks')->default(8); // Untuk cek kuota FR-01
            $table->boolean('is_komisi_tesis')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
