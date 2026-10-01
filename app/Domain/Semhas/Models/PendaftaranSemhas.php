<?php

namespace App\Domain\Semhas\Models;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use Database\Factories\PendaftaranSemhasFactory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranSemhas extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return PendaftaranSemhasFactory::new();
    }

    protected $table = 'pendaftaran_semhas';
    protected $guarded = [];

    protected $casts = [
        'draf_artikel_ilmiah_urls' => 'array',
        'approval_pembimbing_1' => 'boolean',
        'approval_pembimbing_2' => 'boolean',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }
}
