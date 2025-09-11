<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin Routes
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);

    // Data fix routes
    Route::get('/data-fix', [\App\Http\Controllers\DataFixController::class, 'fixAssociations'])->name('data-fix');
});
