<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\KameraController;
use App\Http\Controllers\SirineController;
use App\Http\Controllers\DistribusiController;

/*
|--------------------------------------------------------------------------
| API Routes untuk ESP32 dan External Devices
|--------------------------------------------------------------------------
*/

// =====================
// RIWAYAT / EVENT API
// =====================
// Untuk ESP32 mengirim data kejadian
Route::post('/riwayat', [RiwayatController::class, 'store']);
Route::get('/riwayat', [RiwayatController::class, 'apiIndex']);

// Acknowledge & Resolve (untuk dashboard)
Route::post('/riwayat/{riwayat}/ack', [RiwayatController::class, 'acknowledge']);
Route::post('/riwayat/{riwayat}/resolve', [RiwayatController::class, 'resolve']);

// =====================
// SIRINE CONTROL API
// =====================
// ESP32 cek status sirine
Route::get('/sirine', [SirineController::class, 'getStatus']);
// Set status sirine (dari web atau API)
Route::post('/sirine', [SirineController::class, 'setStatus']);
// Toggle sirine
Route::post('/sirine/toggle', [SirineController::class, 'toggle']);
// Set to AUTO mode
Route::post('/sirine/auto', [SirineController::class, 'setAuto']);

// Legacy support (control endpoint)
Route::get('/control', [SirineController::class, 'getStatus']);
Route::post('/control', [SirineController::class, 'setStatus']);

// =====================
// KAMERA API
// =====================
// ESP32 kirim gambar
Route::post('/kamera', [KameraController::class, 'store']);
// Get gambar terakhir
Route::get('/kamera/latest', [KameraController::class, 'latestImage']);

// =====================
// DISTRIBUSI API
// =====================
Route::post('/distribusi/{distribusi}/retry', [DistribusiController::class, 'retry']);

// =====================
// HEALTH CHECK
// =====================
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
    ]);
});
