<?php
// filepath: app\Http\Controllers\Features\Data\SiswaController.php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\SikeuApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    protected $sikeuApiService;
    protected $batchSize = 50; // Default batch size

    public function __construct(SikeuApiService $sikeuApiService)
    {
        $this->sikeuApiService = $sikeuApiService;
    }

    /**
     * Display a listing of siswa
     */
    public function index(Request $request)
    {
        $query = Siswa::with('kelas'); // Eager load kelas data for better performance

        // Apply filters if present
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->get('kelas_id'));
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->get('status_pembayaran'));
        }

        if ($request->filled('rekomendasi')) {
            $query->where('rekomendasi', $request->get('rekomendasi'));
        }

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $siswas = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $siswas->appends($request->query());

        // Get available kelas for filter dropdown using more efficient query
        $availableKelas = \App\Models\Kelas::select('id', 'nama_kelas')
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('siswa')
                    ->whereColumn('siswa.kelas_id', 'kelas.id');
            })
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');

        // Get total count for empty state detection
        $totalSiswa = Siswa::count();

        return view('features.data.siswa.index', compact('siswas', 'availableKelas', 'totalSiswa'));
    }

    /**
     * AJAX search for siswa - WITH IMPROVED PAGINATION SUPPORT
     */
    public function search(Request $request)
    {
        $query = Siswa::with('kelas'); // Eager load kelas data for better performance

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->get('kelas_id'));
        }

        if ($request->filled('status_pembayaran')) {
            $query->where('status_pembayaran', $request->get('status_pembayaran'));
        }

        if ($request->filled('rekomendasi')) {
            $query->where('rekomendasi', $request->get('rekomendasi'));
        }

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $siswas = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // PENTING: Tambahkan semua parameter request ke pagination links
        $siswas->appends($request->except('_token'));

        // Calculate stats from the query
        $stats = [
            'total' => $siswas->total(),
            'showing' => $siswas->count(),
        ];

        // If AJAX request, return JSON response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'table' => view('features.data.siswa.partials.table', ['siswas' => $siswas])->render(),
                    'pagination' => view('features.data.siswa.partials.pagination', ['siswas' => $siswas])->render(),
                    'stats' => $stats
                ]
            ]);
        }

        // Untuk non-AJAX, tampilkan view dengan data
        $availableKelas = \App\Models\Kelas::select('id', 'nama_kelas')
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('siswa')
                    ->whereColumn('siswa.kelas_id', 'kelas.id');
            })
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');

        return view('features.data.siswa.index', compact('siswas', 'availableKelas'));
    }

    /**
     * Display the specified siswa
     */
    public function show(Siswa $siswa)
    {
        return view('features.data.siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing siswa (only rekomendasi)
     */
    public function edit(Siswa $siswa)
    {
        return view('features.data.siswa.edit', compact('siswa'));
    }

    /**
     * Update the specified siswa (only rekomendasi)
     */
    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'rekomendasi' => 'required|in:ya,tidak',
            'catatan_rekomendasi' => 'nullable|string|max:500',
        ]);

        $siswa->update($validated);

        return redirect()->route('data.siswa.show', $siswa)
            ->with('success', 'Rekomendasi siswa updated successfully!');
    }

    /**
     * Remove the specified siswa
     */
    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('data.siswa.index')
            ->with('success', 'Siswa deleted successfully!');
    }

    /**
     * Show import API page
     */
    public function import()
    {
        $totalSiswa = Siswa::count();
        return view('features.data.siswa.import', compact('totalSiswa'));
    }

    /**
     * Import siswa data from SIKEU API
     */
    public function importFromApi()
    {
        try {
            Log::info('Starting SIKEU API import process');

            // Fetch data from API
            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                Log::error('API fetch failed', ['error' => $apiResult['error']]);
                return back()->with('error', 'API Error: ' . $apiResult['error']);
            }

            $apiData = $apiResult['data'];

            if (empty($apiData)) {
                return back()->with('warning', 'No data received from API');
            }

            $results = [
                'total_records' => count($apiData),
                'created_kelas' => 0,
                'updated_kelas' => 0,
                'created_siswa' => 0,
                'updated_siswa' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            Log::info('Processing API data', [
                'total_records' => $results['total_records'],
                'sample_data' => isset($apiData[0]) ? $apiData[0] : null
            ]);

            DB::beginTransaction();

            // LANGKAH 1: Ekstrak data kelas unik dari API
            $uniqueKelas = $this->extractUniqueKelasFromApiData($apiData);

            Log::info('Extracted unique kelas', [
                'count' => count($uniqueKelas),
                'kelas_list' => array_keys($uniqueKelas)
            ]);

            // LANGKAH 2: Simpan data kelas ke database
            $kelasResults = $this->processKelasData($uniqueKelas);
            $results['created_kelas'] = $kelasResults['created'];
            $results['updated_kelas'] = $kelasResults['updated'];
            $results['errors'] = array_merge($results['errors'], $kelasResults['errors']);

            // Refresh kelas data untuk langkah berikutnya
            $allKelas = \App\Models\Kelas::pluck('id', 'nama_kelas')->toArray();

            Log::info('Kelas data saved', [
                'created' => $results['created_kelas'],
                'updated' => $results['updated_kelas'],
                'available_kelas' => count($allKelas)
            ]);

            // LANGKAH 3: Proses data siswa
            $siswaResults = $this->processSiswaData($apiData, $allKelas);
            $results['created_siswa'] = $siswaResults['created'];
            $results['updated_siswa'] = $siswaResults['updated'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            DB::commit();

            Log::info('SIKEU API import completed', $results);

            // Buat pesan sukses
            $message = "API Import completed successfully! ";
            $message .= "Created kelas: {$results['created_kelas']}, Updated kelas: {$results['updated_kelas']}, ";
            $message .= "Created siswa: {$results['created_siswa']}, Updated siswa: {$results['updated_siswa']}";

            if ($results['skipped'] > 0) {
                $message .= ", Skipped: {$results['skipped']}";
            }

            return redirect()->route('data.siswa.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('SIKEU API import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'API Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Extract unique kelas data from API response
     * 
     * @param array $apiData The API data array
     * @return array An array of unique kelas data
     */
    private function extractUniqueKelasFromApiData(array $apiData): array
    {
        $uniqueKelas = [];

        foreach ($apiData as $studentData) {
            // Pastikan kelas tidak kosong
            if (!empty($studentData['kelas'])) {
                $kelasName = trim($studentData['kelas']);
                $uniqueKelas[$kelasName] = [
                    'nama_kelas' => $kelasName,
                    'tingkat' => $this->extractTingkatFromKelas($kelasName),
                    'jurusan' => $this->extractJurusanFromKelas($kelasName)
                ];
            }
        }

        return $uniqueKelas;
    }

    /**
     * Process and save kelas data to database
     * 
     * @param array $uniqueKelas Array of unique kelas data
     * @return array Results of processing
     */
    private function processKelasData(array $uniqueKelas): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        foreach ($uniqueKelas as $kelasName => $kelasData) {
            try {
                $existingKelas = \App\Models\Kelas::where('nama_kelas', $kelasName)->first();

                if ($existingKelas) {
                    // Update existing kelas if needed
                    if (
                        $existingKelas->tingkat !== $kelasData['tingkat'] ||
                        $existingKelas->jurusan !== $kelasData['jurusan']
                    ) {
                        $existingKelas->update([
                            'tingkat' => $kelasData['tingkat'],
                            'jurusan' => $kelasData['jurusan']
                        ]);

                        $results['updated']++;
                    }
                } else {
                    // Create new kelas
                    \App\Models\Kelas::create($kelasData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                Log::error("Error processing kelas {$kelasName}", [
                    'error' => $e->getMessage(),
                ]);
                $results['errors'][] = [
                    'kelas' => $kelasName,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process and save siswa data to database
     * 
     * @param array $apiData The API data array
     * @param array $allKelas Lookup array of kelas ID by name
     * @return array Results of processing
     */
    private function processSiswaData(array $apiData, array $allKelas): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($apiData as $index => $studentData) {
            try {
                // Validasi data yang diperlukan
                if (empty($studentData['idyayasan'])) {
                    $results['errors'][] = [
                        'index' => $index,
                        'error' => 'Missing idyayasan'
                    ];
                    $results['skipped']++;
                    continue;
                }

                // Dapatkan kelas_id
                $kelasId = null;
                if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                    $kelasId = $allKelas[trim($studentData['kelas'])];
                }

                // Cek apakah siswa sudah ada
                $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                if ($existingSiswa) {
                    // Update siswa yang sudah ada (tetap simpan rekomendasi dan catatan_rekomendasi)
                    $updateData = [
                        'nama' => $studentData['nama'] ?? $existingSiswa->nama,
                        'kelas_id' => $kelasId ?? $existingSiswa->kelas_id,
                        'status_pembayaran' => $studentData['status_pembayaran'] ?? $existingSiswa->status_pembayaran,
                        // Tetap simpan rekomendasi dan catatan_rekomendasi yang sudah ada
                    ];

                    $existingSiswa->update($updateData);
                    $results['updated']++;

                    Log::info("Updated student: {$studentData['idyayasan']}");
                } else {
                    // Buat siswa baru
                    $createData = [
                        'idyayasan' => $studentData['idyayasan'],
                        'nama' => $studentData['nama'] ?? null,
                        'kelas_id' => $kelasId,
                        'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                        'email' => $studentData['email'] ?? $this->generateEmail($studentData['idyayasan']),
                        'password' => bcrypt('password'),
                        'rekomendasi' => 'tidak', // Default value
                        'catatan_rekomendasi' => null,
                    ];

                    Siswa::create($createData);
                    $results['created']++;

                    Log::info("Created student: {$studentData['idyayasan']}");
                }
            } catch (\Exception $e) {
                Log::error("Error processing student {$index}", [
                    'error' => $e->getMessage(),
                    'student_data' => $studentData
                ]);

                $results['errors'][] = [
                    'idyayasan' => $studentData['idyayasan'] ?? "Unknown (index: {$index})",
                    'error' => $e->getMessage()
                ];
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Extract tingkat (level) from kelas name
     */
    protected function extractTingkatFromKelas($kelasName)
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        // Check format with space first (most common in API data like "X DPIB -", "X DKV 2")
        // Order is important: check XII first, then XI, then X
        if (strpos($upperKelas, 'XII ') === 0) {
            return 'XII';
        } elseif (strpos($upperKelas, 'XI ') === 0) {
            return 'XI';
        } elseif (strpos($upperKelas, 'X ') === 0) {
            return 'X';
        }

        // Handle special case for "X - TEI" format (with dash)
        if (preg_match('/^(X|XI|XII)\s*-/i', $kelasName, $matches)) {
            return strtoupper($matches[1]); // Return the part before dash (X, XI, XII)
        }

        // Check exact matches
        if ($upperKelas === 'XII') {
            return 'XII';
        } elseif ($upperKelas === 'XI') {
            return 'XI';
        } elseif ($upperKelas === 'X') {
            return 'X';
        }

        // Handle special cases for strings without spaces
        // These special cases need to be explicitly handled to avoid incorrect matches
        if (in_array($upperKelas, ['XIIPA1', 'XIIPS1', 'XIIPS2', 'XIIPA2', 'XIIPA3', 'XIIPS3'])) {
            return 'XI';
        }

        // For other formats without spaces, check prefixes carefully
        if (substr($upperKelas, 0, 3) === 'XII') {
            return 'XII';
        } elseif (substr($upperKelas, 0, 2) === 'XI') {
            return 'XI';
        } elseif (substr($upperKelas, 0, 1) === 'X') {
            return 'X';
        }

        // If no valid tingkat pattern is found, return null or default
        // Using null instead of a substring so we can identify invalid formats
        return null;
    }

    /**
     * Extract jurusan from kelas name
     */
    protected function extractJurusanFromKelas($kelasName)
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        // Handle special case for "X - TEI" format (with dash)
        if (preg_match('/^(X|XI|XII)\s*-\s*([A-Z]+)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]); // Return the part after dash (TEI)
        }

        // For new format like "X DPIB -", "X DKV 2", "X BD 2"
        if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]); // Return the jurusan part (DPIB, DKV, BD, etc.)
        }

        // Traditional patterns for IPA, IPS, etc.
        $patterns = [
            '/IPA\s*\d+/i' => 'IPA',
            '/IPS\s*\d+/i' => 'IPS',
            '/MIPA\s*\d+/i' => 'MIPA',
            '/BAHASA\s*\d+/i' => 'BAHASA',
            '/AGAMA\s*\d+/i' => 'AGAMA'
        ];

        foreach ($patterns as $pattern => $jurusan) {
            if (preg_match($pattern, $kelasName)) {
                return $jurusan;
            }
        }

        // Handle pattern without number (e.g. "XII IPA")
        $jurusanPatterns = [
            '/IPA(?!\w)/i' => 'IPA',
            '/IPS(?!\w)/i' => 'IPS',
            '/MIPA(?!\w)/i' => 'MIPA',
            '/BAHASA(?!\w)/i' => 'BAHASA',
            '/AGAMA(?!\w)/i' => 'AGAMA'
        ];

        foreach ($jurusanPatterns as $pattern => $jurusan) {
            if (preg_match($pattern, $kelasName)) {
                return $jurusan;
            }
        }

        // For combined strings without spaces like "XIIPA1"
        if (preg_match('/(X|XI|XII)(IPA|IPS|MIPA|BAHASA|AGAMA)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]);
        }

        // Default jurusan if not found
        return 'UMUM';
    }

    /**
     * Generate email for siswa (helper method)
     */
    private function generateEmail($idyayasan)
    {
        return $idyayasan . '@smkdata.sch.id';
    }

    /**
     * Test API connection
     */
    public function testApiConnection()
    {
        try {
            $result = $this->sikeuApiService->testConnection();

            return response()->json([
                'success' => $result['success'],
                'message' => $result['success'] ? 'Connection successful' : 'Connection failed',
                'error' => $result['error'] ?? null,
                'response_time' => $result['response_time'] ?? 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test API single student fetch
     */
    public function testApiSingleStudent()
    {
        try {
            $result = $this->sikeuApiService->testFetchSingleStudent();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Single student test failed'
            ]);
        }
    }

    /**
     * Check API status
     */


    /**
     * Import siswa data from SIKEU API (AJAX)
     */
    public function importFromApiAjax(Request $request)
    {
        try {
            Log::info('Starting AJAX SIKEU API import process');

            // Initialize session for progress tracking
            session(['import_progress' => 0]);
            session(['import_status' => 'starting']);
            session(['import_message' => 'Initializing import...']);

            // Fetch data from API
            session(['import_message' => 'Connecting to SIKEU API...']);
            session(['import_progress' => 10]);

            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                session(['import_status' => 'error']);
                session(['import_message' => 'API Error: ' . $apiResult['error']]);

                return response()->json([
                    'success' => false,
                    'error' => 'API Error: ' . $apiResult['error'],
                    'step' => 'api_connection'
                ]);
            }

            $apiData = $apiResult['data'];

            if (empty($apiData)) {
                session(['import_status' => 'warning']);
                session(['import_message' => 'No data received from API']);

                return response()->json([
                    'success' => false,
                    'error' => 'No data received from API',
                    'step' => 'data_validation'
                ]);
            }

            session(['import_message' => 'Processing class data...']);
            session(['import_progress' => 15]);

            $results = [
                'total_records' => count($apiData),
                'created_kelas' => 0,
                'updated_kelas' => 0,
                'created_siswa' => 0,
                'updated_siswa' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            // LANGKAH 1: Ekstrak data kelas unik dari API (15% - 25%)
            session(['import_message' => 'Extracting unique class data...']);
            $uniqueKelas = $this->extractUniqueKelasFromApiData($apiData);
            session(['import_progress' => 25]);

            Log::info('Extracted unique kelas', [
                'count' => count($uniqueKelas),
                'kelas_list' => array_keys($uniqueKelas)
            ]);

            // LANGKAH 2: Simpan data kelas ke database (25% - 35%)
            session(['import_message' => 'Saving class data to database...']);

            // Track progress untuk pemrosesan kelas
            $progressStepKelas = 10 / max(count($uniqueKelas), 1);
            $currentProgress = 25;

            // Proses dan simpan data kelas
            $kelasResults = ['created' => 0, 'updated' => 0, 'errors' => []];
            foreach ($uniqueKelas as $kelasName => $kelasData) {
                try {
                    $currentProgress += $progressStepKelas;
                    session(['import_progress' => min(35, $currentProgress)]);

                    $existingKelas = \App\Models\Kelas::where('nama_kelas', $kelasName)->first();

                    if ($existingKelas) {
                        // Update kelas yang sudah ada jika diperlukan
                        if (
                            $existingKelas->tingkat !== $kelasData['tingkat'] ||
                            $existingKelas->jurusan !== $kelasData['jurusan']
                        ) {
                            $existingKelas->update([
                                'tingkat' => $kelasData['tingkat'],
                                'jurusan' => $kelasData['jurusan']
                            ]);

                            $kelasResults['updated']++;
                        }
                    } else {
                        // Buat kelas baru
                        \App\Models\Kelas::create($kelasData);
                        $kelasResults['created']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing kelas {$kelasName}", [
                        'error' => $e->getMessage(),
                    ]);
                    $kelasResults['errors'][] = [
                        'kelas' => $kelasName,
                        'error' => $e->getMessage()
                    ];
                }
            }

            $results['created_kelas'] = $kelasResults['created'];
            $results['updated_kelas'] = $kelasResults['updated'];
            $results['errors'] = array_merge($results['errors'], $kelasResults['errors']);

            // Refresh data kelas untuk langkah berikutnya
            $allKelas = \App\Models\Kelas::pluck('id', 'nama_kelas')->toArray();

            session(['import_message' => 'Processing student data...']);
            session(['import_progress' => 35]);

            Log::info('Kelas data saved', [
                'created' => $results['created_kelas'],
                'updated' => $results['updated_kelas'],
                'available_kelas' => count($allKelas)
            ]);

            // LANGKAH 3: Proses data siswa (35% - 95%)
            $progressStepSiswa = 55 / max(count($apiData), 1);
            $currentProgress = 35;

            // Proses dan simpan data siswa
            $siswaResults = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
            foreach ($apiData as $index => $studentData) {
                try {
                    // Update progress
                    $currentProgress += $progressStepSiswa;
                    session(['import_progress' => min(90, $currentProgress)]);
                    session(['import_message' => "Processing student " . ($index + 1) . " of " . count($apiData)]);

                    // Validasi data yang diperlukan
                    if (empty($studentData['idyayasan'])) {
                        $siswaResults['errors'][] = [
                            'index' => $index,
                            'error' => 'Missing idyayasan'
                        ];
                        $siswaResults['skipped']++;
                        continue;
                    }

                    // Dapatkan kelas_id
                    $kelasId = null;
                    if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                        $kelasId = $allKelas[trim($studentData['kelas'])];
                    }

                    // Cek apakah siswa sudah ada
                    $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                    if ($existingSiswa) {
                        // Update siswa yang sudah ada (simpan rekomendasi dan catatan_rekomendasi)
                        $updateData = [
                            'nama' => $studentData['nama'] ?? $existingSiswa->nama,
                            'kelas_id' => $kelasId ?? $existingSiswa->kelas_id,
                            'status_pembayaran' => $studentData['status_pembayaran'] ?? $existingSiswa->status_pembayaran,
                        ];

                        $existingSiswa->update($updateData);
                        $siswaResults['updated']++;
                    } else {
                        // Buat siswa baru
                        $createData = [
                            'idyayasan' => $studentData['idyayasan'],
                            'nama' => $studentData['nama'] ?? null,
                            'kelas_id' => $kelasId,
                            'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                            'email' => $studentData['email'] ?? $this->generateEmail($studentData['idyayasan']),
                            'password' => bcrypt('password'),
                            'rekomendasi' => 'tidak',
                            'catatan_rekomendasi' => null,
                        ];

                        Siswa::create($createData);
                        $siswaResults['created']++;
                    }
                } catch (\Exception $e) {
                    $siswaResults['errors'][] = [
                        'idyayasan' => $studentData['idyayasan'] ?? "Unknown (index: {$index})",
                        'error' => $e->getMessage()
                    ];
                    $siswaResults['skipped']++;
                }
            }

            $results['created_siswa'] = $siswaResults['created'];
            $results['updated_siswa'] = $siswaResults['updated'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            session(['import_progress' => 95]);
            session(['import_message' => 'Finalizing import...']);

            DB::commit();

            session(['import_progress' => 100]);
            session(['import_status' => 'completed']);

            $message = "Import completed! Created kelas: {$results['created_kelas']}, Updated kelas: {$results['updated_kelas']}, ";
            $message .= "Created siswa: {$results['created_siswa']}, Updated siswa: {$results['updated_siswa']}";

            if ($results['skipped'] > 0) {
                $message .= ", Skipped: {$results['skipped']}";
            }

            session(['import_message' => $message]);

            Log::info('AJAX SIKEU API import completed', $results);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            session(['import_status' => 'error']);
            session(['import_message' => 'Import failed: ' . $e->getMessage()]);

            Log::error('AJAX SIKEU API import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage(),
                'exception' => get_class($e)
            ]);
        }
    }
    /**
     * Get import progress for AJAX polling
     */
    public function getImportProgress()
    {
        return response()->json([
            'progress' => session('import_progress', 0),
            'status' => session('import_status', 'idle'),
            'message' => session('import_message', 'Ready to import')
        ]);
    }

    /**
     * Clear import progress session
     */
    public function clearImportProgress()
    {
        session()->forget(['import_progress', 'import_status', 'import_message']);

        return response()->json([
            'success' => true,
            'message' => 'Progress cleared'
        ]);
    }

    /**
     * Sync existing data with SIKEU API
     */
    public function syncFromApi(Request $request)
    {
        try {
            Log::info('Starting SIKEU API sync process');

            // Initialize session for progress tracking
            session(['sync_progress' => 0]);
            session(['sync_status' => 'starting']);
            session(['sync_message' => 'Starting sync process...']);

            // Fetch data from API
            session(['sync_message' => 'Fetching latest data from SIKEU API...']);
            session(['sync_progress' => 10]);

            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                session(['sync_status' => 'error']);
                session(['sync_message' => 'API Error: ' . $apiResult['error']]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'API Error: ' . $apiResult['error']
                    ]);
                }
                return back()->with('error', 'API Error: ' . $apiResult['error']);
            }

            $apiData = $apiResult['data'];

            if (empty($apiData)) {
                session(['sync_status' => 'warning']);
                session(['sync_message' => 'No data received from API']);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'warning' => 'No data received from API'
                    ]);
                }
                return back()->with('warning', 'No data received from API');
            }

            session(['sync_message' => 'Extracting class data...']);
            session(['sync_progress' => 20]);

            $results = [
                'total_api_records' => count($apiData),
                'total_db_records' => Siswa::count(),
                'created_kelas' => 0,
                'updated_kelas' => 0,
                'updated_siswa' => 0,
                'created_siswa' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            // LANGKAH 1: Ekstrak data kelas unik dari API
            $uniqueKelas = $this->extractUniqueKelasFromApiData($apiData);

            Log::info('Extracted unique kelas for sync', [
                'count' => count($uniqueKelas),
                'kelas_list' => array_keys($uniqueKelas)
            ]);

            session(['sync_message' => 'Synchronizing class data...']);
            session(['sync_progress' => 30]);

            // LANGKAH 2: Simpan data kelas ke database
            $kelasResults = $this->processKelasData($uniqueKelas);
            $results['created_kelas'] = $kelasResults['created'];
            $results['updated_kelas'] = $kelasResults['updated'];
            $results['errors'] = array_merge($results['errors'], $kelasResults['errors']);

            // Refresh kelas data for the next step
            $allKelas = \App\Models\Kelas::pluck('id', 'nama_kelas')->toArray();

            session(['sync_message' => 'Analyzing student data differences...']);
            session(['sync_progress' => 40]);

            // Create lookup array for API data
            $apiStudents = collect($apiData)->keyBy('idyayasan');
            $existingStudents = Siswa::pluck('idyayasan')->toArray();

            session(['sync_message' => 'Synchronizing student data...']);
            session(['sync_progress' => 50]);

            // LANGKAH 3: Proses data siswa
            $progressStep = 40 / max(count($apiData), 1);
            $currentProgress = 50;

            $siswaResults = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

            foreach ($apiData as $index => $studentData) {
                try {
                    $currentProgress += $progressStep;
                    session(['sync_progress' => min(90, $currentProgress)]);
                    session(['sync_message' => "Syncing student " . ($index + 1) . " of " . count($apiData)]);

                    if (empty($studentData['idyayasan'])) {
                        $siswaResults['errors'][] = "Missing idyayasan for student at index {$index}";
                        $siswaResults['skipped']++;
                        continue;
                    }

                    // Dapatkan kelas_id dari lookup array
                    $kelasId = null;
                    if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                        $kelasId = $allKelas[trim($studentData['kelas'])];
                    }

                    $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                    if ($existingSiswa) {
                        // Cek apakah data perlu diupdate
                        $needsUpdate = false;
                        $updateData = [];

                        if ($existingSiswa->nama !== ($studentData['nama'] ?? null)) {
                            $updateData['nama'] = $studentData['nama'];
                            $needsUpdate = true;
                        }

                        if ($existingSiswa->kelas_id !== $kelasId && $kelasId !== null) {
                            $updateData['kelas_id'] = $kelasId;
                            $needsUpdate = true;
                        }

                        if ($existingSiswa->status_pembayaran !== ($studentData['status_pembayaran'] ?? 'Belum Lunas')) {
                            $updateData['status_pembayaran'] = $studentData['status_pembayaran'];
                            $needsUpdate = true;
                        }

                        if ($needsUpdate) {
                            $existingSiswa->update($updateData);
                            $siswaResults['updated']++;
                        }
                    } else {
                        // Buat siswa baru
                        Siswa::create([
                            'idyayasan' => $studentData['idyayasan'],
                            'nama' => $studentData['nama'] ?? null,
                            'kelas_id' => $kelasId,
                            'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                            'email' => $this->generateEmail($studentData['idyayasan']),
                            'password' => bcrypt('password'),
                            'rekomendasi' => 'tidak',
                            'catatan_rekomendasi' => null,
                        ]);
                        $siswaResults['created']++;
                    }
                } catch (\Exception $e) {
                    $siswaResults['errors'][] = "Error processing {$studentData['idyayasan']}: " . $e->getMessage();
                    $siswaResults['skipped']++;
                }
            }

            $results['updated_siswa'] = $siswaResults['updated'];
            $results['created_siswa'] = $siswaResults['created'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            session(['sync_progress' => 95]);
            session(['sync_message' => 'Finalizing sync...']);

            DB::commit();

            session(['sync_progress' => 100]);
            session(['sync_status' => 'completed']);

            $message = "Sync completed! Created kelas: {$results['created_kelas']}, Updated kelas: {$results['updated_kelas']}, ";
            $message .= "Created siswa: {$results['created_siswa']}, Updated siswa: {$results['updated_siswa']}";

            if ($results['skipped'] > 0) {
                $message .= ", Skipped: {$results['skipped']}";
            }

            session(['sync_message' => $message]);

            Log::info('SIKEU API sync completed', $results);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'message' => $message
                ]);
            }

            return redirect()->route('data.siswa.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            session(['sync_status' => 'error']);
            session(['sync_message' => 'Sync failed: ' . $e->getMessage()]);

            Log::error('SIKEU API sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sync failed: ' . $e->getMessage()
                ]);
            }

            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Get sync progress for AJAX polling
     */
    public function getSyncProgress()
    {
        return response()->json([
            'progress' => session('sync_progress', 0),
            'status' => session('sync_status', 'idle'),
            'message' => session('sync_message', 'Ready to sync')
        ]);
    }

    /**
     * Clear sync progress session
     */
    public function clearSyncProgress()
    {
        session()->forget(['sync_progress', 'sync_status', 'sync_message']);

        return response()->json([
            'success' => true,
            'message' => 'Sync progress cleared'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:siswa,id',
        ]);

        try {
            Siswa::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' siswa berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function bulkUpdateRekomendasi(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:siswa,id',
            'rekomendasi' => 'required|in:ya,tidak',
        ]);

        try {
            DB::beginTransaction();

            $updated = Siswa::whereIn('id', $validated['ids'])
                ->update(['rekomendasi' => $validated['rekomendasi']]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Rekomendasi berhasil diupdate untuk {$updated} siswa"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Bulk update rekomendasi failed', [
                'error' => $e->getMessage(),
                'ids' => $request->ids,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Bulk update gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * New batch import method that processes students in batches to prevent timeouts
     * and provide progress feedback
     */
    public function batchImport(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'batch_size' => 'nullable|integer|min:10|max:500',
            ]);

            // Set batch size from request or use default
            $batchSize = $validated['batch_size'] ?? $this->batchSize;

            // Initialize session for batch tracking
            session([
                'batch_import_status' => 'initializing',
                'batch_import_progress' => 0,
                'batch_import_message' => 'Starting batch import...',
                'batch_import_results' => [
                    'created_kelas' => 0,
                    'updated_kelas' => 0,
                    'created_siswa' => 0,
                    'updated_siswa' => 0,
                    'skipped' => 0,
                    'errors' => []
                ]
            ]);

            // Fetch data from API
            session(['batch_import_message' => 'Connecting to SIKEU API...']);
            session(['batch_import_progress' => 5]);

            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                session([
                    'batch_import_status' => 'error',
                    'batch_import_message' => 'API Error: ' . $apiResult['error']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'API Error: ' . $apiResult['error']
                ]);
            }

            $apiData = $apiResult['data'];

            if (empty($apiData)) {
                session([
                    'batch_import_status' => 'completed',
                    'batch_import_message' => 'No data received from API'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No data received from API'
                ]);
            }

            // Set up batch processing
            $totalRecords = count($apiData);
            $batchCount = ceil($totalRecords / $batchSize);

            // Store batch information in session
            session([
                'batch_import_data' => [
                    'api_data' => $apiData,
                    'batch_size' => $batchSize,
                    'total_records' => $totalRecords,
                    'batch_count' => $batchCount,
                    'current_batch' => 0,
                    'current_index' => 0
                ],
                'batch_import_status' => 'ready',
                'batch_import_message' => "Ready to process {$totalRecords} records in {$batchCount} batches",
                'batch_import_progress' => 10
            ]);

            // Process first batch immediately
            return $this->processBatchImport();
        } catch (\Exception $e) {
            Log::error('Batch import initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session([
                'batch_import_status' => 'error',
                'batch_import_message' => 'Import initialization failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Import initialization failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process a batch from the batch import job
     */
    private function processBatchImport()
    {
        // Get batch data from session
        $batchData = session('batch_import_data');
        $batchResults = session('batch_import_results');

        if (!$batchData) {
            return response()->json([
                'success' => false,
                'error' => 'No batch import in progress'
            ]);
        }

        $currentBatch = $batchData['current_batch'];
        $currentIndex = $batchData['current_index'];
        $batchSize = $batchData['batch_size'];
        $apiData = $batchData['api_data'];
        $totalRecords = $batchData['total_records'];
        $batchCount = $batchData['batch_count'];

        // Update progress information
        $progressPercent = min(10 + (90 * $currentBatch / $batchCount), 99);
        session([
            'batch_import_status' => 'processing',
            'batch_import_progress' => $progressPercent,
            'batch_import_message' => "Processing batch " . ($currentBatch + 1) . " of {$batchCount}..."
        ]);

        try {
            // Process this batch using the BatchSiswaProcessor
            $result = \App\Http\Controllers\Features\Data\BatchSiswaProcessor::processBatchImport(
                $apiData,
                $batchSize,
                $currentIndex
            );

            // Update cumulative results
            $batchResults['created_kelas'] += $result['created_kelas'];
            $batchResults['updated_kelas'] += $result['updated_kelas'];
            $batchResults['created_siswa'] += $result['created_siswa'];
            $batchResults['updated_siswa'] += $result['updated_siswa'];
            $batchResults['skipped'] += $result['skipped'];

            if (!empty($result['errors'])) {
                $batchResults['errors'] = array_merge($batchResults['errors'], $result['errors']);
            }

            // Update session with results
            session(['batch_import_results' => $batchResults]);

            // Check if this is the last batch
            if ($result['is_last_batch']) {
                session([
                    'batch_import_status' => 'completed',
                    'batch_import_progress' => 100,
                    'batch_import_message' => $this->generateCompletionMessage($batchResults)
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Import completed successfully',
                    'results' => $batchResults
                ]);
            } else {
                // Update batch tracking for next batch
                $nextBatch = $currentBatch + 1;
                $nextIndex = $currentIndex + $batchSize;

                session([
                    'batch_import_data' => array_merge($batchData, [
                        'current_batch' => $nextBatch,
                        'current_index' => $nextIndex
                    ])
                ]);

                // Return success and indicate there are more batches to process
                return response()->json([
                    'success' => true,
                    'status' => 'processing',
                    'current_batch' => $nextBatch,
                    'total_batches' => $batchCount,
                    'progress' => $progressPercent,
                    'next_batch_url' => route('data.siswa.batch-import'),
                    'batch_results' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Batch import processing failed', [
                'batch' => $currentBatch,
                'index' => $currentIndex,
                'error' => $e->getMessage()
            ]);

            session([
                'batch_import_status' => 'error',
                'batch_import_message' => 'Batch processing failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Batch processing failed: ' . $e->getMessage(),
                'batch' => $currentBatch,
                'index' => $currentIndex
            ]);
        }
    }

    /**
     * Get the current status of a batch import job
     */
    public function getBatchImportStatus()
    {
        return response()->json([
            'status' => session('batch_import_status', 'none'),
            'progress' => session('batch_import_progress', 0),
            'message' => session('batch_import_message', 'No import in progress'),
            'results' => session('batch_import_results', []),
            'current_batch' => session('batch_import_data.current_batch', 0),
            'total_batches' => session('batch_import_data.batch_count', 0)
        ]);
    }

    /**
     * Batch sync method that processes students in batches
     */
    public function batchSync(Request $request)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'batch_size' => 'nullable|integer|min:10|max:500',
            ]);

            // Set batch size from request or use default
            $batchSize = $validated['batch_size'] ?? $this->batchSize;

            // Initialize session for batch tracking
            session([
                'batch_sync_status' => 'initializing',
                'batch_sync_progress' => 0,
                'batch_sync_message' => 'Starting batch sync...',
                'batch_sync_results' => [
                    'created_kelas' => 0,
                    'updated_kelas' => 0,
                    'created_siswa' => 0,
                    'updated_siswa' => 0,
                    'skipped' => 0,
                    'errors' => []
                ]
            ]);

            // Fetch data from API
            session(['batch_sync_message' => 'Connecting to SIKEU API...']);
            session(['batch_sync_progress' => 5]);

            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                session([
                    'batch_sync_status' => 'error',
                    'batch_sync_message' => 'API Error: ' . $apiResult['error']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'API Error: ' . $apiResult['error']
                ]);
            }

            $apiData = $apiResult['data'];

            if (empty($apiData)) {
                session([
                    'batch_sync_status' => 'completed',
                    'batch_sync_message' => 'No data received from API'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No data received from API'
                ]);
            }

            // Set up batch processing
            $totalRecords = count($apiData);
            $batchCount = ceil($totalRecords / $batchSize);

            // Store batch information in session
            session([
                'batch_sync_data' => [
                    'api_data' => $apiData,
                    'batch_size' => $batchSize,
                    'total_records' => $totalRecords,
                    'batch_count' => $batchCount,
                    'current_batch' => 0,
                    'current_index' => 0
                ],
                'batch_sync_status' => 'ready',
                'batch_sync_message' => "Ready to sync {$totalRecords} records in {$batchCount} batches",
                'batch_sync_progress' => 10
            ]);

            // Process first batch immediately
            return $this->processBatchSync();
        } catch (\Exception $e) {
            Log::error('Batch sync initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            session([
                'batch_sync_status' => 'error',
                'batch_sync_message' => 'Sync initialization failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Sync initialization failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Process a batch from the batch sync job
     */
    private function processBatchSync()
    {
        // Get batch data from session
        $batchData = session('batch_sync_data');
        $batchResults = session('batch_sync_results');

        if (!$batchData) {
            return response()->json([
                'success' => false,
                'error' => 'No batch sync in progress'
            ]);
        }

        $currentBatch = $batchData['current_batch'];
        $currentIndex = $batchData['current_index'];
        $batchSize = $batchData['batch_size'];
        $apiData = $batchData['api_data'];
        $totalRecords = $batchData['total_records'];
        $batchCount = $batchData['batch_count'];

        // Update progress information
        $progressPercent = min(10 + (90 * $currentBatch / $batchCount), 99);
        session([
            'batch_sync_status' => 'processing',
            'batch_sync_progress' => $progressPercent,
            'batch_sync_message' => "Processing batch " . ($currentBatch + 1) . " of {$batchCount}..."
        ]);

        try {
            // Process this batch using the BatchSiswaProcessor
            $result = \App\Http\Controllers\Features\Data\BatchSiswaProcessor::processBatchSync(
                $apiData,
                $batchSize,
                $currentIndex
            );

            // Update cumulative results
            $batchResults['created_kelas'] += $result['created_kelas'];
            $batchResults['updated_kelas'] += $result['updated_kelas'];
            $batchResults['created_siswa'] += $result['created_siswa'];
            $batchResults['updated_siswa'] += $result['updated_siswa'];
            $batchResults['skipped'] += $result['skipped'];

            if (!empty($result['errors'])) {
                $batchResults['errors'] = array_merge($batchResults['errors'], $result['errors']);
            }

            // Update session with results
            session(['batch_sync_results' => $batchResults]);

            // Check if this is the last batch
            if ($result['is_last_batch']) {
                session([
                    'batch_sync_status' => 'completed',
                    'batch_sync_progress' => 100,
                    'batch_sync_message' => $this->generateCompletionMessage($batchResults, 'sync')
                ]);

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Sync completed successfully',
                    'results' => $batchResults
                ]);
            } else {
                // Update batch tracking for next batch
                $nextBatch = $currentBatch + 1;
                $nextIndex = $currentIndex + $batchSize;

                session([
                    'batch_sync_data' => array_merge($batchData, [
                        'current_batch' => $nextBatch,
                        'current_index' => $nextIndex
                    ])
                ]);

                // Return success and indicate there are more batches to process
                return response()->json([
                    'success' => true,
                    'status' => 'processing',
                    'current_batch' => $nextBatch,
                    'total_batches' => $batchCount,
                    'progress' => $progressPercent,
                    'next_batch_url' => route('data.siswa.batch-sync'),
                    'batch_results' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Batch sync processing failed', [
                'batch' => $currentBatch,
                'index' => $currentIndex,
                'error' => $e->getMessage()
            ]);

            session([
                'batch_sync_status' => 'error',
                'batch_sync_message' => 'Batch processing failed: ' . $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Batch processing failed: ' . $e->getMessage(),
                'batch' => $currentBatch,
                'index' => $currentIndex
            ]);
        }
    }

    /**
     * Get the current status of a batch sync job
     */
    public function getBatchSyncStatus()
    {
        return response()->json([
            'status' => session('batch_sync_status', 'none'),
            'progress' => session('batch_sync_progress', 0),
            'message' => session('batch_sync_message', 'No sync in progress'),
            'results' => session('batch_sync_results', []),
            'current_batch' => session('batch_sync_data.current_batch', 0),
            'total_batches' => session('batch_sync_data.batch_count', 0)
        ]);
    }

    /**
     * Generate completion message from batch results
     */
    private function generateCompletionMessage(array $results, string $type = 'import')
    {
        $action = ucfirst($type);
        $message = "{$action} completed! ";
        $message .= "Created kelas: {$results['created_kelas']}, Updated kelas: {$results['updated_kelas']}, ";
        $message .= "Created siswa: {$results['created_siswa']}, Updated siswa: {$results['updated_siswa']}";

        if ($results['skipped'] > 0) {
            $message .= ", Skipped: {$results['skipped']}";
        }

        return $message;
    }

    /**
     * Log client-side batch sync errors
     */
    public function logBatchSyncError(Request $request)
    {
        try {
            $data = $request->all();

            Log::error('Client-side batch sync error', [
                'error' => $data['error'] ?? 'Unknown error',
                'url' => $data['url'] ?? 'Unknown URL',
                'stack' => $data['stack'] ?? 'No stack trace provided',
                'user_id' => auth()->id(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Error logged successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log client error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to log error'
            ], 500);
        }
    }
}
