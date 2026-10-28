<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KioskController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MasterTamuController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TamuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VMS ZPA — Visitor Management System PT. Zinkpower Austridno
|--------------------------------------------------------------------------
*/

/* ---------- Root ---------- */
Route::get('/', fn () => redirect()->route('login'));

/* ---------- Kiosk tablet tamu (login akun "Penerima Tamu" / admin) ---------- */
Route::prefix('kiosk')->name('kiosk.')->middleware(['auth', 'kiosk.guard', 'throttle:60,1'])->group(function () {
    Route::get('/', [KioskController::class, 'index'])->name('index');

    // Layar Daftar Tamu
    Route::get('/daftar', [KioskController::class, 'daftar'])->name('daftar');

    // Registrasi tamu baru
    Route::get('/registrasi', [KioskController::class, 'registrasi'])->name('registrasi');
    Route::post('/registrasi', [KioskController::class, 'storeRegistrasi'])
        ->middleware('throttle:10,1')->name('registrasi.store');

    // Sudah pernah daftar
    Route::get('/cari', [KioskController::class, 'cari'])->name('cari');

    // Check-in kunjungan
    Route::get('/checkin/{guest}', [KioskController::class, 'checkinForm'])->name('checkin.form');
    Route::post('/checkin/{guest}', [KioskController::class, 'checkinStore'])
        ->middleware('throttle:10,1')->name('checkin.store');

    // Check-out
    Route::get('/checkout', [KioskController::class, 'checkoutIndex'])->name('checkout');
    Route::post('/checkout/{visit}', [KioskController::class, 'checkoutStore'])
        ->middleware('throttle:10,1')->name('checkout.store');
});

/* ---------- Auth management ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
    Route::get('/captcha', [AuthController::class, 'captcha'])->name('captcha');

    Route::get('/lupa-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'reset'])
        ->middleware('throttle:5,1')->name('password.update');
});

/* ---------- Logout (semua role) ---------- */
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------- Pengaturan profil sendiri (semua role, termasuk penerima kiosk) ---------- */
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.index');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profil.password');
});

/* ---------- Area management (login wajib; akun Penerima Tamu dialihkan ke Kiosk) ---------- */
Route::middleware(['auth', 'role.management'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi aktivitas check-in / check-out (ikon lonceng)
    Route::get('/notifikasi/data', [NotificationController::class, 'index'])->name('notifikasi.data');
    Route::post('/notifikasi/baca', [NotificationController::class, 'markRead'])->name('notifikasi.read');

    // Data Tamu (riwayat kunjungan — check-in/out HANYA dari kiosk)
    Route::get('/data-tamu', [TamuController::class, 'index'])->name('tamu.index');
    Route::get('/data-tamu/{visit}', [TamuController::class, 'show'])->name('tamu.show');
    Route::delete('/data-tamu/{visit}', [TamuController::class, 'destroy'])
        ->middleware('role.admin')->name('tamu.destroy');
    Route::get('/data-tamu-export', [TamuController::class, 'export'])->name('tamu.export');

    // Master Data Tamu (data induk tamu)
    Route::prefix('master-tamu')->name('master-tamu.')->group(function () {
        Route::get('/', [MasterTamuController::class, 'index'])->name('index');
        Route::get('/baru', [MasterTamuController::class, 'create'])->name('create');
        Route::post('/', [MasterTamuController::class, 'store'])->name('store');
        Route::get('/{guest}/edit', [MasterTamuController::class, 'edit'])->name('edit');
        Route::put('/{guest}', [MasterTamuController::class, 'update'])->name('update');
        Route::delete('/{guest}', [MasterTamuController::class, 'destroy'])
            ->middleware('role.admin')->name('destroy');
    });

    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan-export', [LaporanController::class, 'export'])->name('laporan.export');

    // Pengguna (khusus admin)
    Route::prefix('pengguna')->name('pengguna.')->middleware('role.admin')->group(function () {
        Route::get('/', [PenggunaController::class, 'index'])->name('index');
        Route::get('/baru', [PenggunaController::class, 'create'])->name('create');
        Route::post('/', [PenggunaController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [PenggunaController::class, 'edit'])->name('edit');
        Route::put('/{user}', [PenggunaController::class, 'update'])->name('update');
        Route::post('/{user}/toggle', [PenggunaController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{user}', [PenggunaController::class, 'destroy'])->name('destroy');
    });

    // Pengaturan SMTP notifikasi email (khusus admin)
    Route::prefix('pengaturan')->name('pengaturan.')->middleware('role.admin')->group(function () {
        Route::get('/smtp', [PengaturanController::class, 'edit'])->name('smtp');
        Route::post('/smtp', [PengaturanController::class, 'update'])->name('smtp.update');
        Route::post('/smtp/test', [PengaturanController::class, 'test'])->name('smtp.test');
    });
});
