<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Ruangan\EnrollmentController;
use App\Http\Controllers\Features\Ujian\UjianController;
use App\Http\Controllers\Auth\UjianLoginController;

/*
|--------------------------------------------------------------------------
| EXAM SYSTEM ROUTES
|--------------------------------------------------------------------------
*/

// Token-based authentication routes for students
Route::get('/ujian/login', [UjianLoginController::class, 'showTokenForm'])
    ->name('ujian.token');

Route::post('/ujian/login', [UjianLoginController::class, 'loginWithToken'])
    ->name('ujian.login');

Route::post('/ujian/logout', [UjianLoginController::class, 'logout'])
    ->name('ujian.logout');

// Enrollment management routes for admin/guru
Route::middleware(['auth:web', 'role:admin,guru'])->prefix('enrollment')->name('enrollment.')->group(function () {
    Route::get('/', [EnrollmentController::class, 'index'])->name('index');
    Route::get('/create/{jadwal}', [EnrollmentController::class, 'create'])->name('create');
    Route::post('/store', [EnrollmentController::class, 'store'])->name('store');
    Route::get('/{jadwal}', [EnrollmentController::class, 'show'])->name('show');
    Route::post('/generate-tokens', [EnrollmentController::class, 'generateTokens'])->name('generate-tokens');
});

// Exam routes for students
Route::middleware(['auth:siswa', 'ujian.active'])->prefix('ujian')->name('ujian.')->group(function () {
    Route::get('/start', [UjianController::class, 'start'])->name('start');
    Route::get('/soal/{index?}', [UjianController::class, 'showSoal'])->name('soal');
    Route::post('/jawaban', [UjianController::class, 'saveJawaban'])->name('jawaban');
    Route::get('/confirm-finish', [UjianController::class, 'confirmFinish'])->name('confirm_finish');
});

// These routes don't require the ujian.active middleware
Route::middleware(['auth:siswa'])->prefix('ujian')->name('ujian.')->group(function () {
    Route::get('/finish', [UjianController::class, 'finish'])->name('finish');
    Route::get('/result/{hasil}', [UjianController::class, 'result'])->name('result');
});
