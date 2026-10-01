<?php

namespace App\Domain\StateEngine\Models;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use Illuminate\Database\Eloquent\Model;

class StateTransitionLog extends Model
{
    // WAJIB eksplisit: migration membuat tabel bernama singular
    // 'state_transition_log', bukan 'state_transition_logs' (plural) yang
    // jadi tebakan default Eloquent dari nama class.
    protected $table = 'state_transition_log';

    public $timestamps = false; // pakai created_at manual (useCurrent di migration)
    protected $guarded = [];

    protected $casts = [
        'is_override' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function pengajuanTesis()
    {
        return $this->belongsTo(PengajuanTesis::class, 'pengajuan_tesis_id');
    }
}
