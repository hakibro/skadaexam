<?php

use App\Http\Controllers\Features\Pengawas\DashboardController;
use Illuminate\Support\Facades\Route;

// This file is now redundant since we're using the web guard for all users including guru
// Routes are now defined in pengawas.php

// Keeping this file as a placeholder with a redirect for backward compatibility
Route::prefix('pengawas')->middleware(['auth:web', 'role:admin,pengawas'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('pengawas.dashboard');
    });
});

// Add a new route to handle the old URL format and redirect to the new one
Route::get('/pengawas', function () {
    return redirect('/features/pengawas');
})->middleware(['auth:web', 'role:pengawas']);
