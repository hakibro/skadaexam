<?php

use App\Http\Controllers\Features\Koordinator\DashboardController;
// Legacy AssignmentController has been removed
use App\Http\Controllers\Features\Koordinator\MonitoringController;
use App\Http\Controllers\Features\Koordinator\LaporanController;
use App\Http\Controllers\Features\Koordinator\PengawasAssignmentController;
use Illuminate\Support\Facades\Route;

// Koordinator Routes
Route::middleware(['auth', 'role:admin|koordinator'])->prefix('koordinator')->name('koordinator.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/upload-tata-tertib', [DashboardController::class, 'showUploadForm'])->name('upload-form');
    Route::post('/upload-tata-tertib', [DashboardController::class, 'uploadTataTertib'])->name('upload');

    // Old Assignment Management routes have been removed
    // Use PengawasAssignmentController functionality instead

    // New Pengawas Assignment Management
    Route::prefix('pengawas-assignment')->name('pengawas-assignment.')->group(function () {
        Route::get('/', [PengawasAssignmentController::class, 'index'])->name('index');
        Route::post('/assign', [PengawasAssignmentController::class, 'assign'])->name('assign');
        Route::post('/unassign', [PengawasAssignmentController::class, 'unassign'])->name('unassign');
        Route::post('/bulk-assign', [PengawasAssignmentController::class, 'bulkAssign'])->name('bulk-assign');
        Route::get('/availability', [PengawasAssignmentController::class, 'getPengawasAvailability'])->name('availability');
        Route::get('/schedule/{pengawasId}/{tanggal}', [PengawasAssignmentController::class, 'getSchedule'])->name('schedule');
        Route::get('/schedule/{pengawasId}', [PengawasAssignmentController::class, 'getAllSchedules'])->name('all-schedules');
        Route::get('/calendar', [PengawasAssignmentController::class, 'calendar'])->name('calendar');
        Route::get('/calendar-events', [PengawasAssignmentController::class, 'getCalendarEvents'])->name('calendar-events');
    });

    // Live Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');
        Route::get('/{sesi}', [MonitoringController::class, 'show'])->name('show');
        Route::post('/message', [MonitoringController::class, 'sendMessage'])->name('message');
        Route::post('/allow-reentry', [MonitoringController::class, 'allowReentry'])->name('allow-reentry');
        Route::get('/student-detail/{studentSession}', [MonitoringController::class, 'studentDetail'])->name('student-detail');
        Route::get('/export', [MonitoringController::class, 'export'])->name('export');
    });

    // Report Management
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/verify', [LaporanController::class, 'verify'])->name('verify');
        Route::post('/bulk-verify', [LaporanController::class, 'bulkVerify'])->name('bulk-verify');
        Route::get('/{beritaAcara}', [LaporanController::class, 'show'])->name('show');
        Route::get('/{beritaAcara}/download', [LaporanController::class, 'download'])->name('download');
        Route::get('/{beritaAcara}/edit', [LaporanController::class, 'edit'])->name('edit');
        Route::put('/{beritaAcara}', [LaporanController::class, 'update'])->name('update');
    });
});
