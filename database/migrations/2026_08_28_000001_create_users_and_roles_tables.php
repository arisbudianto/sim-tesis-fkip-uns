<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['mahasiswa', 'dosen', 'komisi_tesis', 'admin_prodi', 'kaprodi', 'dekanat'])->default('mahasiswa');
            $table->string('phone_number')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('mahasiswa_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nim', 30)->unique();
            $table->string('program_studi')->default('Magister Pendidikan Guru Vokasi');
            $table->string('angkatan', 10);
            $table->timestamps();
        });

        Schema::create('dosen_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nip', 30)->unique();
            $table->string('pangkat_golongan')->nullable();
            $table->enum('bidang_keahlian', ['bidang_studi', 'bidang_pendidikan', 'umum'])->default('umum');
            $table->boolean('is_komisi_tesis')->default(false);
            $table->integer('kuota_bimbingan_max')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('dosen_profiles');
        Schema::dropIfExists('mahasiswa_profiles');
        Schema::dropIfExists('users');
    }
};
