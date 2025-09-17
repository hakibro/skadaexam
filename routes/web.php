<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\GuruLoginController;
use App\Http\Controllers\Siswa\SiswaLoginController;
use App\Http\Controllers\ProfileController;

// Dashboard controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Features\Data\DashboardController as DataDashboard;
use App\Http\Controllers\Features\Data\GuruController;
use App\Http\Controllers\Features\Data\KelasController;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Http\Controllers\Features\Naskah\DashboardController as NaskahDashboard;
use App\Http\Controllers\Features\Pengawas\DashboardController as PengawasDashboard;
use App\Http\Controllers\Features\Koordinator\DashboardController as KoordinatorDashboard;
// Legacy AssignmentController has been removed
use App\Http\Controllers\Features\Koordinator\MonitoringController;
use App\Http\Controllers\Features\Koordinator\LaporanController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/
// Authentication routes moved to routes/auth_extended.php
require __DIR__ . '/auth_extended.php';

/*
|--------------------------------------------------------------------------
| MAIN DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    // Pastikan user terautentikasi
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    // Debug log untuk melihat apa yang terjadi
    Log::info('Dashboard redirect attempt', [
        'user_id' => $user->id ?? null,
        'user_email' => $user->email ?? null,
        'roles' => $user->roles->pluck('name')->toArray() ?? []
    ]);

    // Use the getRedirectRoute helper method to determine where to redirect
    $redirectRoute = $user->getRedirectRoute();

    // Check if the route exists before redirecting
    if (Route::has($redirectRoute)) {
        return redirect()->route($redirectRoute);
    }

    // Fallback jika route tidak ditemukan
    auth()->logout();
    return redirect()->route('login')->with('error', 'Dashboard not found for role: ' . $user->role);
})->middleware('auth:web')->name('dashboard');

/*
|--------------------------------------------------------------------------
| FEATURE-SPECIFIC ROUTES
|--------------------------------------------------------------------------
*/

// Load consolidated feature-specific routes
require __DIR__ . '/admin.php';            // Admin routes
require __DIR__ . '/data.php';             // Data management (guru, siswa, kelas) + Guru/Siswa user/portal routes
require __DIR__ . '/naskah.php';           // Naskah management (mapel, banksoal, soal, jadwal, hasil, panduan)
require __DIR__ . '/pengawas.php';         // Pengawas features
require __DIR__ . '/koordinator.php';      // Koordinator features
require __DIR__ . '/ruangan.php';          // Ruangan management (ruangan, sesi, siswa)
require __DIR__ . '/ujian.php';            // Ujian/Exam functionality
require __DIR__ . '/enrollment.php';       // Enrollment management (consolidated from multiple modules)
require __DIR__ . '/api_internal.php';     // API endpoints for AJAX
require __DIR__ . '/fallback.php';         // Fallback routes for compatibility

// Include debug routes if in debug mode
require __DIR__ . '/debug.php';
require __DIR__ . '/debug-mapel.php'; // Debug route for mapel relationships
require __DIR__ . '/debug-dates.php'; // Debug route for date filtering issues
require __DIR__ . '/debug-login-redirect.php'; // Debug route for login redirection

// Debug route for student login issues
if (app()->environment(['local', 'development'])) {
    $debugRoutesPath = __DIR__ . '/../debug_routes.php';
    if (file_exists($debugRoutesPath)) {
        require $debugRoutesPath; // Debug student login tokens
    }
}

// Test route for form submission debugging
if (app()->environment(['local', 'development'])) {
    Route::get('/test-form', function () {
        return view('test-form');
    })->name('test.form');
}

/*
|--------------------------------------------------------------------------
| DOCUMENTATION ROUTES
|--------------------------------------------------------------------------
*/

// Test route for ruangan/template
Route::get('/test-ruangan-template', function () {
    return 'This is a test route for ruangan/template';
});
