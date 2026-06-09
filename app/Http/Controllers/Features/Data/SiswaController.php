<?php
// filepath: app\Http\Controllers\Features\Data\SiswaController.php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\SiswaTahunAjaran;
use App\Services\SikeuApiService;
use App\Services\SiswaQuickSyncService;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    protected $sikeuApiService;
    protected $tahunAjaranService;
    protected $siswaQuickSyncService;

    public function __construct(
        SikeuApiService $sikeuApiService,
        ?TahunAjaranService $tahunAjaranService = null,
        ?SiswaQuickSyncService $siswaQuickSyncService = null
    )
    {
        $this->sikeuApiService = $sikeuApiService;
        $this->tahunAjaranService = $tahunAjaranService ?: app(TahunAjaranService::class);
        $this->siswaQuickSyncService = $siswaQuickSyncService ?: app(SiswaQuickSyncService::class);
    }

    private function hydrateKelasForTahun($siswas, ?int $tahunAjaranId): void
    {
        if (!$tahunAjaranId) {
            return;
        }

        foreach ($siswas as $siswa) {
            $record = $siswa->tahunAjaranRecords->firstWhere('tahun_ajaran_id', $tahunAjaranId);
            if ($record?->kelas) {
                $siswa->setRelation('kelas', $record->kelas);
                $siswa->kelas_id = $record->kelas_id;
                $siswa->status_pembayaran = $record->status_pembayaran ?? $siswa->status_pembayaran;
                $siswa->rekomendasi = $record->rekomendasi ?? $siswa->rekomendasi;
                $siswa->catatan_rekomendasi = $record->catatan ?? $siswa->catatan_rekomendasi;
            }
        }
    }

    private function progressKey(string $type): string
    {
        $userId = auth()->id() ?: session()->getId();

        return "siswa_{$type}_progress_{$userId}";
    }

    private function progressDefaults(string $type): array
    {
        return [
            'progress' => 0,
            'status' => 'idle',
            'message' => $type === 'sync' ? 'Ready to sync' : 'Ready to import',
            'started_at' => null,
            'updated_at' => null,
        ];
    }

    private function setProgress(string $type, array $values): void
    {
        $current = Cache::get($this->progressKey($type), $this->progressDefaults($type));

        Cache::put(
            $this->progressKey($type),
            array_merge($current, $values, [
                'updated_at' => now()->toDateTimeString(),
                'started_at' => $current['started_at'] ?? now()->toDateTimeString(),
            ]),
            now()->addHour()
        );
    }

    private function getProgress(string $type): array
    {
        return Cache::get($this->progressKey($type), $this->progressDefaults($type));
    }

    private function clearProgress(string $type): void
    {
        Cache::forget($this->progressKey($type));
    }

    private function getTotalSiswaPerTingkat(?int $tahunAjaranId)
    {
        return SiswaTahunAjaran::query()
            ->join('kelas', 'siswa_tahun_ajaran.kelas_id', '=', 'kelas.id')
            ->when($tahunAjaranId, fn($query) => $query->where('siswa_tahun_ajaran.tahun_ajaran_id', $tahunAjaranId))
            ->selectRaw("COALESCE(kelas.tingkat, 'Tanpa Tingkat') as tingkat, COUNT(DISTINCT siswa_tahun_ajaran.siswa_id) as total")
            ->groupBy('kelas.tingkat')
            ->orderByRaw("FIELD(kelas.tingkat, 'X', 'XI', 'XII'), kelas.tingkat")
            ->pluck('total', 'tingkat');
    }

    private function setImportProgress(array $values): void
    {
        $this->setProgress('import', $values);
    }

    private function setSyncProgress(array $values): void
    {
        $this->setProgress('sync', $values);
    }

    /**
     * Display a listing of siswa
     */
    public function index(Request $request)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id', $this->tahunAjaranService->activeId());
        $query = Siswa::with(['kelas', 'tahunAjaranRecords.kelas']); // Eager load kelas data for better performance

        if ($tahunAjaranId) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

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
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('kelas_id', $request->get('kelas_id'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('status_pembayaran')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('status_pembayaran', $request->get('status_pembayaran'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('rekomendasi')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('rekomendasi', $request->get('rekomendasi'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $siswas = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $this->hydrateKelasForTahun($siswas->getCollection(), $tahunAjaranId);
        $siswas->appends($request->query());

        // Get available kelas for filter dropdown using more efficient query
        $availableKelas = \App\Models\Kelas::forTahunAjaran($tahunAjaranId)
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');

        // Get total count for empty state detection
        $totalSiswa = $tahunAjaranId
            ? Siswa::whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->count()
            : Siswa::count();
        $totalSiswaPerTingkat = $this->getTotalSiswaPerTingkat($tahunAjaranId);
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        return view('features.data.siswa.index', compact('siswas', 'availableKelas', 'totalSiswa', 'totalSiswaPerTingkat', 'tahunAjarans', 'tahunAjaranId'));
    }

    public function settings()
    {
        $settings = SchoolSetting::allAsArray();

        return view('features.data.siswa.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'sync_siswa_enabled' => 'nullable|boolean',
            'sync_siswa_interval_minutes' => 'required|integer|min:1|max:1440',
            'sync_siswa_date_start' => 'nullable|date',
            'sync_siswa_date_end' => 'nullable|date|after_or_equal:sync_siswa_date_start',
            'sync_siswa_time_start' => 'nullable|date_format:H:i',
            'sync_siswa_time_end' => 'nullable|date_format:H:i',
        ]);

        $validated['sync_siswa_enabled'] = $request->boolean('sync_siswa_enabled') ? '1' : '0';

        SchoolSetting::setMany($validated);

        return redirect()
            ->route('data.siswa.settings')
            ->with('success', 'Setting sinkronisasi siswa berhasil disimpan.');
    }

    /**
     * AJAX search for siswa - WITH IMPROVED PAGINATION SUPPORT
     */
    public function search(Request $request)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id', $this->tahunAjaranService->activeId());
        $query = Siswa::with(['kelas', 'tahunAjaranRecords.kelas']); // Eager load kelas data for better performance

        if ($tahunAjaranId) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

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
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('kelas_id', $request->get('kelas_id'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('status_pembayaran')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('status_pembayaran', $request->get('status_pembayaran'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        if ($request->filled('rekomendasi')) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('rekomendasi', $request->get('rekomendasi'))
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId)));
        }

        // Paginate results
        $perPage = $request->get('per_page', 50);
        $siswas = $query->orderBy('created_at', 'desc')->paginate($perPage);
        $this->hydrateKelasForTahun($siswas->getCollection(), $tahunAjaranId);

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
        $availableKelas = \App\Models\Kelas::forTahunAjaran($tahunAjaranId)
            ->orderBy('nama_kelas')
            ->pluck('nama_kelas', 'id');

        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $totalSiswa = $tahunAjaranId
            ? Siswa::whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->count()
            : Siswa::count();
        $totalSiswaPerTingkat = $this->getTotalSiswaPerTingkat($tahunAjaranId);

        return view('features.data.siswa.index', compact('siswas', 'availableKelas', 'totalSiswa', 'totalSiswaPerTingkat', 'tahunAjarans', 'tahunAjaranId'));
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

        $tahunAjaranId = $this->tahunAjaranService->ensureActive()->id;
        SiswaTahunAjaran::updateOrCreate(
            [
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $tahunAjaranId,
            ],
            [
                'kelas_id' => $siswa->kelas_id,
                'status_siswa' => 'aktif',
                'status_pembayaran' => $siswa->status_pembayaran,
                'rekomendasi' => $validated['rekomendasi'],
                'catatan' => $validated['catatan_rekomendasi'] ?? null,
            ]
        );

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
     * Optimized for 6 jurusan: BD, DKV, DPIB, TKJ, TKR, TSM
     */
    protected function extractJurusanFromKelas($kelasName)
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        // Jurusan yang tersedia di sekolah (6 jurusan)
        $jurusanList = ['BD', 'DKV', 'DPIB', 'TKJ', 'TKR', 'TSM'];

        // Format "X - BD" atau "XI - TKJ"
        if (preg_match('/^(X|XI|XII)\s*-\s*([A-Z]+)/i', $kelasName, $matches)) {
            $jurusan = strtoupper($matches[2]);
            return in_array($jurusan, $jurusanList) ? $jurusan : 'UMUM';
        }

        // Format "X BD -" atau "X DPIB 2" atau "XI TKR 1"
        if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', $kelasName, $matches)) {
            $jurusan = strtoupper($matches[2]);
            return in_array($jurusan, $jurusanList) ? $jurusan : 'UMUM';
        }

        // Format gabung tanpa spasi seperti "XITKS1"
        $jurusanPattern = implode('|', $jurusanList);
        if (preg_match('/(X|XI|XII)(' . $jurusanPattern . ')/i', $kelasName, $matches)) {
            return strtoupper($matches[2]);
        }

        // Default jurusan jika tidak ditemukan
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
            @set_time_limit(0);
            @ini_set('memory_limit', '1024M');

            Log::info('Starting AJAX SIKEU API import process');

            // Initialize cache progress
            $this->setImportProgress(['progress' => 0, 'status' => 'starting', 'message' => 'Initializing import...']);
            $this->setImportProgress(['message' => 'Connecting to SIKEU API...', 'progress' => 10]);

            $apiResult = $this->sikeuApiService->fetchSiswaData();

            if (!$apiResult['success']) {
                $this->setImportProgress(['status' => 'error', 'message' => 'API Error: ' . $apiResult['error']]);
                return response()->json(['success' => false, 'error' => 'API Error: ' . $apiResult['error'], 'step' => 'api_connection']);
            }

            $apiData = $apiResult['data'];
            if (empty($apiData)) {
                $this->setImportProgress(['status' => 'warning', 'message' => 'No data received from API']);
                return response()->json(['success' => false, 'error' => 'No data received from API', 'step' => 'data_validation']);
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
            $activeYear = $this->tahunAjaranService->ensureActive();

            DB::beginTransaction();

            // ===== LANGKAH 1: EXTRACT & BATCH PROCESS KELAS (10% - 30%) =====
            $this->setImportProgress(['message' => 'Extracting unique class data...', 'progress' => 15]);

            $uniqueKelas = $this->extractUniqueKelasFromApiData($apiData);
            Log::info('Extracted unique kelas', ['count' => count($uniqueKelas)]);

            // Prepare kelas data for batch processing
            $this->setImportProgress(['message' => 'Processing class data...', 'progress' => 20]);

            // Get all existing kelas in one query
            $existingKelas = \App\Models\Kelas::where('tahun_ajaran_id', $activeYear->id)
                ->get()
                ->keyBy('nama_kelas');

            $kelasToCreate = [];
            $kelasToUpdate = [];

            foreach ($uniqueKelas as $kelasName => $kelasData) {
                if ($existingKelas->has($kelasName)) {
                    $existing = $existingKelas->get($kelasName);
                    if ($existing->tingkat !== $kelasData['tingkat'] || $existing->jurusan !== $kelasData['jurusan']) {
                        $kelasToUpdate[$existing->id] = [
                            'tingkat' => $kelasData['tingkat'],
                            'jurusan' => $kelasData['jurusan']
                        ];
                    }
                } else {
                    $kelasToCreate[] = array_merge($kelasData, [
                        'tahun_ajaran_id' => $activeYear->id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Batch insert new kelas
            if (!empty($kelasToCreate)) {
                \App\Models\Kelas::insert($kelasToCreate);
                $results['created_kelas'] = count($kelasToCreate);
                Log::info('Batch inserted kelas', ['count' => count($kelasToCreate)]);
            }

            // Batch update existing kelas using raw SQL for speed
            if (!empty($kelasToUpdate)) {
                foreach ($kelasToUpdate as $kelasId => $updateData) {
                    \App\Models\Kelas::where('id', $kelasId)->update($updateData);
                }
                $results['updated_kelas'] = count($kelasToUpdate);
                Log::info('Batch updated kelas', ['count' => count($kelasToUpdate)]);
            }

            // Refresh kelas lookup map
            $allKelas = \App\Models\Kelas::where('tahun_ajaran_id', $activeYear->id)
                ->pluck('id', 'nama_kelas')
                ->toArray();

            $this->setImportProgress(['message' => 'Classes processed. Processing students...', 'progress' => 30]);

            // ===== LANGKAH 2: CHUNK PROCESS SISWA (30% - 95%) =====
            $chunkSize = 200;
            $totalChunks = ceil(count($apiData) / $chunkSize);
            $progressStep = 65 / max($totalChunks, 1);
            $currentProgress = 30;

            // Pre-load all existing siswa (withTrashed) for fast lookup
            $existingSiswaList = Siswa::withTrashed()
                ->pluck('id', 'idyayasan')
                ->toArray();

            // OPTIMIZATION: Pre-hash password once (avoid 1050x bcrypt calls)
            $hashedPassword = bcrypt('password');

            // Build idyayasan-to-student data map for O(1) lookup (avoid O(n²) nested loop)
            $chunkStudentDataMap = [];
            foreach ($apiData as $studentData) {
                if (!empty($studentData['idyayasan'])) {
                    $chunkStudentDataMap[$studentData['idyayasan']] = $studentData;
                }
            }

            $chunkIndex = 0;
            foreach (array_chunk($apiData, $chunkSize) as $chunk) {
                $chunkIndex++;

                // Update progress less frequently (every chunk)
                $currentProgress += $progressStep;
                $this->setImportProgress([
                    'progress' => min(92, (int) $currentProgress),
                    'message' => "Processing students... Batch $chunkIndex/$totalChunks"
                ]);

                $siswaToCreate = [];
                $siswaToUpdate = [];
                $pivotDataByChunk = [];

                foreach ($chunk as $studentData) {
                    try {
                        if (empty($studentData['idyayasan'])) {
                            $results['errors'][] = ['error' => 'Missing idyayasan'];
                            $results['skipped']++;
                            continue;
                        }

                        $idyayasan = $studentData['idyayasan'];
                        $kelasId = null;
                        if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                            $kelasId = $allKelas[trim($studentData['kelas'])];
                        }

                        if (isset($existingSiswaList[$idyayasan])) {
                            $siswaId = $existingSiswaList[$idyayasan];
                            $siswaToUpdate[$siswaId] = [
                                'nama' => $studentData['nama'] ?? null,
                                'updated_at' => now()
                            ];
                            $results['updated_siswa']++;

                            // Collect pivot data for later update
                            $pivotDataByChunk[$siswaId] = [
                                'siswa_id' => $siswaId,
                                'tahun_ajaran_id' => $activeYear->id,
                                'kelas_id' => $kelasId,
                                'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas'
                            ];
                        } else {
                            $siswaToCreate[] = [
                                'idyayasan' => $idyayasan,
                                'nama' => $studentData['nama'] ?? null,
                                'email' => $studentData['email'] ?? $this->generateEmail($idyayasan),
                                'password' => $hashedPassword,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $results['created_siswa']++;
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = ['idyayasan' => $studentData['idyayasan'] ?? 'Unknown', 'error' => $e->getMessage()];
                        $results['skipped']++;
                    }
                }

                // OPTIMIZATION: Batch insert new siswa and map to pivot data using O(1) lookup
                if (!empty($siswaToCreate)) {
                    foreach ($siswaToCreate as $siswaData) {
                        $newSiswa = Siswa::create($siswaData);
                        // Use pre-built map for O(1) lookup instead of nested loop O(n²)
                        if (isset($chunkStudentDataMap[$siswaData['idyayasan']])) {
                            $studentData = $chunkStudentDataMap[$siswaData['idyayasan']];
                            $kelasId = null;
                            if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                                $kelasId = $allKelas[trim($studentData['kelas'])];
                            }
                            $pivotDataByChunk[$newSiswa->id] = [
                                'siswa_id' => $newSiswa->id,
                                'tahun_ajaran_id' => $activeYear->id,
                                'kelas_id' => $kelasId,
                                'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas'
                            ];
                        }
                    }
                }

                // Batch update siswa
                if (!empty($siswaToUpdate)) {
                    foreach ($siswaToUpdate as $siswaId => $data) {
                        Siswa::where('id', $siswaId)->update($data);
                    }
                }

                // OPTIMIZATION: Batch upsert pivot records instead of per-item updateOrCreate
                if (!empty($pivotDataByChunk)) {
                    // Prepare data for efficient insert/update
                    $pivotInserts = [];
                    foreach ($pivotDataByChunk as $siswaId => $pivotData) {
                        $pivotInserts[] = array_merge($pivotData, [
                            'status_siswa' => 'aktif',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    // Batch upsert: insert if not exists, update if exists
                    foreach ($pivotInserts as $pivotData) {
                        SiswaTahunAjaran::updateOrCreate(
                            [
                                'siswa_id' => $pivotData['siswa_id'],
                                'tahun_ajaran_id' => $pivotData['tahun_ajaran_id']
                            ],
                            $pivotData
                        );
                    }
                }
            }

            $this->setImportProgress(['progress' => 95, 'message' => 'Finalizing import...']);

            DB::commit();

            $this->setImportProgress(['progress' => 100, 'status' => 'completed']);
            $message = sprintf(
                "Import completed! Kelas: %d created, %d updated | Students: %d created, %d updated, %d skipped",
                $results['created_kelas'],
                $results['updated_kelas'],
                $results['created_siswa'],
                $results['updated_siswa'],
                $results['skipped']
            );
            $this->setImportProgress(['message' => $message]);

            Log::info('AJAX SIKEU API import completed', $results);

            return response()->json(['success' => true, 'data' => $results, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->setImportProgress(['status' => 'error', 'message' => 'Import failed: ' . $e->getMessage()]);
            Log::error('AJAX SIKEU API import failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'error' => 'Import failed: ' . $e->getMessage(), 'exception' => get_class($e)]);
        }
    }
    /**
     * Get import progress for AJAX polling
     */
    public function getImportProgress()
    {
        $state = $this->getProgress('import');

        // Log to help with debugging
        Log::debug('Import progress request', [
            'progress' => $state['progress'],
            'status' => $state['status'],
            'message' => $state['message'],
            'progress_key' => $this->progressKey('import')
        ]);

        return response()->json([
            'progress' => $state['progress'],
            'status' => $state['status'],
            'message' => $state['message'],
            'started_at' => $state['started_at'],
            'updated_at' => $state['updated_at'],
            'timestamp' => now()->timestamp // Include timestamp for debugging
        ]);
    }

    /**
     * Clear import progress session
     */
    public function clearImportProgress()
    {
        $this->clearProgress('import');

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
            $result = $this->siswaQuickSyncService->sync(fn(array $progress) => $this->setSyncProgress($progress));

            if (!$result['success']) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => $result['error'] ?? null,
                        'warning' => $result['warning'] ?? null,
                    ]);
                }

                if (!empty($result['warning'])) {
                    return back()->with('warning', $result['warning']);
                }

                return back()->with('error', $result['error'] ?? 'Sync failed.');
            }

            $results = $result['data'];
            $message = $result['message'];

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $results,
                    'message' => $message
                ]);
            }

            return redirect()->route('data.siswa.index')->with('success', $message);
        } catch (\Exception $e) {
            $this->setSyncProgress(['status' => 'error']);
            $this->setSyncProgress(['message' => 'Sync failed: ' . $e->getMessage()]);

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
        $state = $this->getProgress('sync');

        return response()->json([
            'progress' => $state['progress'],
            'status' => $state['status'],
            'message' => $state['message'],
            'started_at' => $state['started_at'],
            'updated_at' => $state['updated_at'],
            'timestamp' => now()->timestamp
        ]);
    }

    /**
     * Clear sync progress session
     */
    public function clearSyncProgress()
    {
        $this->clearProgress('sync');

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

            $tahunAjaranId = $this->tahunAjaranService->ensureActive()->id;
            $updated = 0;

            foreach (Siswa::whereIn('id', $validated['ids'])->get() as $siswa) {
                SiswaTahunAjaran::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'tahun_ajaran_id' => $tahunAjaranId,
                    ],
                    [
                        'kelas_id' => $siswa->kelas_id,
                        'status_siswa' => 'aktif',
                        'status_pembayaran' => $siswa->status_pembayaran,
                        'rekomendasi' => $validated['rekomendasi'],
                        'catatan' => $siswa->catatan_rekomendasi,
                    ]
                );
                $updated++;
            }

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
}
