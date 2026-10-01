<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pembimbing\PengajuanTesisController;
use App\Http\Controllers\Sempro\PendaftaranSemproController;
use App\Http\Controllers\Semhas\PendaftaranSemhasController;
use App\Http\Controllers\UjianTesis\PendaftaranUjianController;
use App\Http\Controllers\Sidang\KomisiTesisController;
use App\Http\Controllers\Sidang\PenilaianSidangController;
use App\Http\Controllers\UjianTesis\RevisiDokumenController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Notifikasi\WaBlastController;
use App\Http\Controllers\Dokumen\DokumenCetakController;
use App\Http\Controllers\Dokumen\VerifikasiDokumenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PenggunaController;

Route::get('/', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');
Route::post('/pengguna/{user}/reset-password', [PenggunaController::class, 'resetPassword'])
    ->middleware(['auth', 'role:admin_prodi,kaprodi,komisi_tesis'])->name('pengguna.resetPassword');
Route::get('/berkas/{path}', [BerkasController::class, 'show'])->middleware('auth')->where('path', '.*')->name('berkas.show');
Route::post('/notifikasi/wa-blast', [WaBlastController::class, 'blast'])->middleware(['auth', 'role:komisi_tesis,kaprodi,admin_prodi'])->name('notifikasi.waBlast');

// Halaman publik (tanpa login) & panduan SOP 4-tahap
Route::get('/beranda', [DashboardController::class, 'publicPage'])->name('public.index');
Route::get('/panduan', [DashboardController::class, 'panduan'])->name('public.panduan');

// Autentikasi — rate limited (Modul 12, checklist keamanan): 5
// percobaan per menit per kombinasi IP+email, mencegah brute-force
// credential stuffing pada endpoint login/register.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');

    // Reset password (Password broker bawaan Laravel — Modul 3)
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// FR-01: Pengajuan Judul & Penetapan Pembimbing
Route::middleware('auth')->prefix('pengajuan')->name('pengajuan.')->group(function () {
    Route::get('/', [PengajuanTesisController::class, 'index'])->name('index');
    Route::post('/store', [PengajuanTesisController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [PengajuanTesisController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PengajuanTesisController::class, 'update'])->name('update');

    // Laporan histori transisi status (Modul 4) — bisa diaudit siapa pun
    // yang berkepentingan dengan pengajuan tsb (mahasiswa ybs/pembimbing/pengendali),
    // otorisasi kepemilikan dicek di controller via Policy 'view'.
    Route::get('/{id}/histori-status', [PengajuanTesisController::class, 'historiStatus'])->name('historiStatus');

    // Hanya Komisi Tesis/Kaprodi/Admin Prodi yang boleh menghapus & mengalokasikan pembimbing.
    Route::middleware('role:komisi_tesis,kaprodi,admin_prodi')->group(function () {
        Route::delete('/{id}', [PengajuanTesisController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/alokasi-pembimbing', [PengajuanTesisController::class, 'alokasiPembimbing'])->name('alokasi');

        // Modul 5: kepakaran & sisa kuota dosen (dipakai Komisi Tesis
        // sebelum menetapkan pembimbing) — GET /pengajuan/dosen/kuota-tersedia.
        Route::get('/dosen/kuota-tersedia', [PengajuanTesisController::class, 'kuotaTersedia'])->name('kuotaTersedia');
    });

    // Modul 5: status penugasan pembimbing per mahasiswa (lookup by mahasiswa_id) —
    // GET /pengajuan/penugasan-pembimbing/{mahasiswaId}. Otorisasi kepemilikan
    // (mahasiswa ybs/pembimbing/pengendali) dicek di controller via Policy 'view'.
    Route::get('/penugasan-pembimbing/{mahasiswaId}', [PengajuanTesisController::class, 'penugasanPembimbing'])->name('penugasanPembimbing');
});

// Catatan: modul FR-02 (Logbook Bimbingan Digital & Approval Berkala) SENGAJA
// tidak diimplementasikan sesuai cakupan pengembangan — tidak ada route,
// controller, atau model logbook di sistem ini.

// FR-03 & FR-04: Tahap Sempro
Route::middleware('auth')->prefix('sempro')->name('sempro.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranSemproController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranSemproController::class, 'store'])->middleware('throttle:10,1')->name('store');

    Route::middleware('role:komisi_tesis,kaprodi,admin_prodi')->group(function () {
        Route::post('/verifikasi/{id}', [PendaftaranSemproController::class, 'verifikasi'])->name('verifikasi');
        Route::put('/{id}', [PendaftaranSemproController::class, 'update'])->name('update');
        Route::post('/{id}/update', [PendaftaranSemproController::class, 'update'])->name('update.post');
        Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSempro'])->name('plotting');
    });
});

// FR-05 & FR-06: Tahap Semhas
Route::middleware('auth')->prefix('semhas')->name('semhas.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranSemhasController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranSemhasController::class, 'store'])->middleware('throttle:10,1')->name('store');

    // Approval digital naskah oleh Pembimbing 1/2 (Modul 7) — otorisasi
    // kepemilikan slot dicek di controller via Policy 'approveNaskah'.
    Route::middleware('role:dosen,komisi_tesis,kaprodi,admin_prodi')
        ->post('/{id}/approve-naskah', [PendaftaranSemhasController::class, 'approveNaskah'])->name('approveNaskah');

    Route::middleware('role:komisi_tesis,kaprodi,admin_prodi')->group(function () {
        Route::post('/verifikasi/{id}', [PendaftaranSemhasController::class, 'verifikasi'])->name('verifikasi');
        Route::post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingSemhas'])->name('plotting');
    });
});

// FR-07 & FR-08: Tahap Ujian Tesis
Route::middleware('auth')->prefix('ujian')->name('ujian.')->group(function () {
    Route::get('/daftar/{pengajuanId}', [PendaftaranUjianController::class, 'create'])->name('create');
    Route::post('/daftar/{pengajuanId}', [PendaftaranUjianController::class, 'store'])->middleware('throttle:10,1')->name('store');

    // Persetujuan tertulis digital Pembimbing 1/2 (Modul 8) — otorisasi
    // kepemilikan slot dicek di controller via Policy 'approveNaskah'.
    Route::middleware('role:dosen,komisi_tesis,kaprodi,admin_prodi')
        ->post('/{id}/acc-pembimbing', [PendaftaranUjianController::class, 'accPembimbing'])->name('accPembimbing');

    Route::middleware('role:komisi_tesis,kaprodi,admin_prodi')
        ->post('/plotting-jadwal/{pengajuanId}', [KomisiTesisController::class, 'plottingUjian'])->name('plotting');
});

// FR-09: Penilaian Rubrik Digital & BAP — khusus Dewan Penguji (dosen) & pengendali akademik.
Route::middleware(['auth', 'role:dosen,komisi_tesis,kaprodi,admin_prodi'])->prefix('sidang')->name('sidang.')->group(function () {
    Route::get('/{sidangId}/penilaian', [PenilaianSidangController::class, 'showPenilaian'])->name('penilaian');
    Route::post('/{sidangId}/input-nilai', [PenilaianSidangController::class, 'submitNilaiPenguji'])->name('submitNilai');

    Route::middleware('role:komisi_tesis,kaprodi,admin_prodi')
        ->post('/{sidangId}/rekap-komisi', [PenilaianSidangController::class, 'rekapNilaiKomisi'])->name('rekapKomisi');
});

// FR-10: Revisi Dokumen & Gateway Yudisium
Route::middleware('auth')->prefix('revisi')->name('revisi.')->group(function () {
    Route::get('/{sidangId}', [RevisiDokumenController::class, 'index'])->name('index');
    Route::post('/{sidangId}/submit-matriks', [RevisiDokumenController::class, 'submitMatriks'])->name('submitMatriks');

    // ACC per-penguji: hanya dosen (diverifikasi kepemilikan di controller) atau pengendali akademik.
    Route::middleware('role:dosen,komisi_tesis,kaprodi,admin_prodi')
        ->post('/acc-penguji/{revisiPengujiId}', [RevisiDokumenController::class, 'accPenguji'])->name('accPenguji');

    // Pengesahan final hanya wewenang Kaprodi.
    Route::middleware('role:kaprodi')
        ->post('/pengesahan-kaprodi/{revisiId}', [RevisiDokumenController::class, 'pengesahanKaprodi'])->name('pengesahanKaprodi');
});

// Generator PDF & QR TTE: cetak formulir resmi (FPT-TI-01..09, Surat Tugas
// Wadek I, BAP) — perlu login supaya hanya civitas terkait yang bisa mengunduh.
Route::get('/dokumen/cetak/{kode}/{id}', [DokumenCetakController::class, 'show'])->middleware('auth')->name('dokumen.cetak');

// "1-Click Print Bundle" — gabungan seluruh dokumen resmi satu sidang jadi
// satu PDF, khusus pengendali akademik (buat arsip cetak manual prodi).
Route::middleware(['auth', 'role:komisi_tesis,kaprodi,admin_prodi'])
    ->get('/dokumen/bundle/{sidangId}', [DokumenCetakController::class, 'bundle'])->name('dokumen.bundle');

// Sinkronisasi jadwal sidang ke kalender pribadi dosen (Google
// Calendar/Outlook/Apple Calendar) via file .ics — lihat CalendarIcsService.
Route::get('/sidang/{sidangId}/kalender.ics', [DokumenCetakController::class, 'downloadIcs'])->middleware('auth')->name('sidang.kalenderIcs');

// Halaman verifikasi publik hasil scan QR: sengaja TANPA auth, karena
// dokumen resmi bisa diverifikasi siapa saja (pihak eksternal/penguji tamu)
// yang memindai QR Code TTE di kertas cetakan.
Route::get('/verifikasi/{hash}', [VerifikasiDokumenController::class, 'show'])->name('dokumen.verifikasi');
