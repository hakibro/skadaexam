<?php

use Illuminate\Support\Facades\Route;

// Direct test route without middleware to check if basic routing works
Route::get('/debug-ujian-route/{jadwal_id}', function ($jadwal_id) {
    return "DEBUG: jadwal_id = " . $jadwal_id . " - Route working!";
});

// Test URL generation
Route::get('/debug-url-generation', function () {
    try {
        $url = route('ujian.exam', ['jadwal_id' => 4]);
        return "URL Generation Test: " . $url;
    } catch (Exception $e) {
        return "URL Generation Error: " . $e->getMessage();
    }
});
