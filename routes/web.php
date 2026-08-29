<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengajuanTesisController;
use App\Http\Controllers\LogbookBimbinganController;
use App\Http\Controllers\PendaftaranSemproController;
use App\Http\Controllers\PendaftaranSemhasController;
use App\Http\Controllers\PendaftaranUjianController;
use App\Http\Controllers\KomisiTesisController;
use App\Http\Controllers\PenilaianSidangController;
use App\Http\Controllers\RevisiDokumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DokumenCetakController;
use App\Http\Controllers\VerifikasiDokumenController;
use App\Http\Controllers\AuthController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Halaman publik (tanpa login) & panduan SOP 4-tahap
Route::get('/beranda', [DashboardController::class, 'publicPage'])->name('public.index');
Route::get('/panduan', [DashboardController::class, 'panduan'])->name('public.panduan');

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// FR-01: Pengajuan Judul & Penetapan Pembimbing
Route::prefix('pengajuan')->name('pengajuan.')->group(function () {
    Route::get('/', [PengajuanTesisController::class, 'index'])->name('index');
    Route::post('/store', [PengajuanTesisController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PengajuanTesisController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PengajuanTesisController::class, 'update'])->name('update');
    Route::delete('/{id}', [PengajuanTesisController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/alokasi-pembimbing', [PengajuanTesisController::class, 'alokasiPembimbing'])->name('alokasi');
});

// FR-02: Logbook Bimbingan
Route::prefix('logbook')->name('logbook.')->group(function () {
    Route::get('/{pengajuanId}', [LogbookBimbinganController::class, 'index'])->name('index');
    Route::post('/{pengajuanId}/store', [LogbookBimbinganController::class, 'store'])->name('store');
    Route::post('/approval/{id}', [LogbookBimbinganController::class, 'approve'])->name('approve');
});

// FR-03 & FR-04: Tahap Sempro
Route::prefix('sempro')->name('sempro.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranSemproController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranSemproController::class, 'store'])->name('store');
    Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSempro'])->name('plotting');
});

// FR-05 & FR-06: Tahap Semhas
Route::prefix('semhas')->name('semhas.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranSemhasController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranSemhasController::class, 'store'])->name('store');
    Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSemhas'])->name('plotting');
});

// FR-07 & FR-08: Tahap Ujian Tesis
Route::prefix('ujian')->name('ujian.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranUjianController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranUjianController::class, 'store'])->name('store');
    Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingUjian'])->name('plotting');
});

// FR-09: Penilaian Rubrik Digital & BAP
Route::prefix('sidang')->name('sidang.')->group(function () {
    Route::get('/{sidangId}/penilaian', [PenilaianSidangController::class, 'showPenilaian'])->name('penilaian');
    Route::post('/{sidangId}/input-nilai', [PenilaianSidangController::class, 'submitNilaiPenguji'])->name('submitNilai');
    Route::post('/{sidangId}/rekap-komisi', [PenilaianSidangController::class, 'rekapNilaiKomisi'])->name('rekapKomisi');
});

// FR-10: Revisi Dokumen & Gateway Yudisium
Route::prefix('revisi')->name('revisi.')->group(function () {
    Route::get('/{sidangId}', [RevisiDokumenController::class, 'index'])->name('index');
    Route::post('/{sidangId}/submit-matriks', [RevisiDokumenController::class, 'submitMatriks'])->name('submitMatriks');
    Route::post('/acc-penguji/{revisiPengujiId}', [RevisiDokumenController::class, 'accPenguji'])->name('accPenguji');
    Route::post('/pengesahan-kaprodi/{revisiId}', [RevisiDokumenController::class, 'pengesahanKaprodi'])->name('pengesahanKaprodi');
});

// Generator PDF & QR TTE: cetak formulir resmi (FPT-TI-01..09, Surat Tugas
// Wadek I, BAP) dan halaman verifikasi publik hasil scan QR.
Route::get('/dokumen/cetak/{kode}/{id}', [DokumenCetakController::class, 'show'])->name('dokumen.cetak');
Route::get('/verifikasi/{hash}', [VerifikasiDokumenController::class, 'show'])->name('dokumen.verifikasi');
