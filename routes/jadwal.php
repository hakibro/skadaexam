<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\JadwalUjianController;
use App\Http\Controllers\Features\Naskah\UpdateJadwalTargetKelasController;
use App\Http\Controllers\Features\Naskah\HasilUjianController;

/*
|--------------------------------------------------------------------------
| Jadwal Ujian ROUTES
|--------------------------------------------------------------------------
|
| Routes related to exam schedules (jadwal ujian) including CRUD operations,
| batch updates, and result management.
|
*/

Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Batch update kelas_target (more specific routes should come first)
    Route::get('jadwal/batch-update-kelas-target', [UpdateJadwalTargetKelasController::class, 'index'])->name('jadwal.batch-update-kelas-target');
    Route::post('jadwal/batch-update-kelas-target', [UpdateJadwalTargetKelasController::class, 'update'])->name('jadwal.batch-update-kelas-target.update');

    // Bulk action for jadwal
    Route::post('jadwal/bulk-action', [JadwalUjianController::class, 'bulkAction'])->name('jadwal.bulk-action');

    // Resource routes
    Route::resource('jadwal', JadwalUjianController::class);

    // Additional jadwal actions with parameters
    Route::put('jadwal/{jadwal}/status', [JadwalUjianController::class, 'updateStatus'])->name('jadwal.status');
    Route::post('jadwal/{jadwal}/attach-sesi', [JadwalUjianController::class, 'attachSesi'])->name('jadwal.attach-sesi');
    Route::post('jadwal/{jadwal}/detach-sesi', [JadwalUjianController::class, 'detachSesi'])->name('jadwal.detach-sesi');
    Route::post('jadwal/{jadwal}/reassign-sesi', [JadwalUjianController::class, 'reassignSesi'])->name('jadwal.reassign-sesi');
    Route::put('jadwal/{jadwal}/toggle-auto-assign', [JadwalUjianController::class, 'toggleAutoAssign'])->name('jadwal.toggle-auto-assign');
    Route::put('jadwal/{jadwal}/switch-scheduling-mode', [JadwalUjianController::class, 'switchSchedulingMode'])->name('jadwal.switch-scheduling-mode');
    Route::post('jadwal/{jadwal}/apply-to-sessions', [JadwalUjianController::class, 'applyToSessions'])->name('jadwal.apply-to-sessions');

    // Hasil Ujian routes
    Route::get('hasil', [HasilUjianController::class, 'index'])->name('hasil.index');
    Route::get('hasil/{hasil}', [HasilUjianController::class, 'show'])->name('hasil.show');
    Route::get('hasil/jadwal/{jadwal}', [HasilUjianController::class, 'byJadwal'])->name('hasil.by-jadwal');
    Route::get('hasil/jadwal/{jadwal}/sesi/{sesi}', [HasilUjianController::class, 'bySesi'])->name('hasil.by-sesi');
    Route::get('hasil/analisis', [HasilUjianController::class, 'analisis'])->name('hasil.analisis');
    Route::post('hasil/export', [HasilUjianController::class, 'export'])->name('hasil.export');
    Route::delete('hasil/{hasil}', [HasilUjianController::class, 'destroy'])->name('hasil.destroy');
});
