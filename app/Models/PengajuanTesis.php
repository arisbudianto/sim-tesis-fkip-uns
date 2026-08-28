<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanTesis extends Model {
    use HasFactory, HasUuids;

    protected $table = 'pengajuan_tesis';
    protected $guarded = [];

    public function mahasiswa() {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function pembimbing1() {
        return $this->belongsTo(User::class, 'pembimbing_1_id');
    }

    public function pembimbing2() {
        return $this->belongsTo(User::class, 'pembimbing_2_id');
    }

    public function pendaftaranSempro() {
        return $this->hasOne(PendaftaranSempro::class, 'tesis_id');
    }

    public function pendaftaranSemhas() {
        return $this->hasOne(PendaftaranSemhas::class, 'tesis_id');
    }

    public function pendaftaranUjianTesis() {
        return $this->hasOne(PendaftaranUjianTesis::class, 'tesis_id');
    }
}
