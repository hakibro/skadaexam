<?php

use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('maintenance')->middleware(['auth', 'role:admin'])->name('maintenance.')->group(function () {
    Route::get('/fix-jawaban-siswa', [MaintenanceController::class, 'showFixJawabanSiswa'])->name('fix-jawaban-siswa');
    Route::post('/run-fix', [MaintenanceController::class, 'runFix'])->name('run-fix');
});
