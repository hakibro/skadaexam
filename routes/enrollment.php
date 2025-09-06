<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\EnrollmentController;
use App\Http\Controllers\Features\Ruangan\EnrollmentUjianController;

/*
|--------------------------------------------------------------------------
| Enrollment ROUTES
|--------------------------------------------------------------------------
|
| Routes related to enrollments including old enrollment system and 
| new enrollment ujian system.
|
*/

Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Old Enrollment routes (if still needed)
    Route::get('enrollment', [EnrollmentController::class, 'index'])->name('enrollment.index');
    Route::get('enrollment/{mapel}', [EnrollmentController::class, 'show'])->name('enrollment.show');
    Route::get('enrollment/{mapel}/create', [EnrollmentController::class, 'create'])->name('enrollment.create');
    Route::post('enrollment/{mapel}', [EnrollmentController::class, 'store'])->name('enrollment.store');
    Route::delete('enrollment/{mapel}/{siswa}', [EnrollmentController::class, 'destroy'])->name('enrollment.destroy');
    Route::put('enrollment/{mapel}/{siswa}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollment.update-status');
    Route::get('enrollment/siswa', [EnrollmentController::class, 'getSiswaByKelas'])->name('enrollment.get-siswa');

    // New Enrollment Ujian routes
    Route::get('enrollment-ujian/get-sesi-options', [EnrollmentUjianController::class, 'getSesiOptions'])
        ->name('enrollment-ujian.get-sesi-options');
    Route::get('enrollment-ujian/get-siswa-options', [EnrollmentUjianController::class, 'getSiswaOptions'])
        ->name('enrollment-ujian.get-siswa-options');
    Route::post('enrollment-ujian/bulk', [EnrollmentUjianController::class, 'bulkEnrollment'])
        ->name('enrollment-ujian.bulk');
    Route::post('enrollment-ujian/{enrollmentUjian}/generate-token', [EnrollmentUjianController::class, 'generateToken'])
        ->name('enrollment-ujian.generate-token');
    Route::patch('enrollment-ujian/{enrollmentUjian}/status/{status}', [EnrollmentUjianController::class, 'updateStatus'])
        ->name('enrollment-ujian.update-status');
    Route::resource('enrollment-ujian', EnrollmentUjianController::class);
});
