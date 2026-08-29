<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DokumenCetak extends Model
{
    use HasUuids;

    protected $fillable = [
        'kode_dokumen',
        'dokumentable_type',
        'dokumentable_id',
        'nomor_dokumen',
        'dicetak_oleh_id',
        'hash_verifikasi',
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
