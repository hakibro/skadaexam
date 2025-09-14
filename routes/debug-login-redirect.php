<?php

use Illuminate\Support\Facades\Route;

// Debug routes for testing siswa login redirection
Route::get('/debug/siswa-login-test', function () {
    return view('debug.siswa_redirect_test');
})->name('debug.siswa_login_test');
