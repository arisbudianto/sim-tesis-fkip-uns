<?php

namespace App\Providers;

use App\Domain\Pembimbing\Models\PengajuanTesis;
use App\Domain\Sidang\Models\AktivitasSidang;
use App\Domain\UjianTesis\Models\RevisiPenguji;
use App\Policies\PenugasanPembimbingPolicy;
use App\Policies\RevisiPengujiPolicy;
use App\Policies\SidangPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Peta Policy (Modul 3 — RBAC berbasis Gate & Policy).
     * Proyek ini sebelumnya TIDAK punya service provider sama sekali —
     * ini yang pertama, sekaligus jadi tempat pusat pendaftaran otorisasi.
     */
    protected $policies = [
        PengajuanTesis::class => PenugasanPembimbingPolicy::class,
        AktivitasSidang::class => SidangPolicy::class,
        RevisiPenguji::class => RevisiPengujiPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
