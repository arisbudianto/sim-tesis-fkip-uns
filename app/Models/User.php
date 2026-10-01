<?php

namespace App\Models;

use App\Domain\Pembimbing\Models\PengajuanTesis;

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

    /**
     * === Dukungan Multi-Peran (Modul 3) ===
     *
     * Kolom `role` tetap ada sebagai "peran utama" (dipakai untuk badge
     * tampilan & kompatibilitas kode lama), tapi keputusan OTORISASI
     * sesungguhnya bersumber dari tabel pivot `user_roles` di bawah ini,
     * supaya satu user (mis. dosen) bisa punya lebih dari satu peran
     * sekaligus (mis. dosen yang juga menjabat Kaprodi).
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class, 'user_id');
    }

    /** Daftar semua peran user ini sebagai array string, mis. ['dosen','kaprodi']. */
    public function roleList(): array
    {
        $fromPivot = $this->userRoles()->pluck('role')->all();

        // Fallback ke kolom `role` lama kalau baris pivot belum ada sama
        // sekali untuk user ini (mis. race condition sebelum migrasi data
        // jalan) — supaya tidak ada user yang mendadak kehilangan akses.
        if (empty($fromPivot) && $this->role) {
            return [$this->role];
        }

        return $fromPivot;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roleList(), true);
    }

    /** True kalau user punya SALAH SATU dari peran yang diberikan. */
    public function hasAnyRole(array $roles): bool
    {
        return count(array_intersect($roles, $this->roleList())) > 0;
    }

    /** Tambah peran baru ke user ini (idempotent — aman dipanggil berulang). */
    public function assignRole(string $role): void
    {
        $this->userRoles()->firstOrCreate(['role' => $role]);
    }

    public function removeRole(string $role): void
    {
        $this->userRoles()->where('role', $role)->delete();
    }
}
