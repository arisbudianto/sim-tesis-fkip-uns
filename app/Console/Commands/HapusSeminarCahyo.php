<?php

namespace App\Console\Commands;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HapusSeminarCahyo extends Command
{
    protected $signature = 'simtesis:hapus-seminar-cahyo';
    protected $description = 'Hapus data seminar (pendaftaran + sidang) Cahyo Widodo S162402003, akun tetap ada.';

    public function handle(): int
    {
        $user = User::where('identifier', 'S162402003')->first();
        if (!$user) {
            $this->warn('User S162402003 tidak ditemukan.');
            return self::SUCCESS;
        }

        $tesis = PengajuanTesis::where('mahasiswa_id', $user->id)->first();
        if (!$tesis) {
            $this->info('Tidak ada pengajuan tesis untuk Cahyo.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($tesis) {
            $sidangIds = $tesis->aktivitasSidangs()->pluck('id');

            foreach (['penguji_sidangs', 'manajemen_nilai_sidangs', 'revisi_dokumens'] as $table) {
                if ($sidangIds->isNotEmpty() && Schema::hasTable($table)) {
                    DB::table($table)->whereIn('sidang_id', $sidangIds)->delete();
                }
            }

            if (Schema::hasTable('aktivitas_sidangs')) {
                DB::table('aktivitas_sidangs')->where('pengajuan_tesis_id', $tesis->id)->delete();
            }

            foreach (['pendaftaran_sempros', 'pendaftaran_semhas', 'pendaftaran_ujians'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('pengajuan_tesis_id', $tesis->id)->delete();
                }
            }

            $tesis->update(['status_tahap' => 'tahap_1_bimbingan']);
        });

        $this->info('Data seminar Cahyo Widodo (S162402003) dihapus. Status kembali ke tahap_1_bimbingan.');
        return self::SUCCESS;
    }
}
