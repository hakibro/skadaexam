<?php

use App\Http\Controllers\Features\Data\SiswaController;
use Illuminate\Support\Facades\Route;

// Siswa Data Routes
Route::middleware('auth')->prefix('data')->name('data.')->group(function () {
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

    // New batch import route
    Route::post('siswa/batch-import', [SiswaController::class, 'batchImport'])->name('siswa.batch-import');
    Route::get('siswa/batch-import-status', [SiswaController::class, 'getBatchImportStatus'])->name('siswa.batch-import-status');

    // Sync routes
    Route::post('siswa/sync-from-api', [SiswaController::class, 'syncFromApi'])->name('siswa.sync-from-api');
    Route::get('siswa/sync-progress', [SiswaController::class, 'getSyncProgress'])->name('siswa.sync-progress');
    Route::post('siswa/clear-sync-progress', [SiswaController::class, 'clearSyncProgress'])->name('siswa.clear-sync-progress');

    // New batch sync route
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
