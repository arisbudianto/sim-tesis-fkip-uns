<?php

namespace App\Domain\Sempro\Models;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use Database\Factories\PendaftaranSemproFactory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranSempro extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return PendaftaranSemproFactory::new();
    }

    protected $table = 'pendaftaran_sempros';
    protected $guarded = [];

    protected $casts = [
        'jadwal_usulan_sidang' => 'datetime',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }
}
