<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\JadwalUjianController;
use App\Http\Controllers\Features\Naskah\HasilUjianController;

// Create fallback routes that match the incorrect route names being used in templates
Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Jadwal Ujian fallback routes - map jadwalujian.* to jadwal.* 
    Route::prefix('jadwalujian')->name('jadwalujian.')->group(function () {
        Route::get('/', [JadwalUjianController::class, 'index'])->name('index');
        Route::get('/create', [JadwalUjianController::class, 'create'])->name('create');
        Route::post('/', [JadwalUjianController::class, 'store'])->name('store');
        Route::get('/{jadwal}', [JadwalUjianController::class, 'show'])->name('show');
        Route::get('/{jadwal}/edit', [JadwalUjianController::class, 'edit'])->name('edit');
        Route::put('/{jadwal}', [JadwalUjianController::class, 'update'])->name('update');
        Route::delete('/{jadwal}', [JadwalUjianController::class, 'destroy'])->name('destroy');
        Route::put('/{jadwal}/status', [JadwalUjianController::class, 'updateStatus'])->name('status');
    });

    // Hasil Ujian fallback routes - map hasilujian.* to hasil.* 
    Route::prefix('hasilujian')->name('hasilujian.')->group(function () {
        Route::get('/', [HasilUjianController::class, 'index'])->name('index');
        Route::get('/{hasil}', [HasilUjianController::class, 'show'])->name('show');
        Route::delete('/{hasil}', [HasilUjianController::class, 'destroy'])->name('destroy');
    });
});
