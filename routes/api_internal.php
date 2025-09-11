<?php

use App\Http\Controllers\Features\Data\SiswaController;
use Illuminate\Support\Facades\Route;

// API Routes for AJAX functionality
Route::middleware('auth:web')->prefix('api')->name('api.')->group(function () {
    // Siswa API endpoints for AJAX
    Route::prefix('siswa')->name('siswa.')->group(function () {
        Route::post('search', [SiswaController::class, 'search'])->name('search');
        Route::post('filter', [SiswaController::class, 'search'])->name('filter');
        Route::get('stats', [SiswaController::class, 'getStats'])->name('stats');
        Route::post('stats-filtered', [SiswaController::class, 'getFilteredStats'])->name('stats.filtered');
        Route::get('kelas-options', [SiswaController::class, 'getKelasOptions'])->name('kelas.options');
        Route::post('bulk-action', [SiswaController::class, 'bulkActions'])->name('bulk.action');
    });

    // General test endpoint
    Route::get('test', function () {
        return response()->json([
            'success' => true,
            'message' => 'API endpoint working',
            'timestamp' => now()->toISOString(),
            'user' => auth()->check() ? auth()->user()->name : 'Not authenticated',
        ]);
    });
});
