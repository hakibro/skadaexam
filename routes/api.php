<?php

use App\Http\Controllers\Api\SiswaController;
use App\Http\Controllers\Api\RuanganSiswaController;
use Illuminate\Support\Facades\Route;

Route::prefix('/api')->group(function () {

    // API SISWA
    Route::get('/siswas', [SiswaController::class, 'index']);
    Route::get('/siswas/{id}', [SiswaController::class, 'show']);
    Route::post('/siswas', [SiswaController::class, 'store']);
    Route::put('/siswas/{id}', [SiswaController::class, 'update']);
    Route::delete('/siswas/{id}', [SiswaController::class, 'destroy']);

    // API RUANGAN SISWA
    Route::get('/ruangan', [RuanganSiswaController::class, 'index']);

});