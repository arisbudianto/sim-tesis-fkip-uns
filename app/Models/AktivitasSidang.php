<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AktivitasSidang extends Model
{
    use HasUuids;

    protected $table = 'aktivitas_sidangs';
    protected $guarded = [];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }

    public function pengujiSidangs()
    {
        return $this->hasMany(PengujiSidang::class, 'sidang_id');
    }

    public function manajemenNilai()
    {
        return $this->hasOne(ManajemenNilaiSidang::class, 'sidang_id');
    }

    public function revisiDokumen()
    {
        return $this->hasOne(RevisiDokumen::class, 'sidang_id');
    }

    public function komisiTesis()
    {
        return $this->belongsTo(User::class, 'komisi_tesis_id');
    }
}
