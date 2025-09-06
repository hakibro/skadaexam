<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\BankSoalController;
use App\Http\Controllers\Features\Naskah\SoalController;

/*
|--------------------------------------------------------------------------
| Bank Soal and Soal ROUTES
|--------------------------------------------------------------------------
|
| Routes related to question banks (bank soal) and questions (soal)
| including CRUD operations, imports and previews.
|
*/

Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Bank Soal routes
    Route::resource('banksoal', BankSoalController::class);

    // Soal routes
    Route::resource('soal', SoalController::class);
    Route::post('soal/bulk-delete', [SoalController::class, 'bulkDelete'])->name('soal.bulk-delete');
    Route::get('soal/{soal}/duplicate', [SoalController::class, 'duplicate'])->name('soal.duplicate');
    Route::get('soal/import', [SoalController::class, 'import'])->name('soal.import');
    Route::get('soal/{soal}/preview', [SoalController::class, 'preview'])->name('soal.preview');
});
