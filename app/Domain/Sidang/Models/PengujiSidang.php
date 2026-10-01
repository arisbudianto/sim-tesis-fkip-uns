<?php

namespace App\Domain\Sidang\Models;

use App\Models\User;
use Database\Factories\PengujiSidangFactory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengujiSidang extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return PengujiSidangFactory::new();
    }

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
