<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Features\Ujian\UjianController;
use App\Http\Controllers\Auth\UjianLoginController;

/*
|--------------------------------------------------------------------------
| EXAM SYSTEM ROUTES
|--------------------------------------------------------------------------
*/

// Main exam routes for students - moved from auth_extended.php
Route::middleware(['auth:siswa'])->prefix('ujian')->name('ujian.')->group(function () {
    // Main exam interface - EXPLICIT PARAMETER
    Route::get('exam/{jadwal_id}', [UjianController::class, 'exam'])
        ->name('exam')
        ->where('jadwal_id', '[0-9]+');

    // Exam API routes
    Route::post('/save-answer', [UjianController::class, 'saveAnswer'])->name('save-answer');
    Route::post('/toggle-flag', [UjianController::class, 'toggleFlag'])->name('toggle-flag');

    Route::post('/submit', [UjianController::class, 'submitExam'])->name('submit');
    Route::get('/confirm-finish', [UjianController::class, 'confirmFinish'])->name('confirm-finish');
    Route::get('/result', [UjianController::class, 'examResult'])->name('result');

    // Catat pelanggaran (tanpa langsung logout)
    Route::post('record-violation', [UjianController::class, 'recordViolation'])
        ->name('record-violation');
    // Ambil Pelanggaran terakhir siswa
    Route::get('/last-violation/{hasil_ujian_id}', [UjianController::class, 'getLastViolation'])
        ->name('last-violation');
    Route::post('/logout', [UjianController::class, 'logoutFromExam'])->name('logout');

    // Legacy routes for backward compatibility
    Route::get('/start/{jadwal_id?}', [UjianController::class, 'exam'])->name('start');
    Route::get('/soal/{index?}', [UjianController::class, 'showSoal'])->name('soal');
    Route::post('/jawaban', [UjianController::class, 'saveAnswer'])->name('jawaban');
    Route::get('/finish', [UjianController::class, 'finish'])->name('finish');
    Route::get('/result/{hasil}', [UjianController::class, 'result'])->name('result-detail');
});

// Keep siswa namespace routes for dashboard compatibility
Route::middleware(['auth:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    // Redirect old exam routes to new ujian routes
    Route::get('/exam/{jadwal_id?}', function ($jadwal_id = null) {
        if ($jadwal_id) {
            return redirect()->route('ujian.exam', ['jadwal_id' => $jadwal_id]);
        }
        return redirect()->route('siswa.dashboard')->with('error', 'Jadwal ujian tidak ditemukan.');
    })->name('exam');

    Route::get('/exam/result', function () {
        return redirect()->route('ujian.result');
    })->name('exam.result');
});
