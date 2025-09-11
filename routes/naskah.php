<?php

use App\Http\Controllers\Features\Naskah\DashboardController;
use App\Http\Controllers\Features\Naskah\MapelController;
use App\Http\Controllers\Features\Naskah\MapelRecoveryController;
use App\Http\Controllers\Features\Naskah\BankSoalController;
use App\Http\Controllers\Features\Naskah\SoalController;
use App\Http\Controllers\Features\Naskah\JadwalUjianController;
use App\Http\Controllers\Features\Naskah\UpdateJadwalTargetKelasController;
use App\Http\Controllers\Features\Naskah\HasilUjianController;
use App\Http\Controllers\Features\Naskah\PanduanController;
use Illuminate\Support\Facades\Route;

// Naskah Management Routes
Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ===== MAPEL MANAGEMENT =====
    // Mapel trash management routes - MUST be defined before the resource routes to avoid conflicts
    Route::get('mapel/trashed', [MapelRecoveryController::class, 'index'])->name('mapel.trashed');
    Route::get('mapel/trashed/restore-all', [MapelRecoveryController::class, 'restoreAll'])->name('mapel.trashed.restore-all');
    Route::get('mapel/trashed/force-delete-all', [MapelRecoveryController::class, 'forceDeleteAll'])->name('mapel.trashed.force-delete-all');
    Route::get('mapel/trashed/{id}/restore', [MapelRecoveryController::class, 'restore'])->name('mapel.trashed.restore');
    Route::get('mapel/trashed/{id}/force-delete', [MapelRecoveryController::class, 'forceDelete'])->name('mapel.trashed.force-delete');
    Route::resource('mapel', MapelController::class);
    Route::put('mapel/{mapel}/status', [MapelController::class, 'updateStatus'])->name('mapel.status');
    Route::post('mapel/bulk-action', [MapelController::class, 'bulkAction'])->name('mapel.bulk-action');

    // ===== BANK SOAL & SOAL MANAGEMENT =====
    // Bank Soal routes
    Route::resource('banksoal', BankSoalController::class);
    // Soal routes
    Route::resource('soal', SoalController::class);
    Route::post('soal/bulk-delete', [SoalController::class, 'bulkDelete'])->name('soal.bulk-delete');
    Route::get('soal/{soal}/duplicate', [SoalController::class, 'duplicate'])->name('soal.duplicate');
    Route::get('soal/import', [SoalController::class, 'import'])->name('soal.import');
    Route::get('soal/{soal}/preview', [SoalController::class, 'preview'])->name('soal.preview');

    // ===== JADWAL UJIAN MANAGEMENT =====
    // Batch update kelas_target
    Route::get('jadwal/batch-update-kelas-target', [UpdateJadwalTargetKelasController::class, 'index'])->name('jadwal.batch-update-kelas-target');
    Route::post('jadwal/batch-update-kelas-target', [UpdateJadwalTargetKelasController::class, 'update'])->name('jadwal.batch-update-kelas-target.update');
    // Bulk action for jadwal
    Route::post('jadwal/bulk-action', [JadwalUjianController::class, 'bulkAction'])->name('jadwal.bulk-action');
    // Resource routes
    Route::resource('jadwal', JadwalUjianController::class);
    // Additional jadwal actions
    Route::put('jadwal/{jadwal}/status', [JadwalUjianController::class, 'updateStatus'])->name('jadwal.status');
    Route::post('jadwal/{jadwal}/attach-sesi', [JadwalUjianController::class, 'attachSesi'])->name('jadwal.attach-sesi');
    Route::post('jadwal/{jadwal}/detach-sesi', [JadwalUjianController::class, 'detachSesi'])->name('jadwal.detach-sesi');
    Route::post('jadwal/{jadwal}/reassign-sesi', [JadwalUjianController::class, 'reassignSesi'])->name('jadwal.reassign-sesi');
    Route::put('jadwal/{jadwal}/toggle-auto-assign', [JadwalUjianController::class, 'toggleAutoAssign'])->name('jadwal.toggle-auto-assign');
    Route::put('jadwal/{jadwal}/switch-scheduling-mode', [JadwalUjianController::class, 'switchSchedulingMode'])->name('jadwal.switch-scheduling-mode');
    Route::post('jadwal/{jadwal}/apply-to-sessions', [JadwalUjianController::class, 'applyToSessions'])->name('jadwal.apply-to-sessions');

    // ===== HASIL UJIAN MANAGEMENT =====
    Route::get('hasil', [HasilUjianController::class, 'index'])->name('hasil.index');
    Route::get('hasil/{hasil}', [HasilUjianController::class, 'show'])->name('hasil.show');
    Route::get('hasil/jadwal/{jadwal}', [HasilUjianController::class, 'byJadwal'])->name('hasil.by-jadwal');
    Route::get('hasil/jadwal/{jadwal}/sesi/{sesi}', [HasilUjianController::class, 'bySesi'])->name('hasil.by-sesi');
    Route::get('hasil/analisis', [HasilUjianController::class, 'analisis'])->name('hasil.analisis');
    Route::post('hasil/export', [HasilUjianController::class, 'export'])->name('hasil.export');
    Route::delete('hasil/{hasil}', [HasilUjianController::class, 'destroy'])->name('hasil.destroy');

    // ===== ENROLLMENT MANAGEMENT =====
    // Enrollment routes moved to routes/enrollment.php

    // ===== PANDUAN/GUIDELINES =====
    Route::get('/panduan/format-docx', [PanduanController::class, 'formatDocx'])->name('panduan.format-docx');
});
