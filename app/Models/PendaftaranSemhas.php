<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PendaftaranSemhas extends Model
{
    use HasUuids;

    protected $table = 'pendaftaran_semhas';
    protected $guarded = [];

    protected $casts = [
        'draf_artikel_ilmiah_urls' => 'array'
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }
}
