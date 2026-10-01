<?php

namespace App\Domain\Notifikasi\Models;

use Illuminate\Database\Eloquent\Model;

class NotifikasiLog extends Model
{
    protected $table = 'notifikasi_log';
    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'terkirim_at' => 'datetime',
    ];
}
