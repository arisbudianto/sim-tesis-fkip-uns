<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RevisiDokumen extends Model
{
    use HasUuids;

    protected $table = 'revisi_dokumens';
    protected $guarded = [];

    public function sidang()
    {
        return $this->belongsTo(AktivitasSidang::class, 'sidang_id');
    }

    public function revisiPengujis()
    {
        return $this->hasMany(RevisiPenguji::class, 'revisi_dokumen_id');
    }
}
