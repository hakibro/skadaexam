<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Siswa\SiswaLoginController;
use App\Http\Controllers\Siswa\SiswaDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/login/siswa', [SiswaLoginController::class, 'showLoginForm'])->name('login.siswa');
Route::post('/login/siswa', [SiswaLoginController::class, 'login'])->name('login.siswa.submit');
Route::get('/login/token/{token}', [LoginController::class, 'directTokenLogin'])
    ->name('login.direct-token');

// Profile routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

// Student routes with siswa guard
Route::middleware(['auth:siswa', 'siswa.force_logout'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    // Route::get('/new-dashboard', [SiswaDashboardController::class, 'newDashboard'])->name('new-dashboard');

    Route::post('/logout', [SiswaLoginController::class, 'logout'])->name('logout');
});
