<?php

use App\Http\Controllers\Features\Ruangan\RuanganController;
use App\Http\Controllers\Features\Ruangan\SesiRuanganController;
use App\Http\Controllers\Features\Ruangan\SesiTemplateController;
use App\Http\Controllers\Features\Ruangan\DashboardController;


use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Ruangan Management Routes
|--------------------------------------------------------------------------
*/


// Main ruangan routes
Route::middleware(['auth:web', 'role:admin'])
    ->prefix('ruangan')
    ->name('ruangan.')
    ->group(function () {

        // Dashboard (paling spesifik, static)
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        // ===============================
        // Template routes (STATIC PREFIX)
        // ===============================
        Route::prefix('template')->name('template.')->group(function () {
            Route::get('/', [SesiTemplateController::class, 'index'])->name('index');
            Route::get('/create', [SesiTemplateController::class, 'create'])->name('create');
            Route::post('/', [SesiTemplateController::class, 'store'])->name('store');
            Route::get('/{template}', [SesiTemplateController::class, 'show'])->name('show');
            Route::get('/{template}/edit', [SesiTemplateController::class, 'edit'])->name('edit');
            Route::put('/{template}', [SesiTemplateController::class, 'update'])->name('update');
            Route::delete('/{template}', [SesiTemplateController::class, 'destroy'])->name('destroy');
            Route::delete('/{template}/force', [SesiTemplateController::class, 'forceDelete'])->name('force-delete');
            Route::put('/{template}/toggle-active', [SesiTemplateController::class, 'toggleActive'])->name('toggle-active');
            Route::get('/{template}/apply', [SesiTemplateController::class, 'showApplyForm'])->name('show-apply');
            Route::post('/{template}/apply', [SesiTemplateController::class, 'applyTemplate'])->name('apply');
        });

        // ===============================
        // CRUD Ruangan
        // ===============================
        Route::get('/list', [RuanganController::class, 'index'])->name('index');
        Route::get('/create', [RuanganController::class, 'create'])->name('create');
        Route::post('/', [RuanganController::class, 'store'])->name('store');
        Route::get('/import', [RuanganController::class, 'import'])->name('import');
        Route::post('/import/process', [RuanganController::class, 'processImport'])->name('import.process');
        Route::post('/bulk-delete', [RuanganController::class, 'bulkDelete'])->name('bulk-delete');

        // ===============================
        // Route dengan parameter {ruangan}
        // ===============================
        Route::prefix('{ruangan}')->whereNumber('ruangan')->group(function () {
            Route::get('/', [RuanganController::class, 'show'])->name('show');
            Route::get('/edit', [RuanganController::class, 'edit'])->name('edit');
            Route::put('/', [RuanganController::class, 'update'])->name('update');
            Route::put('/update-status', [RuanganController::class, 'updateStatus'])->name('update-status');
            Route::delete('/', [RuanganController::class, 'destroy'])->name('destroy');
            Route::delete('/force', [RuanganController::class, 'forceDelete'])->name('force-delete');

            // Sesi Ruangan
            Route::prefix('sesi')->name('sesi.')->group(function () {
                Route::get('/', [SesiRuanganController::class, 'index'])->name('index');
                Route::get('/create', [SesiRuanganController::class, 'create'])->name('create');
                Route::post('/', [SesiRuanganController::class, 'store'])->name('store');
                Route::get('/{sesi}', [SesiRuanganController::class, 'show'])->name('show');
                Route::get('/{sesi}/edit', [SesiRuanganController::class, 'edit'])->name('edit');
                Route::put('/{sesi}', [SesiRuanganController::class, 'update'])->name('update');
                Route::delete('/{sesi}', [SesiRuanganController::class, 'destroy'])->name('destroy');
                Route::delete('/{sesi}/force', [SesiRuanganController::class, 'forceDelete'])->name('force-delete');
                Route::post('/{sesi}/generate-token', [SesiRuanganController::class, 'generateToken'])->name('generate-token');

                // siswa dalam sesi
                Route::prefix('{sesi}/siswa')->name('siswa.')->group(function () {
                    Route::get('/', [SesiRuanganController::class, 'siswaIndex'])->name('index');
                    Route::post('/', [SesiRuanganController::class, 'siswaStore'])->name('store');
                    Route::delete('/{siswa}', [SesiRuanganController::class, 'siswaDestroy'])->name('destroy');
                    Route::delete('/', [SesiRuanganController::class, 'siswaDestroyAll'])->name('destroy-all');
                });

                // jadwal ujian dalam sesi
                Route::prefix('{sesi}/jadwal')->name('jadwal.')->group(function () {
                    Route::get('/', [SesiRuanganController::class, 'jadwalIndex'])->name('index');
                    Route::post('/', [SesiRuanganController::class, 'jadwalStore'])->name('store');
                    Route::delete('/{jadwal}', [SesiRuanganController::class, 'jadwalDestroy'])->name('destroy');
                });
            });
        });
    });
