<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengajuanTesisController;
use App\Http\Controllers\LogbookBimbinganController;
use App\Http\Controllers\PendaftaranSemproController;
use App\Http\Controllers\PendaftaranSemhasController;
use App\Http\Controllers\PendaftaranUjianController;
use App\Http\Controllers\KomisiTesisController;
use App\Http\Controllers\PenilaianSidangController;
use App\Http\Controllers\RevisiDokumenController;

// 1. Menu Publik & Landing Page (Bebas Akses)
Route::get('/', [DashboardController::class, 'publicPage'])->name('public.index');
Route::get('/panduan', [DashboardController::class, 'panduan'])->name('public.panduan');

// 2. Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// 3. Auth Routes (Logged In Only)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // FR-01: Pengajuan Judul & Penetapan Pembimbing
    Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
        Route::post('/store', [PengajuanTesisController::class, 'store'])->name('store');
        Route::post('/{id}/alokasi-pembimbing', [PengajuanTesisController::class, 'alokasiPembimbing'])->name('alokasi');
    });

    // FR-02: Logbook Bimbingan
    Route::prefix('logbook')->name('logbook.')->group(function () {
        Route::post('/{pengajuanId}/store', [LogbookBimbinganController::class, 'store'])->name('store');
        Route::post('/approval/{id}', [LogbookBimbinganController::class, 'approve'])->name('approve');
    });

    // FR-03 & FR-04: Tahap Sempro
    Route::prefix('sempro')->name('sempro.')->group(function () {
        Route::post('/daftar/{pengajuanId}', [PendaftaranSemproController::class, 'store'])->name('store');
        Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSempro'])->name('plotting');
    });

    // FR-05 & FR-06: Tahap Semhas
    Route::prefix('semhas')->name('semhas.')->group(function () {
        Route::post('/daftar/{pengajuanId}', [PendaftaranSemhasController::class, 'store'])->name('store');
        Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSemhas'])->name('plotting');
    });

    // FR-07 & FR-08: Tahap Ujian Tesis
    Route::prefix('ujian')->name('ujian.')->group(function () {
        Route::post('/daftar/{pengajuanId}', [PendaftaranUjianController::class, 'store'])->name('store');
        Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingUjian'])->name('plotting');
    });

    // FR-09: Penilaian Rubrik Digital & BAP
    Route::prefix('sidang')->name('sidang.')->group(function () {
        Route::post('/{sidangId}/input-nilai', [PenilaianSidangController::class, 'submitNilaiPenguji'])->name('submitNilai');
        Route::post('/{sidangId}/rekap-komisi', [PenilaianSidangController::class, 'rekapNilaiKomisi'])->name('rekapKomisi');
    });

    // FR-10: Revisi Dokumen & Gateway Yudisium
    Route::prefix('revisi')->name('revisi.')->group(function () {
        Route::post('/{sidangId}/submit-matriks', [RevisiDokumenController::class, 'submitMatriks'])->name('submitMatriks');
        Route::post('/acc-penguji/{revisiPengujiId}', [RevisiDokumenController::class, 'accPenguji'])->name('accPenguji');
        Route::post('/pengesahan-kaprodi/{revisiId}', [RevisiDokumenController::class, 'pengesahanKaprodi'])->name('pengesahanKaprodi');
    });
});
