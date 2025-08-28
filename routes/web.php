<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Guru\GuruLoginController;
use App\Http\Controllers\Siswa\SiswaLoginController;

// Controllers untuk role-based routing
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Features\Data\DashboardController as DataDashboard;
use App\Http\Controllers\Features\Data\GuruController;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Http\Controllers\Features\Data\KelasController;
use App\Http\Controllers\Features\Naskah\DashboardController as NaskahDashboard;
use App\Http\Controllers\Features\Pengawas\DashboardController as PengawasDashboard;
use App\Http\Controllers\Features\Koordinator\DashboardController as KoordinatorDashboard;
use App\Http\Controllers\Features\Ruangan\DashboardController as RuanganDashboard;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboard;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| HOME & AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// MAIN DASHBOARD - Role-based redirect
Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->role . '.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// LOGIN ROUTES - Main login system
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Login khusus Guru (Guru table)
Route::get('/login/guru', [GuruLoginController::class, 'showLoginForm'])->name('login.guru');
Route::post('/login/guru', [GuruLoginController::class, 'login'])->name('login.guru.submit');
Route::post('/login/guru/logout', [GuruLoginController::class, 'logout'])->name('logout.guru');

// Login khusus Siswa (Siswa table)
Route::get('/login/siswa', [SiswaLoginController::class, 'showLoginForm'])->name('login.siswa');
Route::post('/login/siswa', [SiswaLoginController::class, 'login'])->name('login.siswa.submit');
Route::post('/login/siswa/logout', [SiswaLoginController::class, 'logout'])->name('logout.siswa');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (Super User - Full Access)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
});

/*
|--------------------------------------------------------------------------
| DATA MANAGEMENT ROUTES - CLEANED & ORGANIZED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web'])->prefix('data')->name('data.')->group(function () {

    // Data Dashboard
    Route::get('/', [DataDashboard::class, 'index'])->name('dashboard');

    // ===== SISWA MANAGEMENT - FRESH CLEAN ROUTES =====

    // Core CRUD Routes
    Route::resource('siswa', SiswaController::class);

    // AJAX Search Route (CRITICAL FOR FILTERS)
    Route::get('siswa-search', [SiswaController::class, 'search'])->name('siswa.search');

    // Import/Export Routes  
    Route::get('siswa-import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::post('siswa-import', [SiswaController::class, 'processImport'])->name('siswa.import.process');
    Route::get('siswa-template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    Route::get('siswa-import-results', [SiswaController::class, 'showImportResults'])->name('siswa.import-results');
    Route::get('siswa-export', [SiswaController::class, 'export'])->name('siswa.export');

    // Test & Sync Routes (SISDA Integration)
    Route::get('siswa-test-sync', [SiswaController::class, 'testSync'])->name('siswa.test-sync');
    Route::post('siswa-sync-all', [SiswaController::class, 'syncAllSisda'])->name('siswa.sync-all');
    Route::post('siswa/test-sync-single', [SiswaController::class, 'testSyncSingle'])->name('siswa.test-sync-single');
    Route::post('siswa/test-sync-multiple', [SiswaController::class, 'testSyncMultiple'])->name('siswa.test-sync-multiple');

    // Individual Sync Routes
    Route::post('siswa/{siswa}/sync-sisda', [SiswaController::class, 'syncSisda'])->name('siswa.sync-sisda');
    Route::post('siswa/{siswa}/refresh-payment', [SiswaController::class, 'refreshPaymentStatus'])->name('siswa.refresh-payment');

    // Bulk Operations Routes
    Route::post('siswa-bulk-sync', [SiswaController::class, 'bulkSync'])->name('siswa.bulk-sync');
    Route::post('siswa-bulk-delete', [SiswaController::class, 'bulkDelete'])->name('siswa.bulk-delete');
    Route::post('siswa-bulk-update-status', [SiswaController::class, 'bulkUpdateStatus'])->name('siswa.bulk-update-status');

    // Payment Status Routes
    Route::post('siswa-payment-status', [SiswaController::class, 'getPaymentStatus'])->name('siswa.payment-status');
    Route::post('siswa-refresh-all-payment', [SiswaController::class, 'refreshAllPaymentStatus'])->name('siswa.refresh-all-payment');

    // Validation Routes
    Route::post('siswa/validate-idyayasan', [SiswaController::class, 'validateIdYayasan'])->name('siswa.validate-idyayasan');

    // Statistics & Reports
    Route::get('siswa-stats', [SiswaController::class, 'getStats'])->name('siswa.stats');
    Route::get('siswa-sync-report', [SiswaController::class, 'syncReport'])->name('siswa.sync-report');
    Route::get('siswa-export-sync-report', [SiswaController::class, 'exportSyncReport'])->name('siswa.export-sync-report');

    // ===== GURU MANAGEMENT - FIXED WITH SEARCH ROUTE =====
    Route::resource('guru', GuruController::class);

    // AJAX Search Route for Guru (MISSING ROUTE - ADD THIS)
    Route::get('guru-search', [GuruController::class, 'search'])->name('guru.search');

    // Import/Export Routes for Guru
    Route::get('guru-import', [GuruController::class, 'import'])->name('guru.import');
    Route::post('guru-import', [GuruController::class, 'processImport'])->name('guru.import.process');
    Route::get('guru-template', [GuruController::class, 'downloadTemplate'])->name('guru.template');

    // ===== KELAS MANAGEMENT =====
    Route::resource('kelas', KelasController::class);

    // Email preview route (ADD THIS)
    Route::post('siswa-preview-email', [SiswaController::class, 'previewEmail'])->name('siswa.preview-email');

    // NEW SYNC PAYMENT ROUTES
    Route::post('siswa/{siswa}/sync-payment', [SiswaController::class, 'syncPayment'])->name('siswa.sync-payment');
    Route::post('siswa-sync-all-payments', [SiswaController::class, 'syncAllPayments'])->name('siswa.sync-all-payments');
    Route::get('siswa-sync-stats', [SiswaController::class, 'getSyncStats'])->name('siswa.sync-stats');
});

/*
|--------------------------------------------------------------------------
| OTHER FEATURE ROUTES
|--------------------------------------------------------------------------
*/

