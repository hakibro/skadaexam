<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Ujian\UjianController;

// Temporary test route to verify ujian controller
Route::get('/test-ujian/{jadwal_id}', function ($jadwal_id) {
    return response()->json([
        'message' => 'Test route working',
        'jadwal_id' => $jadwal_id,
        'timestamp' => now()
    ]);
})->name('test.ujian');
