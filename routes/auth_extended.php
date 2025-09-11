<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\GuruLoginController;
use App\Http\Controllers\Siswa\SiswaLoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
// Guru now uses the main login, but keep the login/guru route for backward compatibility
Route::get('/login/guru', function () {
    return redirect()->route('login');
})->name('login.guru');
Route::get('/login/siswa', [SiswaLoginController::class, 'showLoginForm'])->name('login.siswa');
Route::post('/login/siswa', [SiswaLoginController::class, 'login'])->name('login.siswa.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Profile routes
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
