<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PengajuanTesis extends Model
{
    use HasUuids;

    protected $table = 'pengajuan_tesis';
    protected $guarded = [];

    protected $casts = [
        'tanggal_sk_pembimbing' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function pembimbing1()
    {
        return $this->belongsTo(User::class, 'pembimbing_1_id');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(User::class, 'pembimbing_2_id');
    }

    public function logbooks()
    {
        return $this->hasMany(LogbookBimbingan::class, 'pengajuan_tesis_id');
    }

    public function pendaftaranSempro()
    {
        return $this->hasOne(PendaftaranSempro::class, 'pengajuan_tesis_id');
    }

    public function pendaftaranSemhas()
    {
        return $this->hasOne(PendaftaranSemhas::class, 'pengajuan_tesis_id');
    }

    public function pendaftaranUjian()
    {
        return $this->hasOne(PendaftaranUjian::class, 'pengajuan_tesis_id');
    }

    public function aktivitasSidangs()
    {
        return $this->hasMany(AktivitasSidang::class, 'pengajuan_tesis_id');
    }
}
