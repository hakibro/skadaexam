<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Features\Naskah\PanduanController;

/*
|--------------------------------------------------------------------------
| Panduan (Guidelines) ROUTES
|--------------------------------------------------------------------------
|
| Routes related to guidelines and documentation.
|
*/

Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    Route::get('/panduan/format-docx', [PanduanController::class, 'formatDocx'])->name('panduan.format-docx');
});
