<?php

use App\Http\Controllers\Api\SiswaController;
use Illuminate\Support\Facades\Route;


Route::get('/siswas', [SiswaController::class, 'index']);
Route::get('/siswas/{id}', [SiswaController::class, 'show']);
Route::post('/siswas', [SiswaController::class, 'store']);
Route::put('/siswas/{id}', [SiswaController::class, 'update']);
Route::delete('/siswas/{id}', [SiswaController::class, 'destroy']);
