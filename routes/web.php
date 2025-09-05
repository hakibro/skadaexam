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

// Import all dashboard controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Features\Data\DashboardController as DataDashboard;
use App\Http\Controllers\Features\Data\SiswaController;
use App\Http\Controllers\Features\Data\GuruController;
use App\Http\Controllers\Features\Data\KelasController;
use App\Http\Controllers\Features\Naskah\DashboardController as NaskahDashboard;
use App\Http\Controllers\Features\Naskah\BankSoalController;
use App\Http\Controllers\Features\Naskah\SoalController;
use App\Http\Controllers\Features\Naskah\MapelController;
use App\Http\Controllers\Features\Naskah\JadwalUjianController;
use App\Http\Controllers\Features\Naskah\HasilUjianController;
use App\Http\Controllers\Features\Naskah\PanduanController;
use App\Http\Controllers\Features\Pengawas\DashboardController as PengawasDashboard;
use App\Http\Controllers\Features\Koordinator\DashboardController as KoordinatorDashboard;
use App\Http\Controllers\Features\Koordinator\AssignmentController;
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
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/login/guru', [GuruLoginController::class, 'showLoginForm'])->name('login.guru');
Route::post('/login/guru', [GuruLoginController::class, 'login'])->name('login.guru.submit');
Route::get('/login/siswa', [SiswaLoginController::class, 'showLoginForm'])->name('login.siswa');
Route::post('/login/siswa', [SiswaLoginController::class, 'login'])->name('login.siswa.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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
        'user_role' => $user->role ?? null,
        'user_email' => $user->email ?? null
    ]);

    // Pastikan user memiliki role yang valid
    if (!$user || !$user->role) {
        auth()->logout();
        return redirect()->route('login')->with('error', 'Invalid user role. Please contact administrator.');
    }

    // Redirect berdasarkan role dengan pengecekan route exists
    switch ($user->role) {
        case 'admin':
            if (Route::has('admin.dashboard')) {
                return redirect()->route('admin.dashboard');
            }
            break;
        case 'data':
            if (Route::has('data.dashboard')) {
                return redirect()->route('data.dashboard');
            }
            break;
        case 'naskah':
            if (Route::has('naskah.dashboard')) {
                return redirect()->route('naskah.dashboard');
            }
            break;
        case 'pengawas':
            if (Route::has('pengawas.dashboard')) {
                return redirect()->route('pengawas.dashboard');
            }
            break;
        case 'koordinator':
            if (Route::has('koordinator.dashboard')) {
                return redirect()->route('koordinator.dashboard');
            }
            break;
        case 'guru':
            if (Route::has('guru.dashboard')) {
                return redirect()->route('guru.dashboard');
            }
            break;
        case 'siswa':
            if (Route::has('siswa.dashboard')) {
                return redirect()->route('siswa.dashboard');
            }
            break;
        default:
            auth()->logout();
            return redirect()->route('login')->with('error', 'Unknown user role: ' . $user->role);
    }

    // Fallback jika route tidak ditemukan
    auth()->logout();
    return redirect()->route('login')->with('error', 'Dashboard not found for role: ' . $user->role);
})->middleware('auth:web')->name('dashboard');

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboard::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);

    // Data fix routes
    Route::get('/data-fix', [\App\Http\Controllers\DataFixController::class, 'fixAssociations'])->name('data-fix');
});

