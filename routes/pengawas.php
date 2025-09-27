<?php

use App\Http\Controllers\Features\Naskah\EnrollmentUjianController;
use App\Http\Controllers\Features\Pengawas\DashboardController;
use App\Http\Controllers\Features\Pengawas\BeritaAcaraController;
use App\Http\Controllers\Features\Pengawas\TokenController;
use \App\Http\Controllers\Features\Pengawas\PelanggaranController;
use App\Models\EnrollmentUjian;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

// Pengawas Routes - All routes related to the pengawas panel
// Allow access from web guard with pengawas role
Route::middleware(['auth:web', 'role:admin,pengawas'])->prefix('features/pengawas')->name('pengawas.')->group(function () {
    // Dashboard
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tata-tertib', [DashboardController::class, 'tataTertib'])->name('tata-tertib');
    Route::get('/debug', [DashboardController::class, 'debug'])->name('debug');

    // Toggle auto-logout feature
    Route::post('/toggle-auto-logout/{jadwalUjianId}', [DashboardController::class, 'toggleAutoLogout'])->name('toggle-auto-logout');
    Route::post('/toggle-submit/{id}', [DashboardController::class, 'toggleSubmitButton'])->name('toggle-submit');

    // Assignment details and attendance management
    Route::get('/assignment/{id}', [DashboardController::class, 'showAssignment'])->name('assignment');
    Route::post('/assignment/{id}/attendance', [DashboardController::class, 'updateAttendance'])->name('update-attendance');

    // Token Generation
    Route::get('/generate-token/{id}', [TokenController::class, 'showTokenForm'])->name('generate-token');
    Route::post('/generate-token/{id}', [TokenController::class, 'generateToken'])->name('store-token');

    // Berita Acara
    Route::get('/berita-acara/{id}', [BeritaAcaraController::class, 'show'])->name('berita-acara.show');
    Route::get('/berita-acara/{id}/create', [BeritaAcaraController::class, 'create'])->name('berita-acara.create');
    Route::post('/berita-acara/{id}', [BeritaAcaraController::class, 'store'])->name('berita-acara.store');
    Route::get('/berita-acara/{id}/edit', [BeritaAcaraController::class, 'edit'])->name('berita-acara.edit');
    Route::put('/berita-acara/{id}', [BeritaAcaraController::class, 'update'])->name('berita-acara.update');
    Route::post('/berita-acara/{id}/finalize', [BeritaAcaraController::class, 'finalize'])->name('berita-acara.finalize');

    // Pelanggaran / Violations Monitoring
    Route::get('/get-violations', [PelanggaranController::class, 'getViolations'])->name('get-violations');
    Route::get('/get-violations/{id}', [PelanggaranController::class, 'getViolations'])->name('get-violations.by-session');
    Route::post('/process-violation/{id}', [PelanggaranController::class, 'processViolation'])->name('process-violation');

    // Enrollment Management
    Route::get('/manage-enrollment/{ujianId}', [EnrollmentUjianController::class, 'manageCancelledEnrollments'])->name('manage-enrollment');

    Route::patch('/manage-enrollment/{id}/restore', [EnrollmentUjianController::class, 'restoreEnrollment'])->name('manage-enrollment.restore');
});
