<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PengujiSidang extends Model
{
    use HasUuids;

    protected $table = 'penguji_sidangs';
    protected $guarded = [];

    public function sidang()
    {
        return $this->belongsTo(AktivitasSidang::class, 'sidang_id');
    }

    public function dosen()
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