/*
|--------------------------------------------------------------------------
| DATA MANAGEMENT ROUTES - FIXED & SIMPLIFIED
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'role:admin,data'])->prefix('data')->name('data.')->group(function () {
    Route::get('/', [DataDashboard::class, 'index'])->name('dashboard');

    // ===== SISWA MANAGEMENT =====
    Route::resource('siswa', SiswaController::class);

    // Search Routes
    Route::match(['GET', 'POST'], 'siswa/search', [SiswaController::class, 'search'])->name('siswa.search');

    // API Testing Routes - FIXED NAMING
    Route::post('siswa/test-connection', [SiswaController::class, 'testApiConnection'])->name('siswa.test-connection');
    Route::post('siswa/test-single-student', [SiswaController::class, 'testApiSingleStudent'])->name('siswa.test-single-student');

    // Import Routes
    Route::get('siswa/import', [SiswaController::class, 'import'])->name('siswa.import');
    Route::post('siswa/import-from-api', [SiswaController::class, 'importFromApi'])->name('siswa.import-from-api');
    Route::post('siswa/import-from-api-ajax', [SiswaController::class, 'importFromApiAjax'])->name('siswa.import-from-api-ajax');
    Route::get('siswa/import-progress', [SiswaController::class, 'getImportProgress'])->name('siswa.import-progress');
    Route::post('siswa/clear-import-progress', [SiswaController::class, 'clearImportProgress'])->name('siswa.clear-import-progress');

    // Sync Routes  
    Route::post('siswa/sync-from-api', [SiswaController::class, 'syncFromApi'])->name('siswa.sync-from-api');
    Route::get('siswa/sync-progress', [SiswaController::class, 'getSyncProgress'])->name('siswa.sync-progress');
    Route::post('siswa/clear-sync-progress', [SiswaController::class, 'clearSyncProgress'])->name('siswa.clear-sync-progress');

    // Export & Stats
    Route::get('siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::get('siswa/stats', [SiswaController::class, 'getStats'])->name('siswa.stats');
    Route::post('siswa/filtered-stats', [SiswaController::class, 'getFilteredStats'])->name('siswa.filtered-stats');

    // Bulk Operations
    Route::post('siswa/bulk-delete', [SiswaController::class, 'bulkDelete'])->name('siswa.bulk-delete');
    Route::post('siswa/bulk-update-rekomendasi', [SiswaController::class, 'bulkUpdateRekomendasi'])->name('siswa.bulk-update-rekomendasi');
    Route::post('siswa/bulk-update-status', [SiswaController::class, 'bulkUpdateStatus'])->name('siswa.bulk-update-status');

    // ===== GURU MANAGEMENT =====
    // PENTING: Tempatkan custom routes SEBELUM resource route

    // Guru search route - PINDAHKAN KE SINI (SEBELUM RESOURCE)
    Route::get('guru/search', [GuruController::class, 'search'])->name('guru.search');
    Route::get('guru/import', [GuruController::class, 'import'])->name('guru.import');
    Route::post('guru/import-process', [GuruController::class, 'processImport'])->name('guru.import.process');
    Route::get('guru/template', [GuruController::class, 'downloadTemplate'])->name('guru.template');
    Route::post('guru/bulk-delete', [GuruController::class, 'bulkDelete'])->name('guru.bulk-delete');
    Route::post('guru/bulk-update-role', [GuruController::class, 'bulkUpdateRole'])->name('guru.bulk-update-role');

    // Resource route harus ditempatkan SETELAH custom routes
    Route::resource('guru', GuruController::class);

    // Route Kelas
    Route::prefix('kelas')->name('kelas.')->group(function () {
        Route::get('/', [KelasController::class, 'index'])->name('index');
        Route::post('/sync', [KelasController::class, 'syncFromSiswa'])->name('sync');
    });
});

/*
|--------------------------------------------------------------------------
| Naskah Management ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:web', 'role:admin,naskah'])->prefix('naskah')->name('naskah.')->group(function () {
    Route::get('/', [NaskahDashboard::class, 'index'])->name('dashboard');

    // Bank Soal routes
    Route::resource('banksoal', BankSoalController::class);

    // Soal routes
    Route::resource('soal', SoalController::class);
    Route::post('soal/bulk-delete', [SoalController::class, 'bulkDelete'])->name('soal.bulk-delete');
    Route::get('soal/{soal}/duplicate', [SoalController::class, 'duplicate'])->name('soal.duplicate');
    Route::get('soal/import', [SoalController::class, 'import'])->name('soal.import');
    Route::get('soal/{soal}/preview', [SoalController::class, 'preview'])->name('soal.preview');

    // Mapel routes
    Route::resource('mapel', MapelController::class);
    Route::put('mapel/{mapel}/status', [MapelController::class, 'updateStatus'])->name('mapel.status');

    // Jadwal Ujian routes
    Route::resource('jadwal', JadwalUjianController::class);
    Route::put('jadwal/{jadwal}/status', [JadwalUjianController::class, 'updateStatus'])->name('jadwal.status');
    Route::post('jadwal/{jadwal}/attach-sesi', [JadwalUjianController::class, 'attachSesi'])->name('jadwal.attach-sesi');
    Route::post('jadwal/{jadwal}/detach-sesi', [JadwalUjianController::class, 'detachSesi'])->name('jadwal.detach-sesi');
    Route::post('jadwal/bulk-action', [JadwalUjianController::class, 'bulkAction'])->name('jadwal.bulk-action');
    Route::post('jadwal/{jadwal}/reassign-sesi', [JadwalUjianController::class, 'reassignSesi'])->name('jadwal.reassign-sesi');
    Route::put('jadwal/{jadwal}/toggle-auto-assign', [JadwalUjianController::class, 'toggleAutoAssign'])->name('jadwal.toggle-auto-assign');
    Route::put('jadwal/{jadwal}/switch-scheduling-mode', [JadwalUjianController::class, 'switchSchedulingMode'])->name('jadwal.switch-scheduling-mode');


    Route::get('/panduan/format-docx', [PanduanController::class, 'formatDocx'])->name('panduan.format-docx');

    // Sesi Ujian routes have been removed as they're replaced by sesi ruangan

    // Hasil Ujian routes
    Route::get('hasil', [HasilUjianController::class, 'index'])->name('hasil.index');
    Route::get('hasil/{hasil}', [HasilUjianController::class, 'show'])->name('hasil.show');
    Route::get('hasil/jadwal/{jadwal}', [HasilUjianController::class, 'byJadwal'])->name('hasil.by-jadwal');
    Route::get('hasil/jadwal/{jadwal}/sesi/{sesi}', [HasilUjianController::class, 'bySesi'])->name('hasil.by-sesi');
    Route::get('hasil/analisis', [HasilUjianController::class, 'analisis'])->name('hasil.analisis');
    Route::post('hasil/export', [HasilUjianController::class, 'export'])->name('hasil.export');
    Route::delete('hasil/{hasil}', [HasilUjianController::class, 'destroy'])->name('hasil.destroy');

    // Old Enrollment routes (if still needed)
    Route::get('enrollment', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'index'])->name('enrollment.index');
    Route::get('enrollment/{mapel}', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'show'])->name('enrollment.show');
    Route::get('enrollment/{mapel}/create', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'create'])->name('enrollment.create');
    Route::post('enrollment/{mapel}', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'store'])->name('enrollment.store');
    Route::delete('enrollment/{mapel}/{siswa}', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'destroy'])->name('enrollment.destroy');
    Route::put('enrollment/{mapel}/{siswa}/status', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'updateStatus'])->name('enrollment.update-status');
    Route::get('enrollment/siswa', [\App\Http\Controllers\Features\Naskah\EnrollmentController::class, 'getSiswaByKelas'])->name('enrollment.get-siswa');

    // New Enrollment Ujian routes
    Route::get('enrollment-ujian/get-sesi-options', [App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class, 'getSesiOptions'])
        ->name('enrollment-ujian.get-sesi-options');
    Route::get('enrollment-ujian/get-siswa-options', [App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class, 'getSiswaOptions'])
        ->name('enrollment-ujian.get-siswa-options');
    Route::post('enrollment-ujian/bulk', [App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class, 'bulkEnrollment'])
        ->name('enrollment-ujian.bulk');
    Route::post('enrollment-ujian/{enrollmentUjian}/generate-token', [App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class, 'generateToken'])
        ->name('enrollment-ujian.generate-token');
    Route::patch('enrollment-ujian/{enrollmentUjian}/status/{status}', [App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class, 'updateStatus'])
        ->name('enrollment-ujian.update-status');
    Route::resource('enrollment-ujian', App\Http\Controllers\Features\Ruangan\EnrollmentUjianController::class);
});

/*
|--------------------------------------------------------------------------
| Pengawas ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'role:admin,pengawas'])->prefix('pengawas')->name('pengawas.')->group(function () {
    Route::get('/', [PengawasDashboard::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Koordinator ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'role:admin,koordinator'])->prefix('koordinator')->name('koordinator.')->group(function () {
    // Dashboard
    Route::get('/', [KoordinatorDashboard::class, 'index'])->name('dashboard');

    // Assignment Management
    Route::prefix('assignment')->name('assignment.')->group(function () {
        Route::get('/', [AssignmentController::class, 'index'])->name('index');
        Route::post('/assign', [AssignmentController::class, 'assign'])->name('assign');
        Route::post('/unassign', [AssignmentController::class, 'unassign'])->name('unassign');
        Route::post('/bulk-assign', [AssignmentController::class, 'bulkAssign'])->name('bulk-assign');
        Route::get('/schedule/{pengawas}/{tanggal}', [AssignmentController::class, 'getSchedule'])->name('schedule');
    });

    // Live Monitoring
    Route::prefix('monitoring')->name('monitoring.')->group(function () {
        Route::get('/', [MonitoringController::class, 'index'])->name('index');
        Route::get('/{sesi}', [MonitoringController::class, 'show'])->name('show');
        Route::post('/message', [MonitoringController::class, 'sendMessage'])->name('message');
        Route::post('/allow-reentry', [MonitoringController::class, 'allowReentry'])->name('allow-reentry');
        Route::get('/student-detail/{studentSession}', [MonitoringController::class, 'studentDetail'])->name('student-detail');
        Route::get('/export', [MonitoringController::class, 'export'])->name('export');
    });

    // Report Management
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::post('/verify', [LaporanController::class, 'verify'])->name('verify');
        Route::post('/bulk-verify', [LaporanController::class, 'bulkVerify'])->name('bulk-verify');
        Route::get('/{beritaAcara}', [LaporanController::class, 'show'])->name('show');
        Route::get('/{beritaAcara}/download', [LaporanController::class, 'download'])->name('download');
        Route::get('/{beritaAcara}/edit', [LaporanController::class, 'edit'])->name('edit');
        Route::put('/{beritaAcara}', [LaporanController::class, 'update'])->name('update');
    });
});

/*
|--------------------------------------------------------------------------
| Ruangan ROUTES
|--------------------------------------------------------------------------
*/

