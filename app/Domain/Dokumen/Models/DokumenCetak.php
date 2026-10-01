<?php

namespace App\Domain\Dokumen\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\DokumenCetakFactory;

class DokumenCetak extends Model
{
    use HasUuids, HasFactory;

    protected static function newFactory()
    {
        return DokumenCetakFactory::new();
    }

    protected $fillable = [
        'kode_dokumen',
        'dokumentable_type',
        'dokumentable_id',
        'nomor_dokumen',
        'dicetak_oleh_id',
        'hash_verifikasi',
        'file_path',
        'versi',
        'payload_snapshot',
        'dicetak_at',
    ];

    protected $casts = [
        'payload_snapshot' => 'array',
        'dicetak_at' => 'datetime',
    ];

    public function dokumentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function dicetakOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicetak_oleh_id');
    }
}
