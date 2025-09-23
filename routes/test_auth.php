<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Test route untuk cek authentication siswa
Route::middleware(['auth:siswa'])->get('/test-siswa-auth', function (Request $request) {
    $siswa = Auth::guard('siswa')->user();

    return response()->json([
        'authenticated' => Auth::guard('siswa')->check(),
        'siswa_id' => $siswa ? $siswa->id : null,
        'siswa_nama' => $siswa ? $siswa->nama : null,
        'session_id' => session()->getId(),
        'current_enrollment_id' => $request->session()->get('current_enrollment_id'),
        'current_sesi_ruangan_id' => $request->session()->get('current_sesi_ruangan_id')
    ]);
})->name('test.siswa.auth');

// Test answer saving dengan debug yang lebih baik
Route::middleware(['auth:siswa'])->post('/test-save-answer-debug', function (Request $request) {
    $siswa = Auth::guard('siswa')->user();

    \Illuminate\Support\Facades\Log::info('Debug save answer test', [
        'siswa_authenticated' => Auth::guard('siswa')->check(),
        'siswa_id' => $siswa ? $siswa->id : null,
        'request_data' => $request->all(),
        'session_data' => $request->session()->all()
    ]);

    return response()->json([
        'success' => true,
        'siswa' => $siswa ? $siswa->only(['id', 'nama', 'nis']) : null,
        'request_received' => $request->all()
    ]);
})->name('test.save.answer.debug');