// All ruangan management routes have been moved to routes/ruangan.php file
require __DIR__ . '/ruangan.php';

// Load fallback routes to handle route name mismatches
require __DIR__ . '/fallback.php';

/*
|--------------------------------------------------------------------------
| Guru ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/', [GuruDashboard::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Siswa ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:web', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', [SiswaDashboard::class, 'index'])->name('dashboard');
});

Route::middleware('auth:guru')->prefix('guru-portal')->name('guru.portal.')->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'portalIndex'])->name('dashboard');
});

Route::middleware('auth:siswa')->prefix('siswa-portal')->name('siswa.portal.')->group(function () {
    Route::get('/dashboard', [SiswaDashboard::class, 'portalIndex'])->name('dashboard');
});

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| DOCUMENTATION ROUTES
|--------------------------------------------------------------------------
*/


// Test route for ruangan/template
Route::get('/test-ruangan-template', function () {
    return 'This is a test route for ruangan/template';
});

/*
|--------------------------------------------------------------------------
| API ENDPOINTS FOR AJAX FUNCTIONALITY
|--------------------------------------------------------------------------
*/

Route::middleware('auth:web')->prefix('api')->name('api.')->group(function () {
    // Siswa API endpoints for AJAX
    Route::prefix('siswa')->name('siswa.')->group(function () {
        Route::post('search', [SiswaController::class, 'search'])->name('search');
        Route::post('filter', [SiswaController::class, 'search'])->name('filter');
        Route::get('stats', [SiswaController::class, 'getStats'])->name('stats');
        Route::post('stats-filtered', [SiswaController::class, 'getFilteredStats'])->name('stats.filtered');
        Route::get('kelas-options', [SiswaController::class, 'getKelasOptions'])->name('kelas.options');
        Route::post('bulk-action', [SiswaController::class, 'bulkActions'])->name('bulk.action');
    });

    // General test endpoint
    Route::get('test', function () {
        return response()->json([
            'success' => true,
            'message' => 'API endpoint working',
            'timestamp' => now()->toISOString(),
            'user' => auth()->check() ? auth()->user()->name : 'Not authenticated',
        ]);
    });
});

