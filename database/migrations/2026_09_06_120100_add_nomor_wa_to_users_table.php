<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nomor WhatsApp aktif untuk notifikasi blast (undangan sidang,
            // status persetujuan, dsb). Format bebas — dinormalisasi ke E.164
            // (62xxxxxxxxxx) di WhatsAppNotifierService sebelum dikirim.
            $table->string('nomor_wa')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nomor_wa');
        });
    }
};
