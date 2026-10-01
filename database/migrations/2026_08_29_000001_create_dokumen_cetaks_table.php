<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log setiap dokumen resmi yang digenerate sistem (FPT-TI-01 s.d. 09,
     * Surat Tugas Wadek I, Berita Acara Sidang/BAP, dst).
     *
     * Setiap kali dokumen dicetak, satu baris tercatat di sini dengan
     * hash_verifikasi unik yang dijadikan payload QR code di pojok dokumen
     * (QR TTE). Siapa pun bisa scan QR itu -> dibuka ke
     * /verifikasi/{hash} -> sistem tampilkan info keaslian dokumen
     * (nomor, kode, kapan & oleh siapa dicetak) tanpa perlu login.
     */
    public function up(): void
    {
        Schema::create('dokumen_cetaks', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Kode dokumen: FPT-TI-01 .. FPT-TI-09, SURAT-TUGAS-WADEK1, BAP
            $table->string('kode_dokumen');

            // Rujukan polymorphic ke record sumber data
            // (AktivitasSidang, RevisiDokumen, atau ManajemenNilaiSidang)
            $table->uuidMorphs('dokumentable');

            // Nomor surat resmi, kalau ada (mis. nomor_surat_tugas dari aktivitas_sidangs)
            $table->string('nomor_dokumen')->nullable();

            $table->foreignUuid('dicetak_oleh_id')->nullable()->constrained('users')->nullOnDelete();

            // Hash unik yang di-embed sebagai QR code -> dasar verifikasi TTE
            $table->string('hash_verifikasi', 64)->unique();

            // Snapshot ringkas data saat dicetak, untuk jejak audit
            // (kalau data sumber berubah di kemudian hari, snapshot ini tetap
            // merekam apa yang tertulis di dokumen versi itu)
            $table->json('payload_snapshot')->nullable();

            $table->timestamp('dicetak_at');
            $table->timestamps();

            $table->index(['kode_dokumen', 'dicetak_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_cetaks');
    }
};