/*
|--------------------------------------------------------------------------
| DEBUG ROUTES (Remove in production)
|--------------------------------------------------------------------------
*/

if (config('app.debug')) {
    Route::get('/debug-auth', function () {
        if (!auth()->check()) {
            return ['error' => 'Not authenticated'];
        }

        $user = auth()->user();
        return [
            'user' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'No role',
            'guard' => 'web',
            'middleware_working' => true
        ];
    });

    Route::get('/test-soal-image', function () {
        $imageService = app(\App\Services\SoalImageService::class);
        $types = ['pertanyaan', 'pilihan', 'pembahasan'];
        $results = [];

        foreach ($types as $type) {
            $filename = $imageService->createTestImage($type);
            $results[$type] = [
                'filename' => $filename,
                'url' => $filename ? Storage::url('soal/' . $type . '/' . $filename) : null,
                'full_path' => $filename ? storage_path('app/public/soal/' . $type . '/' . $filename) : null,
                'exists' => $filename ? file_exists(storage_path('app/public/soal/' . $type . '/' . $filename)) : false
            ];
        }

        return view('debug.test-images', ['results' => $results]);
    });

    Route::get('/force-logout', function () {
        Auth::logout();
        session()->flush();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/')->with('message', 'Logged out successfully!');
    });

    // // Routes check for debugging
    // Route::get('/debug-routes', function () {
    //     return response()->json([
    //         'siswa_routes' => [
    //             'index' => route('data.siswa.index'),
    //             'search' => route('data.siswa.search'),
    //             'filter' => route('data.siswa.filter'),
    //             'stats' => route('data.siswa.stats'),
    //             'export' => route('data.siswa.export'),
    //             'bulk_delete' => route('data.siswa.bulk-delete'),
    //             'bulk_rekomendasi' => route('data.siswa.bulk-update-rekomendasi'),
    //             'api_search' => route('api.siswa.search'),
    //             'api_stats' => route('api.siswa.stats'),
    //         ],
    //         'note' => 'Enhanced routes with AJAX filtering support'
    //     ]);
    // });

    // Test filter functionality
    Route::get('/test-filter', function () {
        $siswa = \App\Models\Siswa::query()
            ->when(request('search'), function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")
                        ->orWhere('idyayasan', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(request('kelas'), function ($q, $kelas) {
                $q->where('kelas', $kelas);
            })
            ->when(request('status_pembayaran'), function ($q, $status) {
                $q->where('status_pembayaran', $status);
            })
            ->when(request('rekomendasi'), function ($q, $rekomendasi) {
                $q->where('rekomendasi', $rekomendasi);
            })
            ->paginate(10);

        return response()->json([
            'total' => $siswa->total(),
            'current_page' => $siswa->currentPage(),
            'per_page' => $siswa->perPage(),
            'filters_applied' => request()->only(['search', 'kelas', 'status_pembayaran', 'rekomendasi']),
            'sample_data' => $siswa->items(),
        ]);
    })->middleware('auth:web');
}
