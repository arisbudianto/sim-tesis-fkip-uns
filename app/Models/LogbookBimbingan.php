<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LogbookBimbingan extends Model
{
    use HasUuids;

    protected $table = 'logbook_bimbingans';
    protected $guarded = [];

    protected $casts = [
        'tanggal_bimbingan' => 'date',
        'approved_at' => 'datetime',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
