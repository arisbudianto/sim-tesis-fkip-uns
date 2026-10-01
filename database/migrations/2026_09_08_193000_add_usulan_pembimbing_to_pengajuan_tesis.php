<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FR-01 perluasan: mahasiswa mengusulkan 2 calon pembimbing + unggah
 * formulir FPT-TI-00 (Permohonan Proposal dan Pembimbing). Komisi Tesis
 * dapat menyetujui atau menolak usulan tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_tesis', function (Blueprint $table) {
            $table->foreignUuid('usulan_pembimbing_1_id')
                ->nullable()
                ->after('abstrak_rencana')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignUuid('usulan_pembimbing_2_id')
                ->nullable()
                ->after('usulan_pembimbing_1_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->string('form_fpt_ti_00_url')->nullable()->after('usulan_pembimbing_2_id');

            $table->enum('status_usulan_pembimbing', ['pending', 'approved', 'rejected'])
                ->nullable()
                ->after('form_fpt_ti_00_url');

            $table->text('catatan_komisi_usulan')->nullable()->after('status_usulan_pembimbing');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_tesis', function (Blueprint $table) {
            $table->dropForeign(['usulan_pembimbing_1_id']);
            $table->dropForeign(['usulan_pembimbing_2_id']);
            $table->dropColumn([
                'usulan_pembimbing_1_id',
                'usulan_pembimbing_2_id',
                'form_fpt_ti_00_url',
                'status_usulan_pembimbing',
                'catatan_komisi_usulan',
            ]);
        });
    }
};
