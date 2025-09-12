<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\GuruLoginController;
use App\Http\Controllers\Siswa\SiswaLoginController;
use App\Http\Controllers\Siswa\SiswaDashboardController;
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

// Student routes with siswa guard
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');
    Route::get('/exam', [SiswaDashboardController::class, 'exam'])->name('exam');

    // Exam API routes
    Route::post('/exam/save-answer', [SiswaDashboardController::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/flag-question', [SiswaDashboardController::class, 'flagQuestion'])->name('exam.flag-question');
    Route::post('/exam/submit', [SiswaDashboardController::class, 'submitExam'])->name('exam.submit');
    Route::get('/exam/result', [SiswaDashboardController::class, 'examResult'])->name('exam.result');

    Route::post('/logout', [SiswaLoginController::class, 'logout'])->name('logout');
});
