<?php

use App\Http\Controllers\Features\Data\DashboardController;
use App\Http\Controllers\Features\Data\GuruController;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Http\Controllers\Features\Data\KelasController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Siswa\SiswaDashboardController as SiswaDashboard;
use Illuminate\Support\Facades\Route;

// Data Management Routes
Route::middleware(['auth:web', 'role:admin,data'])->prefix('data')->name('data.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ===== GURU MANAGEMENT =====
    // Guru search and import routes
    Route::get('guru/search', [GuruController::class, 'search'])->name('guru.search');
    Route::get('guru/import', [GuruController::class, 'import'])->name('guru.import');
    Route::post('guru/import-process', [GuruController::class, 'processImport'])->name('guru.import.process');
    Route::get('guru/template', [GuruController::class, 'downloadTemplate'])->name('guru.template');
    Route::post('guru/bulk-delete', [GuruController::class, 'bulkDelete'])->name('guru.bulk-delete');
    Route::post('guru/bulk-update-role', [GuruController::class, 'bulkUpdateRole'])->name('guru.bulk-update-role');
    Route::resource('guru', GuruController::class);

    // ===== KELAS MANAGEMENT =====
    Route::prefix('kelas')->name('kelas.')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('index');
        Route::post('/sync', [KelasController::class, 'syncFromSiswa'])->name('sync');
    });

    // ===== SISWA MANAGEMENT =====
    // Basic CRUD routes
    Route::resource('siswa', SiswaController::class);

    // Search functionality
    Route::match(['GET', 'POST'], 'siswa/search', [SiswaController::class, 'search'])->name('siswa.search');

    // API testing routes
    Route::post('siswa/test-connection', [SiswaController::class, 'testApiConnection'])->name('siswa.test-connection');
    Route::post('siswa/test-single-student', [SiswaController::class, 'testApiSingleStudent'])->name('siswa.test-single-student');

    // Import routes
    Route::get('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::post('siswa/import-from-api', [SiswaController::class, 'importFromApi'])->name('siswa.import-from-api');
    Route::post('siswa/import-from-api-ajax', [SiswaController::class, 'importFromApiAjax'])->name('siswa.import-from-api-ajax');
    Route::get('siswa/import-progress', [SiswaController::class, 'getImportProgress'])->name('siswa.import-progress');
    Route::post('siswa/clear-import-progress', [SiswaController::class, 'clearImportProgress'])->name('siswa.clear-import-progress');
    Route::post('siswa/batch-import', [SiswaController::class, 'batchImport'])->name('siswa.batch-import');
    Route::get('siswa/batch-import-status', [SiswaController::class, 'getBatchImportStatus'])->name('siswa.batch-import-status');

    // Sync routes
    Route::post('siswa/sync-from-api', [SiswaController::class, 'syncFromApi'])->name('siswa.sync-from-api');
    Route::get('siswa/sync-progress', [SiswaController::class, 'getSyncProgress'])->name('siswa.sync-progress');
    Route::post('siswa/clear-sync-progress', [SiswaController::class, 'clearSyncProgress'])->name('siswa.clear-sync-progress');
    Route::post('siswa/batch-sync', [SiswaController::class, 'batchSync'])->name('siswa.batch-sync');
    Route::get('siswa/batch-sync-status', [SiswaController::class, 'getBatchSyncStatus'])->name('siswa.batch-sync-status');
    Route::post('siswa/batch-sync-error', [SiswaController::class, 'logBatchSyncError'])->name('siswa.batch-sync-error');

    // Export and stats
    Route::get('siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('siswa/stats', [SiswaController::class, 'getStats'])->name('siswa.stats');
    Route::post('siswa/filtered-stats', [SiswaController::class, 'getFilteredStats'])->name('siswa.filtered-stats');

    // Bulk actions
    Route::post('siswa/bulk-delete', [SiswaController::class, 'bulkDelete'])->name('siswa.bulk-delete');
    Route::post('siswa/bulk-update-rekomendasi', [SiswaController::class, 'bulkUpdateRekomendasi'])->name('siswa.bulk-update-rekomendasi');
    Route::post('siswa/bulk-update-status', [SiswaController::class, 'bulkUpdateStatus'])->name('siswa.bulk-update-status');
});

// Guru User Routes (for guru role)
Route::middleware(['auth:web', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [GuruDashboard::class, 'index'])->name('dashboard');
});

// Guru Portal Routes (separate authentication)
Route::middleware('auth:guru')->prefix('guru-portal')->name('guru.portal.')->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'portalIndex'])->name('dashboard');
});

// Siswa User Routes (for siswa role)
Route::middleware(['auth:web', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', [SiswaDashboard::class, 'index'])->name('dashboard');
});

// Siswa Portal Routes (separate authentication)
Route::middleware('auth:siswa')->prefix('siswa-portal')->name('siswa.portal.')->group(function () {
    Route::get('/dashboard', [SiswaDashboard::class, 'portalIndex'])->name('dashboard');
    Route::get('/exam', [SiswaDashboard::class, 'exam'])->name('exam');
    Route::post('/exam/save-answer', [SiswaDashboard::class, 'saveAnswer'])->name('exam.save-answer');
    Route::post('/exam/flag', [SiswaDashboard::class, 'toggleFlag'])->name('exam.flag');
    Route::post('/exam/submit', [SiswaDashboard::class, 'submitExam'])->name('exam.submit');
});
