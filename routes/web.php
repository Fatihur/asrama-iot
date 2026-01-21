<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\KameraController;
use App\Http\Controllers\DistribusiController;
use App\Http\Controllers\SirineController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Routes (redirect to dashboard or login)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/sse', [DashboardController::class, 'sse'])->name('dashboard.sse');

    // Riwayat
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/{riwayat}', [RiwayatController::class, 'show'])->name('riwayat.show');

    // Kontak
    Route::resource('kontak', KontakController::class);

    // Kamera
    Route::get('/kamera', [KameraController::class, 'index'])->name('kamera.index');
    Route::get('/kamera/{kamera}', [KameraController::class, 'show'])->name('kamera.show');

    // Distribusi
    Route::get('/distribusi', [DistribusiController::class, 'index'])->name('distribusi.index');
    Route::get('/distribusi/{distribusi}', [DistribusiController::class, 'show'])->name('distribusi.show');

    // Sirine
    Route::get('/sirine', [SirineController::class, 'index'])->name('sirine.index');
});
