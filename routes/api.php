<?php

use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\RuanganSiswaController;
use App\Http\Controllers\Api\JadwalUjianController;
use App\Http\Controllers\Api\PengawasController;
use App\Http\Controllers\Api\LiveUjianController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\KehadiranController;
use App\Http\Controllers\Api\FilterOptionsController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api')->group(function () {
    // API FILTER OPTIONS
    Route::get('/filter-options', [FilterOptionsController::class, 'index']);

    // API SISWA
    Route::get('/siswas', [SiswaController::class, 'index']);
    Route::post('/siswas/quick-sync', [SiswaController::class, 'quickSync']);
    Route::post('/siswas/sync', [SiswaController::class, 'quickSync']);
    Route::get('/siswas/{id}', [SiswaController::class, 'show']);
    Route::post('/siswas', [SiswaController::class, 'store']);
    Route::put('/siswas/{id}', [SiswaController::class, 'update']);
    Route::patch('/siswas/{siswa}/rekomendasi', [SiswaController::class, 'setRekomendasi']);

    // API JADWAL UJIAN
    Route::get('/jadwal-ujian', [JadwalUjianController::class, 'index']);

    // API RUANGAN SISWA
    Route::get('/ruangan', [RuanganSiswaController::class, 'index']);

    // API PENGAWAS
    Route::get('/pengawas', [PengawasController::class, 'index']);

    // API KEHADIRAN
    Route::get('/kehadiran', [KehadiranController::class, 'index']);

    // API LIVE UJIAN
    Route::get('/live-ujian/progress', [LiveUjianController::class, 'progress']);

    // API USER
    Route::middleware('api.bearer')->get('/users', [UserController::class, 'index']);
});
