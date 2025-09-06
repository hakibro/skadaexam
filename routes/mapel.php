<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\MapelController;
use App\Http\Controllers\Features\Naskah\MapelRecoveryController;

/*
|--------------------------------------------------------------------------
| Mapel ROUTES
|--------------------------------------------------------------------------
|
| Routes related to mata pelajaran (mapel) management including CRUD operations
| and trash management for soft-deleted mapel records.
|
*/

Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Mapel trash management routes - MUST be defined before the resource routes to avoid conflicts
    Route::get('mapel/trashed', [MapelRecoveryController::class, 'index'])->name('mapel.trashed');

    // These specific routes must come before the routes with parameters
    Route::get('mapel/trashed/restore-all', [MapelRecoveryController::class, 'restoreAll'])->name('mapel.trashed.restore-all');
    Route::get('mapel/trashed/force-delete-all', [MapelRecoveryController::class, 'forceDeleteAll'])->name('mapel.trashed.force-delete-all');

    // Now the routes with parameters
    Route::get('mapel/trashed/{id}/restore', [MapelRecoveryController::class, 'restore'])->name('mapel.trashed.restore');
    Route::get('mapel/trashed/{id}/force-delete', [MapelRecoveryController::class, 'forceDelete'])->name('mapel.trashed.force-delete');

    // These need to come after the specific routes above
    Route::resource('mapel', MapelController::class);
    Route::put('mapel/{mapel}/status', [MapelController::class, 'updateStatus'])->name('mapel.status');
    Route::post('mapel/bulk-action', [MapelController::class, 'bulkAction'])->name('mapel.bulk-action');
});
