<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PendaftaranSempro extends Model
{
    use HasUuids;

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
