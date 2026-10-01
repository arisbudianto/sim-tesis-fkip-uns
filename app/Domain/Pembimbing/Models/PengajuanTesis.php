<?php

namespace App\Domain\Pembimbing\Models;

use App\Domain\Semhas\Models\PendaftaranSemhas;
use App\Domain\Sempro\Models\PendaftaranSempro;
use App\Domain\UjianTesis\Models\PendaftaranUjian;
use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\PengajuanTesisFactory;
use App\Domain\Sidang\Models\AktivitasSidang;

class PengajuanTesis extends Model
{
    use HasUuids, HasFactory;

    /**
     * Override wajib: konvensi auto-resolve factory bawaan Laravel menghitung
     * nama class dari namespace PENUH model (App\Domain\Pembimbing\Models\...
     * -> Database\Factories\Domain\Pembimbing\Models\...Factory), padahal
     * struktur folder modular Modul 1 menaruh semua factory flat langsung di
     * database/factories/. Tanpa override ini, HasFactory::newFactory() akan
     * mencari class yang salah dan gagal ("Class ... not found").
     */
    protected static function newFactory()
    {
        return PengajuanTesisFactory::new();
    }

    protected $table = 'pengajuan_tesis';
    protected $guarded = [];

    protected $casts = [
        'tanggal_sk_pembimbing' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function pembimbing1()
    {
        return $this->belongsTo(User::class, 'pembimbing_1_id');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(User::class, 'pembimbing_2_id');
    }

    /** Calon Pembimbing 1 yang diusulkan mahasiswa (belum resmi). */
    public function usulanPembimbing1()
    {
        return $this->belongsTo(User::class, 'usulan_pembimbing_1_id');
    }

    /** Calon Pembimbing 2 yang diusulkan mahasiswa (belum resmi). */
    public function usulanPembimbing2()
    {
        return $this->belongsTo(User::class, 'usulan_pembimbing_2_id');
    }

    public function pendaftaranSempro()
    {
        return $this->hasOne(PendaftaranSempro::class, 'pengajuan_tesis_id');
    }

    public function pendaftaranSemhas()
    {
        return $this->hasOne(PendaftaranSemhas::class, 'pengajuan_tesis_id');
    }

    public function pendaftaranUjian()
    {
        return $this->hasOne(PendaftaranUjian::class, 'pengajuan_tesis_id');
    }

    public function aktivitasSidangs()
    {
        return $this->hasMany(AktivitasSidang::class, 'pengajuan_tesis_id');
    }

    /** Histori lengkap transisi status_tahap mahasiswa ini (Modul 4). */
    public function stateTransitionLogs()
    {
        return $this->hasMany(\App\Domain\StateEngine\Models\StateTransitionLog::class, 'pengajuan_tesis_id')
            ->orderByDesc('created_at');
    }
}
