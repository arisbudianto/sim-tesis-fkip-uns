<?php

namespace App\Domain\UjianTesis\Models;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use Database\Factories\PendaftaranUjianFactory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranUjian extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return PendaftaranUjianFactory::new();
    }

    protected $table = 'pendaftaran_ujians';
    protected $guarded = [];

    protected $casts = [
        'acc_tertulis_pembimbing_1' => 'boolean',
        'acc_tertulis_pembimbing_2' => 'boolean',
        'jadwal_usulan_sidang' => 'datetime',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }
}
