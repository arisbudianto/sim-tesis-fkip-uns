<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function pengajuanTesisMahasiswa()
    {
        return $this->hasOne(PengajuanTesis::class, 'mahasiswa_id');
    }

    public function bimbinganUtama()
    {
        return $this->hasMany(PengajuanTesis::class, 'pembimbing_1_id');
    }

    public function bimbinganPendamping()
    {
        return $this->hasMany(PengajuanTesis::class, 'pembimbing_2_id');
    }
}
