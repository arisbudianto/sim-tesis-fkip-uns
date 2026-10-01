<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel pivot untuk mendukung multi-peran per user (mis. seorang dosen
     * yang juga menjabat Kaprodi, atau Komisi Tesis yang juga membimbing).
     * Kolom `users.role` TETAP dipertahankan sebagai "peran utama" (dipakai
     * untuk badge tampilan & kompatibilitas kode lama), tapi keputusan
     * OTORISASI SESUNGGUHNYA sekarang bersumber dari tabel ini via
     * User::hasRole() / hasAnyRole() — lihat app/Models/User.php.
     */
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->enum('role', ['mahasiswa', 'dosen', 'komisi_tesis', 'admin_prodi', 'kaprodi']);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'role']);
        });

        // Migrasi data: setiap user yang sudah ada otomatis dapat baris
        // user_roles sesuai kolom `role` lamanya, supaya tidak ada yang
        // mendadak kehilangan akses saat kode beralih ke pengecekan baru.
        $now = now();
        $rows = DB::table('users')->select('id', 'role')->get();
        foreach ($rows as $row) {
            DB::table('user_roles')->insertOrIgnore([
                'user_id' => $row->id,
                'role' => $row->role,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
