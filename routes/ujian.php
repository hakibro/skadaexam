<?php

use Illuminate\Support\Facades\Route;

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

// Enrollment management routes moved to routes/enrollment.php

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