// Naskah Management
Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    Route::get('/', [NaskahDashboard::class, 'index'])->name('dashboard');
});

// Pengawas Management
Route::middleware(['auth:web', 'role:admin,pengawas'])->prefix('pengawas')->name('pengawas.')->group(function () {
    Route::get('/', [PengawasDashboard::class, 'index'])->name('dashboard');
});

// Koordinator Management
Route::middleware(['auth:web', 'role:admin,koordinator'])->prefix('koordinator')->name('koordinator.')->group(function () {
    Route::get('/', [KoordinatorDashboard::class, 'index'])->name('dashboard');
});

// Ruangan Management
Route::middleware(['auth:web', 'role:admin,ruangan'])->prefix('ruangan')->name('ruangan.')->group(function () {
    Route::get('/', [RuanganDashboard::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| GURU ROUTES
|--------------------------------------------------------------------------
*/

// Base Guru Dashboard (dari tabel users dengan role guru)
Route::middleware(['auth:web', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [GuruDashboard::class, 'index'])->name('dashboard');
});

// Guru Portal Routes (dari tabel guru terpisah)
Route::middleware('auth:guru')->prefix('guru-portal')->name('guru.portal.')->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'portalIndex'])->name('dashboard');

    // Multi-role guru routes
    Route::middleware(['guru.role:data'])->prefix('data')->name('data.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'dataDashboard'])->name('dashboard');
    });

    Route::middleware(['guru.role:ruangan'])->prefix('ruangan')->name('ruangan.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'ruanganDashboard'])->name('dashboard');
    });

    Route::middleware(['guru.role:pengawas'])->prefix('pengawas')->name('pengawas.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'pengawasDashboard'])->name('dashboard');
    });

    Route::middleware(['guru.role:koordinator'])->prefix('koordinator')->name('koordinator.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'koordinatorDashboard'])->name('dashboard');
    });

    Route::middleware(['guru.role:naskah'])->prefix('naskah')->name('naskah.')->group(function () {
        Route::get('/dashboard', [GuruDashboard::class, 'naskahDashboard'])->name('dashboard');
    });
});

/*
|--------------------------------------------------------------------------
| SISWA ROUTES
|--------------------------------------------------------------------------
*/

// Siswa dari tabel users dengan role siswa
Route::middleware(['auth:web', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', [SiswaDashboard::class, 'index'])->name('dashboard');
});

// Siswa dari tabel siswa terpisah
Route::middleware('auth:siswa')->prefix('siswa-portal')->name('siswa.portal.')->group(function () {
    Route::get('/dashboard', [SiswaDashboard::class, 'portalIndex'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| PROFILE ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| DEBUG & TEST ROUTES
|--------------------------------------------------------------------------
*/

// Force Logout
Route::get('/force-logout', function () {
    Auth::logout();
    session()->flush();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/')->with('message', 'Successfully logged out!');
});

// Debug Role
Route::get('/debug-role', function () {
    $user = Auth::user();
    return [
        'user' => $user,
        'has_hasRole_method' => method_exists($user, 'hasRole'),
        'roles' => method_exists($user, 'roles') ? $user->roles : 'No roles relation',
        'role_field' => $user->role ?? 'No role field',
        'all_attributes' => $user->getAttributes(),
    ];
});

// Test Routes untuk debugging
Route::get('/login-test', function () {
    return 'Login page working! Route OK!';
});

Route::any('/debug-login', function () {
    return 'Debug route working - method: ' . request()->method();
});

// Test admin route tanpa middleware
Route::get('/admin-test', function () {
    return view('admin.dashboard');
});

// Test endpoint untuk check AJAX search
Route::get('/data/test-endpoint', function () {
    return response()->json([
        'success' => true,
        'message' => 'Test endpoint working',
        'timestamp' => now(),
        'routes_available' => [
            'siswa.index' => route('data.siswa.index'),
            'siswa.search' => route('data.siswa.search'),
            'siswa.create' => route('data.siswa.create'),
        ]
    ]);
});
