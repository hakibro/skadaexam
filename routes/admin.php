<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KioskSettingController;
use App\Http\Controllers\Admin\ResetTabelController;
use App\Http\Controllers\Admin\SchoolSettingController;
use App\Http\Controllers\Admin\TahunAjaranController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('tahun-ajaran', TahunAjaranController::class)
        ->parameters(['tahun-ajaran' => 'tahunAjaran'])
        ->except(['show', 'destroy']);
    Route::post('tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])
        ->name('tahun-ajaran.activate');
    Route::get('school-settings', [SchoolSettingController::class, 'edit'])->name('school-settings.edit');
    Route::put('school-settings', [SchoolSettingController::class, 'update'])->name('school-settings.update');
    Route::get('kiosk-settings', [KioskSettingController::class, 'edit'])->name('kiosk-settings.edit');
    Route::put('kiosk-settings', [KioskSettingController::class, 'update'])->name('kiosk-settings.update');
    Route::get('reset-tabel', [ResetTabelController::class, 'index'])->name('reset-tabel.index');
    Route::post('reset-tabel', [ResetTabelController::class, 'reset'])->name('reset-tabel.reset');
    Route::post('reset-tabel/sesi-duplikat', [ResetTabelController::class, 'resetDuplicateSesiRuangan'])
        ->name('reset-tabel.sesi-duplikat');
    Route::resource('users', UserController::class);

    // Data fix routes
    Route::get('/data-fix', [\App\Http\Controllers\DataFixController::class, 'fixAssociations'])->name('data-fix');
});
