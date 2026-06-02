<?php

use App\Http\Controllers\Admin\DashboardController;
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
    Route::resource('users', UserController::class);

    // Data fix routes
    Route::get('/data-fix', [\App\Http\Controllers\DataFixController::class, 'fixAssociations'])->name('data-fix');
});
