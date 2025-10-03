<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;

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
// require __DIR__ . '/auth.php';
// Authentication routes moved to routes/auth_extended.php
require __DIR__ . '/auth_extended.php';

/*
|--------------------------------------------------------------------------
| MAIN DASHBOARD REDIRECT
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = Auth::guard('web')->user() ?? Auth::guard('siswa')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    // Tentukan redirect route dari helper di model
    if (method_exists($user, 'getRedirectRoute')) {
        $redirectRoute = $user->getRedirectRoute();

        if (Route::has($redirectRoute)) {
            return redirect()->route($redirectRoute);
        }
    }

    // fallback: logout user & kembali ke login sesuai guard
    if (Auth::guard('web')->check()) {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login')->with('error', 'Dashboard not found.');
    }

    if (Auth::guard('siswa')->check()) {
        Auth::guard('siswa')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login.siswa')->with('error', 'Dashboard not found.');
    }

    return redirect()->route('login')->with('error', 'Dashboard not found.');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| FEATURE-SPECIFIC ROUTES
|--------------------------------------------------------------------------
*/

// Load consolidated feature-specific routes
require __DIR__ . '/api.php';
require __DIR__ . '/admin.php';            // Admin routes
require __DIR__ . '/data.php';             // Data management (guru, siswa, kelas) + Guru/Siswa user/portal routes
require __DIR__ . '/naskah.php';           // Naskah management (mapel, banksoal, soal, jadwal, hasil, panduan)
require __DIR__ . '/pengawas.php';         // Pengawas features
require __DIR__ . '/koordinator.php';      // Koordinator features
require __DIR__ . '/ruangan.php';          // Ruangan management (ruangan, sesi, siswa)
require __DIR__ . '/ujian.php';            // Ujian/Exam functionality
require __DIR__ . '/test-ujian.php';      // Temporary test for ujian routes
require __DIR__ . '/debug-ujian-specific.php'; // Debug specific ujian issues
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

    Route::middleware(['auth'])->get('/test-violations-api', function () {
        return view('test_violations_api');
    })->name('test.violations.api');

    // Debug violations dashboard
    Route::middleware(['auth'])->get('/debug-violations', function () {
        return view('debug_violations');
    })->name('debug.violations');

    // Test violations directly
    Route::middleware(['auth'])->get('/test-direct-violations', function () {
        $user = auth()->user();
        $violations = \App\Models\PelanggaranUjian::with([
            'siswa',
            'hasilUjian',
            'jadwalUjian.mapel',
            'sesiRuangan.ruangan'
        ])->where('sesi_ruangan_id', 2)
            ->orderBy('waktu_pelanggaran', 'desc')
            ->get();

        return response()->json([
            'user' => $user->name,
            'can_supervise' => $user->canSupervise(),
            'is_admin' => $user->isAdmin(),
            'violations_count' => $violations->count(),
            'violations' => $violations
        ]);
    })->name('test.direct.violations');

    // Test answer saving functionality
    Route::get('/test-answer-saving', function () {
        return view('test_answer_saving');
    })->name('test.answer.saving');

    // Include test authentication routes
    require __DIR__ . '/test_auth.php';
}

/*
|--------------------------------------------------------------------------
| DOCUMENTATION ROUTES
|--------------------------------------------------------------------------
*/
