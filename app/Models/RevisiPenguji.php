<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RevisiPenguji extends Model
{
    use HasUuids;

    protected $table = 'revisi_pengujis';
    protected $guarded = [];

    public function revisiDokumen()
    {
        return $this->belongsTo(RevisiDokumen::class, 'revisi_dokumen_id');
    }

    public function dosenPenguji()
    {
        return $this->belongsTo(User::class, 'dosen_penguji_id');
    }
}
