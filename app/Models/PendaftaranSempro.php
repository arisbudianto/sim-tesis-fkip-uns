<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PendaftaranSempro extends Model {
    use HasUuids;

    protected $table = 'pendaftaran_sempro';
    protected $guarded = [];

    public function tesis() {
        return $this->belongsTo(PengajuanTesis::class, 'tesis_id');
    }

    public function sidang() {
        return $this->hasOne(SidangSempro::class, 'pendaftaran_sempro_id');
    }
}
