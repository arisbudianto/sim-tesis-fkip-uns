<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ManajemenNilaiSidang extends Model
{
    use HasUuids;

    protected $table = 'manajemen_nilai_sidangs';
    protected $guarded = [];

    public function sidang()
    {
        return $this->belongsTo(AktivitasSidang::class, 'sidang_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'komisi_tesis_validator_id');
    }
}
