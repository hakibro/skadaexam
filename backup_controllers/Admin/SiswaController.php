<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Imports\SiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class SiswaController extends Controller
{
    /**
     * Display a listing of students with filters
     */
    public function index(Request $request)
    {
        $query = Siswa::query();

        // Apply filters
        if ($request->filled('q')) {
            $search = $request->get('q');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('idyayasan', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('kelas', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('status_pembayaran', $request->get('payment_status'));
        }

        if ($request->filled('rekomendasi')) {
            $query->where('rekomendasi', $request->get('rekomendasi'));
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->get('sync_status'));
        }

        // Pagination
        $perPage = $request->get('per_page', 25);
        $siswas = $query->latest()->paginate($perPage);

        return view('features.data.siswa.index', compact('siswas'));
    }

    /**
     * AJAX search for live filtering
     */
    public function search(Request $request)
    {
        try {
            $query = Siswa::query();

            // Apply filters
            if ($request->filled('q')) {
                $search = $request->get('q');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'LIKE', "%{$search}%")
                        ->orWhere('idyayasan', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('kelas', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('payment_status')) {
                $query->where('status_pembayaran', $request->get('payment_status'));
            }

            if ($request->filled('rekomendasi')) {
                $query->where('rekomendasi', $request->get('rekomendasi'));
            }

            if ($request->filled('sync_status')) {
                $query->where('sync_status', $request->get('sync_status'));
            }

            // Get results
            $perPage = $request->get('per_page', 25);
            $siswas = $query->latest()->paginate($perPage);

            // Generate HTML for table and pagination
            $tableHtml = view('features.data.siswa.partials.table', compact('siswas'))->render();
            $paginationHtml = view('features.data.siswa.partials.pagination', compact('siswas'))->render();

            return response()->json([
                'success' => true,
                'html' => $tableHtml,
                'pagination' => $paginationHtml,
                'count' => $siswas->total(),
                'showing' => $siswas->count(),
                'total' => $siswas->total()
            ]);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('features.data.siswa.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'idyayasan' => 'required|unique:siswa,idyayasan|max:20',
            'nama' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'kelas' => 'nullable|string|max:100',
            'rekomendasi' => 'required|in:ya,tidak',
            'catatan_rekomendasi' => 'nullable|string|max:500'
        ]);

        $data = $request->all();

        // AUTO-GENERATE EMAIL based on nama or idyayasan
        $data['email'] = $this->generateEmail($data['nama'] ?? null, $data['idyayasan']);

        // Set default values
        $data['password'] = bcrypt('password'); // Default password
        $data['sync_status'] = 'pending';
        $data['user_id'] = auth()->id();

        // Set default rekomendasi if not provided (shouldn't happen with form validation, but just in case)
        if (empty($data['rekomendasi'])) {
            $data['rekomendasi'] = 'tidak';
        }

        $siswa = Siswa::create($data);

        return redirect()->route('data.siswa.index')
            ->with('success', 'Siswa berhasil ditambahkan: ' . ($siswa->nama ?: $siswa->idyayasan) . ' dengan email: ' . $siswa->email);
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
        return view('features.data.siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
        return view('features.data.siswa.edit', compact('siswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nama' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:siswa,email,' . $siswa->id,
            'kelas' => 'nullable|string|max:100',
            'rekomendasi' => 'required|in:ya,tidak',
            'catatan_rekomendasi' => 'nullable|string|max:500'
        ]);

        $data = $request->all();

        // If email is empty, regenerate it
        if (empty($data['email'])) {
            $data['email'] = $this->generateEmail($data['nama'] ?? null, $siswa->idyayasan);
        }

        $siswa->update($data);

        return redirect()->route('data.siswa.index')
            ->with('success', 'Siswa berhasil diupdate: ' . ($siswa->nama ?: $siswa->idyayasan));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        $nama = $siswa->nama ?: $siswa->idyayasan;
        $siswa->delete();

        return redirect()->route('data.siswa.index')
            ->with('success', "Siswa {$nama} berhasil dihapus");
    }

    /**
     * Generate email based on nama or idyayasan
     */
    private function generateEmail($nama = null, $idyayasan)
    {
        if (!empty($nama)) {
            // Convert nama to email format
            $emailPrefix = strtolower(str_replace(' ', '.', $nama));
            // Remove special characters and keep only letters, numbers, dots
            $emailPrefix = preg_replace('/[^a-z0-9.]/', '', $emailPrefix);
            // Remove multiple dots
            $emailPrefix = preg_replace('/\.+/', '.', $emailPrefix);
            // Remove leading/trailing dots
            $emailPrefix = trim($emailPrefix, '.');
        } else {
            // Use idyayasan as fallback
            $emailPrefix = strtolower($idyayasan);
        }

        // Ensure uniqueness by checking if email already exists
        $baseEmail = $emailPrefix . '@smkdata.sch.id';
        $counter = 1;
        $finalEmail = $baseEmail;

        while (Siswa::where('email', $finalEmail)->exists()) {
            $finalEmail = $emailPrefix . $counter . '@smkdata.sch.id';
            $counter++;
        }

        return $finalEmail;
    }

    /**
     * Generate email preview for AJAX
     */
    public function previewEmail(Request $request)
    {
        $nama = $request->get('nama');
        $idyayasan = $request->get('idyayasan');

        if (empty($nama) && empty($idyayasan)) {
            return response()->json([
                'success' => false,
                'message' => 'Nama or ID Yayasan required'
            ]);
        }

        $email = $this->generateEmail($nama, $idyayasan);

        return response()->json([
            'success' => true,
            'email' => $email
        ]);
    }

    /**
     * Show import form
     */
    public function import()
    {
        $totalSiswa = Siswa::count();
        return view('features.data.siswa.import', compact('totalSiswa'));
    }

    /**
     * Process Excel import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // 10MB max
        ]);

        try {
            $import = new SiswaImport();
            Excel::import($import, $request->file('file'));

            $results = $import->getResults();

            // Store results in session for display
            session(['import_results' => $results]);

            return redirect()->route('data.siswa.import-results')
                ->with('success', "Import completed: {$results['success_count']} success, {$results['error_count']} errors");
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show import results
     */
    public function showImportResults()
    {
        $results = session('import_results', [
            'total_rows' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'errors' => [],
            'created' => [],
            'updated' => []
        ]);

        return view('features.data.siswa.import-results', compact('results'));
    }

    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="template_siswa.csv"'
        ];

        $csvContent = "idyayasan,nama,kelas,rekomendasi,catatan_rekomendasi\n";
        $csvContent .= "190001,John Doe,XII IPA 1,ya,Siswa berprestasi\n";
        $csvContent .= "190002,Jane Smith,XII IPS 1,tidak,Perlu pembinaan\n";

        return response($csvContent, 200, $headers);
    }

    /**
     * Test sync page
     */
    public function testSync()
    {
        $config = [
            'base_url' => config('services.sisda.base_url', env('SISDA_API_BASE_URL')),
            'payment_endpoint' => '/payment/check',
            'timeout' => config('services.sisda.timeout', env('SISDA_API_TIMEOUT', 15)),
            'retry_times' => config('services.sisda.retry_times', env('SISDA_API_RETRY_TIMES', 2))
        ];

        $totalSiswa = Siswa::count();

        return view('features.data.siswa.test-sync', compact('config', 'totalSiswa'));
    }

    /**
     * Sync all payment data
     */
    public function syncAllSisda()
    {
        try {
            $startTime = microtime(true);
            $siswas = Siswa::whereNotNull('idyayasan')->get();

            if ($siswas->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students found to sync'
                ]);
            }

            $stats = [
                'total' => $siswas->count(),
                'success' => 0,
                'failed' => 0,
                'names_updated' => 0,
                'total_time' => 0
            ];

            foreach ($siswas as $siswa) {
                try {
                    // Mock API call - replace with actual SISDA API integration
                    $siswa->status_pembayaran = $this->mockApiCall($siswa->idyayasan);
                    $siswa->payment_last_check = now();
                    $siswa->sync_status = 'synced';
                    $siswa->save();

                    $stats['success']++;

                    // Small delay to prevent overwhelming
                    usleep(100000); // 0.1 seconds

                } catch (\Exception $e) {
                    $siswa->sync_status = 'failed';
                    $siswa->sync_error = $e->getMessage();
                    $siswa->save();
                    $stats['failed']++;
                }
            }

            $stats['total_time'] = (microtime(true) - $startTime) * 1000;

            return response()->json([
                'success' => true,
                'message' => "Sync completed: {$stats['success']} success, {$stats['failed']} failed",
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Sync all error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mock API call for testing
     */
    private function mockApiCall($idyayasan)
    {
        $statuses = ['Lunas', 'Belum Lunas', 'Cicilan'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Test sync single student
     */
    public function testSyncSingle(Request $request)
    {
        $request->validate([
            'idyayasan' => 'required|string'
        ]);

        try {
            $startTime = microtime(true);
            $idyayasan = $request->idyayasan;

            // Check if exists in database
            $siswa = Siswa::where('idyayasan', $idyayasan)->first();

            // Mock API response
            $apiData = [
                'payment_status' => $this->mockApiCall($idyayasan),
                'siswa_data' => $siswa ? [
                    'nama' => $siswa->nama,
                    'kelas' => $siswa->kelas,
                    'rekomendasi' => $siswa->rekomendasi
                ] : null
            ];

            $duration = (microtime(true) - $startTime) * 1000;

            return response()->json([
                'success' => true,
                'data' => [
                    'idyayasan' => $idyayasan,
                    'exists_in_db' => $siswa ? true : false,
                    'payment_status' => $apiData['payment_status'],
                    'siswa_data' => $apiData['siswa_data']
                ],
                'duration' => round($duration)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'duration' => round((microtime(true) - $startTime) * 1000)
            ], 500);
        }
    }

    /**
     * Test sync multiple students
     */
    public function testSyncMultiple(Request $request)
    {
        $request->validate([
            'limit' => 'required|integer|min:1|max:50'
        ]);

        try {
            $startTime = microtime(true);
            $limit = $request->limit;

            $siswas = Siswa::whereNotNull('idyayasan')->limit($limit)->get();

            $stats = [
                'total_tested' => $siswas->count(),
                'success_count' => 0,
                'fail_count' => 0
            ];

            $sampleData = [];

            foreach ($siswas as $siswa) {
                try {
                    $studentStartTime = microtime(true);
                    $paymentStatus = $this->mockApiCall($siswa->idyayasan);
                    $studentDuration = (microtime(true) - $studentStartTime) * 1000;

                    $sampleData[] = [
                        'idyayasan' => $siswa->idyayasan,
                        'nama' => $siswa->nama,
                        'success' => true,
                        'payment_status' => $paymentStatus,
                        'duration' => round($studentDuration)
                    ];

                    $stats['success_count']++;
                } catch (\Exception $e) {
                    $sampleData[] = [
                        'idyayasan' => $siswa->idyayasan,
                        'nama' => $siswa->nama,
                        'success' => false,
                        'error' => $e->getMessage(),
                        'duration' => 0
                    ];

                    $stats['fail_count']++;
                }
            }

            $totalDuration = (microtime(true) - $startTime) * 1000;

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'sample_data' => $sampleData,
                'duration' => round($totalDuration)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk sync selected students
     */
    public function bulkSync(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id'
        ]);

        try {
            $siswas = Siswa::whereIn('id', $request->siswa_ids)->get();
            $success = 0;
            $failed = 0;

            foreach ($siswas as $siswa) {
                try {
                    $siswa->status_pembayaran = $this->mockApiCall($siswa->idyayasan);
                    $siswa->payment_last_check = now();
                    $siswa->sync_status = 'synced';
                    $siswa->save();
                    $success++;
                } catch (\Exception $e) {
                    $siswa->sync_status = 'failed';
                    $siswa->sync_error = $e->getMessage();
                    $siswa->save();
                    $failed++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk sync completed: {$success} success, {$failed} failed"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk sync failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete selected students
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id'
        ]);

        try {
            $count = Siswa::whereIn('id', $request->siswa_ids)->count();
            Siswa::whereIn('id', $request->siswa_ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} students deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk delete failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
