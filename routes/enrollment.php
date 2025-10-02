<?php

use App\Http\Controllers\Features\Naskah\EnrollmentUjianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ENROLLMENT ROUTES - CONSOLIDATED
|--------------------------------------------------------------------------
|
| This file contains all enrollment-related routes for both the Naskah and
| Ruangan features. These routes handle student enrollment to exams, session
| management, and token generation.
|
*/

// Enrollment Management Routes - Naskah Feature
Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Enrollment Ujian routes
    Route::get('enrollment-ujian/get-sesi-options', [EnrollmentUjianController::class, 'getSesiOptions'])->name('enrollment-ujian.get-sesi-options');
    Route::get('enrollment-ujian/get-siswa-options', [EnrollmentUjianController::class, 'getSiswaOptions'])->name('enrollment-ujian.get-siswa-options');
    Route::get('enrollment-ujian/get-siswa-by-kelas', [EnrollmentUjianController::class, 'getSiswaByKelas'])->name('enrollment-ujian.get-siswa-by-kelas');
    Route::post('enrollment-ujian/bulk', [EnrollmentUjianController::class, 'bulkEnrollment'])->name('enrollment-ujian.bulk');
    Route::post('enrollment-ujian/bulk-action', [EnrollmentUjianController::class, 'bulkAction'])
        ->name('enrollment-ujian.bulk-action');

    Route::post('enrollment-ujian/generate-tokens', [EnrollmentUjianController::class, 'generateTokens'])->name('enrollment-ujian.generate-tokens');
    Route::post('enrollment-ujian/{enrollmentUjian}/generate-token', [EnrollmentUjianController::class, 'generateToken'])->name('enrollment-ujian.generate-token');
    Route::get('enrollment-ujian/{enrollmentUjian}/print-qr', [EnrollmentUjianController::class, 'printQR'])->name('enrollment-ujian.print-qr');

    Route::patch('enrollment-ujian/{enrollmentUjian}/status/{status}', [EnrollmentUjianController::class, 'updateStatus'])->name('enrollment-ujian.update-status');
    Route::resource('enrollment-ujian', EnrollmentUjianController::class);
});


// Ruangan-based automatic enrollment routes - now using the same controller
Route::middleware(['auth:web', 'role:admin,koordinator'])->prefix('ruangan')->name('ruangan.')->group(function () {
    // Auto-enrollment through session room assignments
    Route::prefix('enrollment')->name('enrollment.')->group(function () {
        Route::get('/', [EnrollmentUjianController::class, 'index'])->name('index');
        Route::post('/bulk', [EnrollmentUjianController::class, 'bulkEnrollment'])->name('bulk');
        Route::post('/generate-tokens', [EnrollmentUjianController::class, 'generateTokens'])->name('generate-tokens');
        Route::post('/{sesi}/sync', [EnrollmentUjianController::class, 'syncEnrollments'])->name('sync');
    });
});
