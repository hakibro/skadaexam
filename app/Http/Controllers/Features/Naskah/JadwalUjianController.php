<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\EnrollmentUjian;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Services\SesiAssignmentService;
use App\Services\TahunAjaranService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JadwalUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tahunAjaranService = app(TahunAjaranService::class);
        $activeYear = $tahunAjaranService->active();
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear?->id);
        $paketUjianId = null;
        $showAllPaket = $request->get('paket_ujian_id') === '__all';

        if ($request->has('paket_ujian_id')) {
            $paketUjianId = $showAllPaket ? null : $request->get('paket_ujian_id');
        } elseif ($tahunAjaranId) {
            $activePaket = \App\Models\PaketUjian::where('tahun_ajaran_id', $tahunAjaranId)
                ->where('status', 'aktif')
                ->orderByDesc('tanggal_mulai')
                ->orderBy('nama')
                ->first();

            if (!$activePaket && $activeYear && (string) $tahunAjaranId === (string) $activeYear->id) {
                $activePaket = $tahunAjaranService->defaultPaketFor($activeYear);
            }

            $paketUjianId = $activePaket?->id;
        }

        $query = JadwalUjian::with(['mapel', 'bankSoal', 'creator', 'tahunAjaran', 'paketUjian'])
            ->withCount('sesiRuangans');

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }

        if ($paketUjianId) {
            $query->where('paket_ujian_id', $paketUjianId);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by mapel
        if ($request->has('mapel_id') && $request->mapel_id != '') {
            $query->where('mapel_id', $request->mapel_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('tanggal', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('tanggal', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('kode_ujian', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 50); // default 50
        $jadwalUjians = $query->orderBy('tanggal', 'desc')->paginate($perPage);

        $mapels = Mapel::forTahunAjaran($tahunAjaranId)->orderBy('nama_mapel', 'asc')->get();
        $paketUjians = \App\Models\PaketUjian::when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->get();
        $sourceSesiOptions = SesiRuangan::with(['ruangan'])
            ->withCount('sesiRuanganSiswa')
            ->where('sumber', 'sumber')
            ->when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->when($paketUjianId, fn($query) => $query->where('paket_ujian_id', $paketUjianId))
            ->orderBy('ruangan_id')
            ->orderBy('waktu_mulai')
            ->get();

        return view('features.naskah.jadwal.index', compact('jadwalUjians', 'mapels', 'perPage', 'tahunAjarans', 'tahunAjaranId', 'paketUjians', 'paketUjianId', 'activeYear', 'showAllPaket', 'sourceSesiOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tahunAjaranService = app(TahunAjaranService::class);
        $activeYear = $tahunAjaranService->active();

        if (!$activeYear) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Belum ada tahun ajaran aktif. Buat dan aktifkan tahun ajaran terlebih dahulu.');
        }

        $paketUjianId = request('paket_ujian_id') ?: $tahunAjaranService->defaultPaketFor($activeYear)->id;
        $paketUjians = \App\Models\PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('status', '!=', 'arsip')
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->get();
        $mapels = Mapel::forTahunAjaran($activeYear->id)->orderBy('nama_mapel', 'asc')->get();
        $bankSoals = BankSoal::forTahunAjaran($activeYear->id)->withCount('soals')->orderBy('judul', 'asc')->get();
        $kelasList = Kelas::forTahunAjaran($activeYear->id)->orderBy('tingkat')->orderBy('jurusan')->orderBy('nama_kelas')->get();

        return view('features.naskah.jadwal.create', compact('mapels', 'bankSoals', 'kelasList', 'activeYear', 'paketUjians', 'paketUjianId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'paket_ujian_id' => 'required|exists:paket_ujian,id',
            'bank_soal_id' => 'nullable|exists:bank_soal,id',
            'tanggal' => 'required|date',
            'durasi_preset' => 'required|in:25,30,45,manual',
            'durasi_manual' => 'required_if:durasi_preset,manual|nullable|integer|min:1',
            'deskripsi' => 'nullable|string',
            'kelas_target' => 'nullable|array',
            'kelas_target.*' => 'exists:kelas,id',
        ]);

        $activeYear = app(TahunAjaranService::class)->ensureActive();
        $paketUjian = \App\Models\PaketUjian::where('tahun_ajaran_id', $activeYear->id)
            ->where('id', $request->paket_ujian_id)
            ->where('status', '!=', 'arsip')
            ->firstOrFail();

        $mapel = Mapel::where('tahun_ajaran_id', $activeYear->id)->findOrFail($request->mapel_id);
        $bankSoal = $this->resolveBankSoalForMapel($request->mapel_id, $request->bank_soal_id);

        if (!$bankSoal) {
            return back()
                ->withInput()
                ->withErrors(['bank_soal_id' => 'Tidak ada bank soal yang terkait dengan mata pelajaran ini.']);
        }

        $jumlahSoal = $bankSoal->soals()->count();
        if ($jumlahSoal < 1) {
            return back()
                ->withInput()
                ->withErrors(['jumlah_soal' => 'Bank soal terkait belum memiliki soal.']);
        }

        $durasiMenit = $request->durasi_preset === 'manual'
            ? (int) $request->durasi_manual
            : (int) $request->durasi_preset;

        // Generate unique exam code
        $kodeUjian = 'U' . date('Ymd') . strtoupper(Str::random(5));

        // Get target classes based on mapel
        $kelasTarget = [];

        // If kelas_target is provided in the request, use it
        if ($request->has('kelas_target')) {
            $kelasTarget = $request->kelas_target;
        } else {
            // Otherwise, try to find matching classes based on mapel's tingkat and jurusan
            if ($mapel) {
                $query = Kelas::where('tahun_ajaran_id', $activeYear->id);

                // Filter by tingkat if mapel has it
                if ($mapel->tingkat) {
                    $query->where('tingkat', $mapel->tingkat);
                }

                // Filter by jurusan if mapel has it, or include UMUM jurusan
                if ($mapel->jurusan) {
                    $query->where(function ($q) use ($mapel) {
                        $q->where('jurusan', $mapel->jurusan)
                            ->orWhere('jurusan', 'UMUM');
                    });
                }

                $matchingKelas = $query->get();
                $kelasTarget = $matchingKelas->pluck('id')->toArray();
            }
        }

        // Create new jadwal ujian
        $jadwalUjian = JadwalUjian::create([
            'tahun_ajaran_id' => $activeYear->id,
            'paket_ujian_id' => $paketUjian->id,
            'kode_ujian' => $kodeUjian,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $bankSoal->id,
            'tanggal' => $request->tanggal,
            'durasi_menit' => $durasiMenit,
            'jumlah_soal' => $jumlahSoal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
            'aktifkan_auto_logout' => $request->has('aktifkan_auto_logout'),
            'auto_assign_sesi' => false,
            'auto_enroll' => false,
            'kelas_target' => $kelasTarget, // Add the kelas_target field
            'status' => 'aktif',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwalUjian->id)
            ->with('success', 'Jadwal ujian berhasil dibuat');
    }

    private function resolveBankSoalForMapel(int $mapelId, ?int $bankSoalId = null): ?BankSoal
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $query = BankSoal::where('mapel_id', $mapelId)
            ->forTahunAjaran($activeYearId)
            ->withCount('soals')
            ->orderBy('judul', 'asc');

        if ($bankSoalId) {
            $selectedBankSoal = (clone $query)->where('id', $bankSoalId)->first();

            if ($selectedBankSoal) {
                return $selectedBankSoal;
            }
        }

        return $query->get()->first(fn($bankSoal) => $bankSoal->soals_count > 0);
    }

    /**
     * Display the specified resource.
     */
    public function show(JadwalUjian $jadwal)
    {
        // Enable error display for debugging
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        // Debug logging with timestamp for tracing
        $logFile = storage_path('logs/jadwal_debug.log');
        $uniqueId = uniqid();
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "===========$uniqueId==========\n", FILE_APPEND);
        file_put_contents($logFile, "Show method called at {$timestamp}\n", FILE_APPEND);
        file_put_contents($logFile, "Jadwal ID: {$jadwal->id}\n", FILE_APPEND);
        file_put_contents($logFile, "Request URI: " . request()->getRequestUri() . "\n", FILE_APPEND);
        file_put_contents($logFile, "IP Address: " . request()->ip() . "\n", FILE_APPEND);
        file_put_contents($logFile, "User Agent: " . request()->userAgent() . "\n", FILE_APPEND);

        try {
            // Try loading each relationship separately for better error isolation
            try {
                $jadwal->load('mapel');
                file_put_contents($logFile, "✓ Loaded mapel\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "✗ Failed to load mapel: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            try {
                $jadwal->load('bankSoal');
                file_put_contents($logFile, "✓ Loaded bankSoal\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "✗ Failed to load bankSoal: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            try {
                $jadwal->load('creator');
                file_put_contents($logFile, "✓ Loaded creator\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "✗ Failed to load creator: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            try {
                $jadwal->load('sesiRuangans.ruangan', 'sesiRuangans.sesiRuanganSiswa');
                file_put_contents($logFile, "✓ Loaded sesiRuangans\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "✗ Failed to load sesiRuangans: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            // Get schedule information using SesiAssignmentService
            $sesiAssignmentService = new SesiAssignmentService();
            $scheduleInfo = $sesiAssignmentService->getConsolidatedSchedule($jadwal);

            // Log waktu_mulai and waktu_selesai to verify accessor methods
            file_put_contents($logFile, "Testing accessor methods\n", FILE_APPEND);
            file_put_contents($logFile, "waktu_mulai: " . ($jadwal->waktu_mulai ? $jadwal->waktu_mulai->format('H:i:s') : 'NULL') . "\n", FILE_APPEND);
            file_put_contents($logFile, "waktu_selesai: " . ($jadwal->waktu_selesai ? $jadwal->waktu_selesai->format('H:i:s') : 'NULL') . "\n", FILE_APPEND);

            // Log data being passed to view
            file_put_contents($logFile, "Data ready for view\n", FILE_APPEND);
            file_put_contents($logFile, "Schedule info: " . json_encode($scheduleInfo) . "\n", FILE_APPEND);

            $sourceSesiOptions = SesiRuangan::with(['ruangan', 'sesiRuanganSiswa'])
                ->withCount('sesiRuanganSiswa')
                ->where('sumber', 'sumber')
                ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                ->when($jadwal->paket_ujian_id, fn($query) => $query->where('paket_ujian_id', $jadwal->paket_ujian_id), fn($query) => $query->whereNull('paket_ujian_id'))
                ->orderBy('ruangan_id')
                ->orderBy('waktu_mulai')
                ->get();

            // Standard debug view
            file_put_contents($logFile, "Using standard debug view\n", FILE_APPEND);
            return view('features.naskah.jadwal.show', [
                'jadwal' => $jadwal,
                'scheduleInfo' => $scheduleInfo,
                'sourceSesiOptions' => $sourceSesiOptions,
                'debug_timestamp' => $timestamp,
                'debug_id' => $uniqueId
            ]);
        } catch (\Exception $e) {
            // Log main error details
            file_put_contents($logFile, "Major error: " . $e->getMessage() . "\n", FILE_APPEND);
            file_put_contents($logFile, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);

            // Return a plain error message
            return response()->make('
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Error</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .error { color: red; padding: 10px; border: 1px solid red; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h1>Major Error</h1>
                    <div class="error">
                        <strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '
                    </div>
                    <p><a href="' . route('naskah.jadwal.index') . '">Back to List</a></p>
                    <hr>
                    <p>Debug ID: ' . $uniqueId . '</p>
                    <p>Timestamp: ' . $timestamp . '</p>
                </body>
                </html>
            ');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $mapels = Mapel::forTahunAjaran($jadwal->tahun_ajaran_id)->orderBy('nama_mapel', 'asc')->get();
        $bankSoals = BankSoal::forTahunAjaran($jadwal->tahun_ajaran_id)->orderBy('judul', 'asc')->get();
        return view('features.naskah.jadwal.edit', compact('jadwal', 'mapels', 'bankSoals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tanggal' => 'required|date',
            'durasi_menit' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        // Kelas target dikelola otomatis berdasarkan tingkat dan jurusan mata pelajaran.
        $kelasTarget = [];

        $mapel = Mapel::forTahunAjaran($jadwal->tahun_ajaran_id)->find($request->mapel_id);
        if ($mapel) {
            $query = Kelas::forTahunAjaran($jadwal->tahun_ajaran_id);

            if ($mapel->tingkat) {
                $query->where('tingkat', $mapel->tingkat);
            }

            if ($mapel->jurusan) {
                $query->where(function ($q) use ($mapel) {
                    $q->where('jurusan', $mapel->jurusan)
                        ->orWhere('jurusan', 'UMUM');
                });
            }

            $kelasTarget = $query->pluck('id')->toArray();
        }

        // Auto-sync jumlah_soal from bank soal
        $bankSoal = BankSoal::forTahunAjaran($jadwal->tahun_ajaran_id)->find($request->bank_soal_id);
        $jumlahSoal = $bankSoal ? $bankSoal->soals()->count() : 0;

        if (!$bankSoal) {
            return redirect()->back()
                ->withErrors(['bank_soal_id' => 'Bank soal tidak valid untuk tahun ajaran jadwal ini.'])
                ->withInput();
        }

        $jadwal->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $request->bank_soal_id,
            'tanggal' => $request->tanggal,
            'durasi_menit' => $request->durasi_menit,
            'jumlah_soal' => $jumlahSoal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
            'aktifkan_auto_logout' => $request->has('aktifkan_auto_logout'),
            'kelas_target' => $kelasTarget, // Add the kelas_target field
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', 'Jadwal ujian berhasil diperbarui');
    }

    /**
     * Update status of the resource.
     */
    public function updateStatus(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'status' => 'required|in:draft,aktif,selesai,dibatalkan',
        ]);

        $jadwal->update([
            'status' => $request->status,
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', 'Status jadwal ujian berhasil diperbarui');
    }

    /**
     * Attach an existing sesi to this jadwal
     */
    public function attachSesi(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.',
            ], 422);
        }

        $request->validate([
            'sesi_id' => 'required|exists:sesi_ruangan,id',
        ]);

        $sesi = SesiRuangan::where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)->findOrFail($request->sesi_id);

        // Attach the sesi to the jadwal using the many-to-many relationship
        if (!$jadwal->sesiRuangans()->where('sesi_ruangan_id', $sesi->id)->exists()) {
            $jadwal->sesiRuangans()->attach($sesi->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil ditambahkan ke jadwal'
        ]);
    }

    public function attachSourceSesiAndEnroll(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'sesi_ids' => 'required|array|min:1',
            'sesi_ids.*' => 'exists:sesi_ruangan,id',
        ]);

        DB::beginTransaction();

        try {
            $result = $this->assignSourceSesiAndEnrollForJadwal($jadwal, $request->sesi_ids);

            DB::commit();

            $message = "Berhasil menambahkan {$result['attached']} sesi dan {$result['enrolled']} siswa ke ujian.";
            if ($result['skipped'] > 0) {
                $message .= " {$result['skipped']} siswa dilewati karena tingkat/jurusan tidak sesuai.";
            }

            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Gagal mengatur sesi ujian: ' . $e->getMessage());
        }
    }

    private function isSiswaEligibleForJadwal($siswa, JadwalUjian $jadwal): bool
    {
        if (!$siswa) {
            return false;
        }

        $kelas = $this->resolveSiswaKelas($siswa, $jadwal);
        if (!$kelas) {
            return false;
        }

        $kelasTargets = collect($jadwal->kelas_target ?? [])
            ->map(fn($kelasId) => (string) $kelasId)
            ->filter()
            ->values()
            ->all();

        if (!empty($kelasTargets) && !in_array((string) $kelas->id, $kelasTargets, true)) {
            return false;
        }

        $mapel = $jadwal->mapel;
        if (!$mapel) {
            return true;
        }

        $mapelTingkat = $this->normalizeEligibilityValue($mapel->tingkat);
        $siswaTingkat = $this->normalizeEligibilityValue($kelas->tingkat);

        if ($mapelTingkat && $siswaTingkat !== $mapelTingkat) {
            return false;
        }

        $mapelJurusan = $this->normalizeEligibilityValue($mapel->jurusan);
        $siswaJurusan = $this->normalizeEligibilityValue($kelas->jurusan);

        if ($mapelJurusan && $mapelJurusan !== 'UMUM' && $siswaJurusan !== $mapelJurusan) {
            return false;
        }

        return true;
    }

    private function resolveSiswaKelas($siswa, JadwalUjian $jadwal): ?Kelas
    {
        if ($jadwal->tahun_ajaran_id) {
            $record = $siswa->relationLoaded('tahunAjaranRecords')
                ? $siswa->tahunAjaranRecords->firstWhere('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                : $siswa->tahunAjaranRecords()
                    ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                    ->with('kelas')
                    ->first();

            if ($record?->kelas) {
                return $record->kelas;
            }
        }

        return $siswa->kelas;
    }

    private function normalizeEligibilityValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            '10', 'KELAS 10', 'KELAS X' => 'X',
            '11', 'KELAS 11', 'KELAS XI' => 'XI',
            '12', 'KELAS 12', 'KELAS XII' => 'XII',
            default => $normalized,
        };
    }

    private function assignSourceSesiAndEnrollForJadwal(JadwalUjian $jadwal, array $sesiIds): array
    {
        $attachedCount = 0;
        $enrolledCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $sourceSesis = SesiRuangan::with(['sesiRuanganSiswa.siswa.kelas', 'sesiRuanganSiswa.siswa.tahunAjaranRecords.kelas'])
            ->whereIn('id', $sesiIds)
            ->where('sumber', 'sumber')
            ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
            ->when(
                $jadwal->paket_ujian_id,
                fn($query) => $query->where('paket_ujian_id', $jadwal->paket_ujian_id),
                fn($query) => $query->whereNull('paket_ujian_id')
            )
            ->get();

        foreach ($sourceSesis as $sourceSesi) {
            $duplicateSesi = SesiRuangan::where('sumber', $sourceSesi->kode_sesi)
                ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                ->when(
                    $jadwal->paket_ujian_id,
                    fn($query) => $query->where('paket_ujian_id', $jadwal->paket_ujian_id),
                    fn($query) => $query->whereNull('paket_ujian_id')
                )
                ->whereHas('jadwalUjians', function ($query) use ($jadwal) {
                    $query->whereDate('jadwal_ujian.tanggal', $jadwal->tanggal->toDateString());
                })
                ->with('sesiRuanganSiswa')
                ->first();

            if (!$duplicateSesi) {
                $duplicateSesi = SesiRuangan::create([
                    'tahun_ajaran_id' => $jadwal->tahun_ajaran_id,
                    'paket_ujian_id' => $jadwal->paket_ujian_id,
                    'ruangan_id' => $sourceSesi->ruangan_id,
                    'nama_sesi' => $sourceSesi->nama_sesi,
                    'waktu_mulai' => $sourceSesi->waktu_mulai,
                    'waktu_selesai' => $sourceSesi->waktu_selesai,
                    'status' => 'belum_mulai',
                    'sumber' => $sourceSesi->kode_sesi,
                    'pengaturan' => $sourceSesi->pengaturan,
                ]);
            }

            foreach ($sourceSesi->sesiRuanganSiswa as $assignment) {
                $duplicateSesi->sesiRuanganSiswa()->firstOrCreate(
                    ['siswa_id' => $assignment->siswa_id],
                    [
                        'status_kehadiran' => 'tidak_hadir',
                        'keterangan' => $assignment->keterangan,
                    ]
                );
            }

            $duplicateSesi->load('sesiRuanganSiswa.siswa.kelas', 'sesiRuanganSiswa.siswa.tahunAjaranRecords.kelas');

            if (!$jadwal->sesiRuangans()->where('sesi_ruangan.id', $duplicateSesi->id)->exists()) {
                $jadwal->sesiRuangans()->attach($duplicateSesi->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $attachedCount++;
            }

            foreach ($duplicateSesi->sesiRuanganSiswa as $assignment) {
                if (!$this->isSiswaEligibleForJadwal($assignment->siswa, $jadwal)) {
                    $skippedCount++;
                    continue;
                }

                $enrollment = EnrollmentUjian::withTrashed()
                    ->where('jadwal_ujian_id', $jadwal->id)
                    ->where('siswa_id', $assignment->siswa_id)
                    ->first();

                if (!$enrollment) {
                    EnrollmentUjian::create([
                        'siswa_id' => $assignment->siswa_id,
                        'jadwal_ujian_id' => $jadwal->id,
                        'sesi_ruangan_id' => $duplicateSesi->id,
                        'status_enrollment' => 'enrolled',
                        'catatan' => 'Enrolled dari sesi sumber ' . $sourceSesi->kode_sesi,
                    ]);
                    $enrolledCount++;
                    continue;
                }

                if ($enrollment->trashed()) {
                    $enrollment->restore();
                    $enrolledCount++;
                }

                $enrollment->update([
                    'sesi_ruangan_id' => $duplicateSesi->id,
                    'status_enrollment' => $enrollment->status_enrollment ?: 'enrolled',
                    'catatan' => $enrollment->catatan ?: 'Enrolled dari sesi sumber ' . $sourceSesi->kode_sesi,
                ]);
                $updatedCount++;
            }
        }

        return [
            'attached' => $attachedCount,
            'enrolled' => $enrolledCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'source_count' => $sourceSesis->count(),
        ];
    }

    /**
     * Detach a sesi from this jadwal
     */
    public function detachSesi(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.',
            ], 422);
        }

        $request->validate([
            'sesi_id' => 'required|exists:sesi_ruangan,id',
        ]);

        $sesi = SesiRuangan::findOrFail($request->sesi_id);

        // Detach the sesi from the jadwal
        $jadwal->sesiRuangans()->detach($sesi->id);

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil dilepas dari jadwal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        try {
            // Cek apakah sudah ada hasil ujian
            if ($jadwal->hasilUjian()->count() > 0) {
                return redirect()->route('naskah.jadwal.index')
                    ->with('error', 'Jadwal ujian tidak dapat dihapus karena sudah memiliki hasil ujian')
                    ->with('delete_failed', $jadwal->id); // penting agar tombol muncul
            }

            // Ambil semua sesi ruangan yang terkait
            $sesiIds = $jadwal->sesiRuangans()->pluck('sesi_ruangan.id')->toArray();

            // Lepaskan relasi jadwal <-> sesi
            $jadwal->sesiRuangans()->detach();

            // Cek setiap sesi, apakah masih dipakai jadwal lain
            foreach ($sesiIds as $sesiId) {
                $sesi = SesiRuangan::find($sesiId);
                if ($sesi && $sesi->jadwalUjians()->count() === 0) {
                    // Kalau sesi sudah tidak dipakai jadwal lain, hapus
                    $sesi->delete();
                }
            }

            // Hapus jadwal
            $jadwal->delete();

            return redirect()->route('naskah.jadwal.index')
                ->with('success', 'Jadwal ujian berhasil dihapus beserta sesi ruangan yang tidak terpakai');
        } catch (\Exception $e) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Terjadi kesalahan saat menghapus jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Bulk actions for jadwal ujian
     */
    public function bulkAssignSourceSesiAndEnrollChunk(Request $request)
    {
        $validated = $request->validate([
            'jadwal_ids' => 'required|array|min:1|max:3',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'sesi_ids' => 'required|array|min:1',
            'sesi_ids.*' => 'exists:sesi_ruangan,id',
            'chunk_index' => 'nullable|integer|min:0',
            'total_chunks' => 'nullable|integer|min:1',
        ]);

        $summary = [
            'success' => true,
            'chunk_index' => $validated['chunk_index'] ?? null,
            'total_chunks' => $validated['total_chunks'] ?? null,
            'processed' => 0,
            'attached' => 0,
            'enrolled' => 0,
            'updated' => 0,
            'skipped' => 0,
            'warnings' => [],
            'errors' => [],
        ];

        try {
            DB::transaction(function () use ($validated, &$summary) {
                $jadwals = JadwalUjian::with(['tahunAjaran', 'mapel'])
                    ->whereIn('id', $validated['jadwal_ids'])
                    ->get()
                    ->keyBy('id');

                foreach ($validated['jadwal_ids'] as $jadwalId) {
                    $jadwal = $jadwals->get((int) $jadwalId);

                    if (!$jadwal) {
                        $summary['errors'][] = "Jadwal ID {$jadwalId} tidak ditemukan.";
                        continue;
                    }

                    if ($jadwal->tahunAjaran?->isReadOnly()) {
                        $summary['warnings'][] = "Jadwal '{$jadwal->judul}' dilewati karena berada di tahun ajaran arsip.";
                        continue;
                    }

                    $result = $this->assignSourceSesiAndEnrollForJadwal($jadwal, $validated['sesi_ids']);

                    if ($result['source_count'] === 0) {
                        $summary['warnings'][] = "Tidak ada sesi sumber yang cocok untuk jadwal '{$jadwal->judul}'.";
                        continue;
                    }

                    $summary['processed']++;
                    $summary['attached'] += $result['attached'];
                    $summary['enrolled'] += $result['enrolled'];
                    $summary['updated'] += $result['updated'];
                    $summary['skipped'] += $result['skipped'];
                }
            });

            return response()->json($summary);
        } catch (\Throwable $e) {
            $summary['success'] = false;
            $summary['errors'][] = 'Terjadi kesalahan saat memproses batch: ' . $e->getMessage();

            return response()->json($summary, 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,force_delete,status_change,assign_source_sesi_enroll',
            'jadwal_ids' => 'required|array|min:1',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'new_status' => 'nullable|in:draft,aktif,nonaktif,selesai',
            'sesi_ids' => 'required_if:action,assign_source_sesi_enroll|array|min:1',
            'sesi_ids.*' => 'exists:sesi_ruangan,id',
        ]);

        $jadwalIds = $request->jadwal_ids;
        $action = $request->action;

        try {
            switch ($action) {
                case 'delete':
                    $count = $this->bulkDelete($jadwalIds);
                    return redirect()->route('naskah.jadwal.index')
                        ->with('success', "Berhasil menghapus {$count} jadwal ujian");

                case 'status_change':
                    $newStatus = $request->new_status;
                    $count = $this->bulkStatusChange($jadwalIds, $newStatus);
                    return redirect()->route('naskah.jadwal.index')
                        ->with('success', "Berhasil mengubah status {$count} jadwal ujian menjadi {$newStatus}");
                case 'force_delete':
                    $count = $this->bulkForceDelete($jadwalIds);
                    return redirect()->route('naskah.jadwal.index')
                        ->with('success', "Berhasil menghapus paksa {$count} jadwal ujian");
                case 'assign_source_sesi_enroll':
                    $result = $this->bulkAssignSourceSesiAndEnroll($jadwalIds, $request->sesi_ids ?? []);
                    $message = "Berhasil memproses {$result['jadwal_processed']} jadwal: {$result['attached']} sesi ditambahkan, {$result['enrolled']} siswa di-enroll";
                    if ($result['updated'] > 0) {
                        $message .= ", {$result['updated']} enrollment diperbarui";
                    }
                    if ($result['skipped'] > 0) {
                        $message .= ", {$result['skipped']} siswa dilewati";
                    }
                    if (!empty($result['warnings'])) {
                        session()->flash('warning', implode(' ', $result['warnings']));
                    }

                    return redirect()->route('naskah.jadwal.index')
                        ->with('success', $message);

                default:
                    return redirect()->route('naskah.jadwal.index')
                        ->with('error', 'Aksi tidak valid');
            }
        } catch (\Exception $e) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete jadwal ujian
     */
    private function bulkDelete($jadwalIds)
    {
        $count = 0;
        $errors = [];

        foreach ($jadwalIds as $id) {
            $jadwal = JadwalUjian::find($id);
            if ($jadwal) {
                if ($jadwal->tahunAjaran?->isReadOnly()) {
                    $errors[] = "Jadwal '{$jadwal->judul}' berada di tahun ajaran arsip dan tidak dapat dihapus";
                    continue;
                }

                // Check if there are any results associated
                if ($jadwal->hasilUjian()->count() > 0) {
                    $errors[] = "Jadwal '{$jadwal->judul}' tidak dapat dihapus karena sudah memiliki hasil ujian";
                    continue;
                }

                $jadwal->delete();
                $count++;
            }
        }

        if (!empty($errors)) {
            session()->flash('warning', implode(', ', $errors));
        }

        return $count;
    }

    private function bulkAssignSourceSesiAndEnroll(array $jadwalIds, array $sesiIds): array
    {
        $summary = [
            'jadwal_processed' => 0,
            'attached' => 0,
            'enrolled' => 0,
            'updated' => 0,
            'skipped' => 0,
            'warnings' => [],
        ];

        DB::transaction(function () use ($jadwalIds, $sesiIds, &$summary) {
            $jadwals = JadwalUjian::with(['tahunAjaran', 'mapel'])
                ->whereIn('id', $jadwalIds)
                ->get();

            foreach ($jadwals as $jadwal) {
                if ($jadwal->tahunAjaran?->isReadOnly()) {
                    $summary['warnings'][] = "Jadwal '{$jadwal->judul}' dilewati karena berada di tahun ajaran arsip.";
                    continue;
                }

                $result = $this->assignSourceSesiAndEnrollForJadwal($jadwal, $sesiIds);

                if ($result['source_count'] === 0) {
                    $summary['warnings'][] = "Tidak ada sesi sumber yang cocok untuk jadwal '{$jadwal->judul}'.";
                    continue;
                }

                $summary['jadwal_processed']++;
                $summary['attached'] += $result['attached'];
                $summary['enrolled'] += $result['enrolled'];
                $summary['updated'] += $result['updated'];
                $summary['skipped'] += $result['skipped'];
            }
        });

        return $summary;
    }

    public function forceDestroy(JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        // Hapus semua pelanggaran ujian terkait hasil ujian
        foreach ($jadwal->hasilUjian as $hasil) {
            $hasil->pelanggaranUjian()->delete();
        }

        // Hapus semua hasil ujian terkait
        $jadwal->hasilUjian()->delete();

        // Ambil semua sesi ruangan yang terkait
        $sesiIds = $jadwal->sesiRuangans()->pluck('sesi_ruangan.id')->toArray();

        // Lepaskan relasi jadwal <-> sesi
        $jadwal->sesiRuangans()->detach();

        // Cek setiap sesi, apakah masih dipakai jadwal lain
        foreach ($sesiIds as $sesiId) {
            $sesi = SesiRuangan::find($sesiId);
            if ($sesi && $sesi->jadwalUjians()->count() === 0) {
                $sesi->delete();
            }
        }

        // Hapus jadwal
        $jadwal->delete();

        return redirect()->route('naskah.jadwal.index')
            ->with('success', 'Jadwal ujian dan seluruh data terkait berhasil dihapus secara paksa');
    }

    private function bulkForceDelete($jadwalIds)
    {
        $count = 0;

        foreach ($jadwalIds as $id) {
            $jadwal = JadwalUjian::find($id);

            if ($jadwal) {
                if ($jadwal->tahunAjaran?->isReadOnly()) {
                    continue;
                }

                // Hapus semua pelanggaran ujian terkait hasil ujian
                foreach ($jadwal->hasilUjian as $hasil) {
                    $hasil->pelanggaranUjian()->delete();
                }

                // Hapus semua hasil ujian
                $jadwal->hasilUjian()->delete();

                // Ambil semua sesi ruangan yang terkait
                $sesiIds = $jadwal->sesiRuangans()->pluck('sesi_ruangan.id')->toArray();

                // Lepaskan relasi jadwal <-> sesi
                $jadwal->sesiRuangans()->detach();

                // Cek setiap sesi, apakah masih dipakai jadwal lain
                foreach ($sesiIds as $sesiId) {
                    $sesi = SesiRuangan::find($sesiId);
                    if ($sesi && $sesi->jadwalUjians()->count() === 0) {
                        $sesi->delete();
                    }
                }

                // Hapus jadwal
                $jadwal->delete();
                $count++;
            }
        }

        return $count;
    }

    /**
     * Bulk status change for jadwal ujian
     */
    private function bulkStatusChange($jadwalIds, $newStatus)
    {
        return JadwalUjian::whereIn('id', $jadwalIds)
            ->whereDoesntHave('tahunAjaran', fn($q) => $q->where('status', 'arsip'))
            ->update(['status' => $newStatus]);
    }

    /**
     * Re-assign sesi ruangan untuk jadwal ujian
     */
    public function reassignSesi(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $sesiAssignmentService = new SesiAssignmentService();

        // Clean up existing assignments first
        $cleanedCount = $sesiAssignmentService->cleanupAssignments($jadwal);

        // Re-assign based on current date
        $assignedCount = $sesiAssignmentService->autoAssignSesiByDate($jadwal);

        $message = "Berhasil memperbarui assignment sesi ruangan.";
        if ($cleanedCount > 0) {
            $message .= " {$cleanedCount} sesi tidak sesuai dihapus.";
        }
        if ($assignedCount > 0) {
            $message .= " {$assignedCount} sesi baru ditambahkan.";
        }

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', $message);
    }

    /**
     * Toggle auto assignment untuk jadwal ujian
     */
    public function toggleAutoAssign(Request $request, JadwalUjian $jadwal)
    {
        if ($jadwal->tahunAjaran?->isReadOnly()) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $autoAssign = $request->get('auto_assign', false);

        $jadwal->update([
            'auto_assign_sesi' => $autoAssign
        ]);

        // If enabling auto assign, run assignment now
        if ($autoAssign) {
            $sesiAssignmentService = new SesiAssignmentService();
            $assignedCount = $sesiAssignmentService->autoAssignSesiByDate($jadwal);

            if ($assignedCount > 0) {
                session()->flash('info', "Auto assignment diaktifkan dan {$assignedCount} sesi berhasil ditambahkan.");
            }
        }

        $message = $autoAssign ? 'Auto assignment sesi diaktifkan' : 'Auto assignment sesi dinonaktifkan';

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', $message);
    }

    /**
     * Switch scheduling mode untuk jadwal ujian
     */
    public function switchSchedulingMode(Request $request, JadwalUjian $jadwal)
    {
        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('warning', 'Mode penjadwalan sudah tidak digunakan.');
    }

    /**
     * Apply exam schedule to all session rooms with the same date
     */
    public function applyToSessions(JadwalUjian $jadwal)
    {
        try {
            if ($jadwal->tahunAjaran?->isReadOnly()) {
                return redirect()->route('naskah.jadwal.show', $jadwal->id)
                    ->with('error', 'Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
            }

            // Since sesi ruangan no longer has tanggal field, we'll match based on other criteria
            $matchingSessions = SesiRuangan::whereDoesntHave('jadwalUjians', function ($query) use ($jadwal) {
                $query->where('jadwal_ujian_id', $jadwal->id);
            })
                ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                ->get();

            if ($matchingSessions->isEmpty()) {
                return redirect()->route('naskah.jadwal.show', $jadwal->id)
                    ->with('info', 'Tidak ditemukan sesi ruangan yang dapat dikaitkan dengan jadwal ini.');
            }

            $attachedCount = 0;

            foreach ($matchingSessions as $sesi) {
                // Check if this sesi is compatible with the jadwal's mapel and jurusan
                // If the mapel's jurusan is null, it applies to all jurusan
                $isCompatible = true;

                // If the sesi has students, check their classes' jurusan against the jadwal's mapel jurusan
                if ($sesi->sesiRuanganSiswa()->count() > 0) {
                    $siswaList = $sesi->sesiRuanganSiswa()->with('siswa.kelas', 'siswa.tahunAjaranRecords.kelas')->get();
                    foreach ($siswaList as $sesiSiswa) {
                        $kelas = $sesiSiswa->siswa ? $this->resolveSiswaKelas($sesiSiswa->siswa, $jadwal) : null;
                        if ($kelas) {
                            $kelasJurusan = $kelas->jurusan;

                            // Skip this sesi if the jadwal doesn't apply to the student's jurusan
                            // unless the mapel's jurusan is null (applies to all)
                            if (!$jadwal->appliesToJurusan($kelasJurusan)) {
                                $isCompatible = false;
                                break;
                            }
                        }
                    }
                }

                // Only attach if compatible
                if ($isCompatible) {
                    // Add the relationship
                    $jadwal->sesiRuangans()->attach($sesi->id);
                    $attachedCount++;
                }
            }

            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('success', "Berhasil menerapkan jadwal ujian ke {$attachedCount} sesi ruangan.");
        } catch (\Exception $e) {
            return redirect()->route('naskah.jadwal.show', $jadwal->id)
                ->with('error', 'Gagal menerapkan ke sesi: ' . $e->getMessage());
        }
    }

    public function storeSusulan(Request $request)
    {
        $request->validate([
            'jadwal_ids' => 'required|array',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ]);

        DB::beginTransaction();
        try {
            $jadwalAsliList = JadwalUjian::with('tahunAjaran')
                ->whereIn('id', $request->jadwal_ids)
                ->get();
            $tahunAjaranId = $jadwalAsliList->first()?->tahun_ajaran_id;
            $paketUjianId = $jadwalAsliList->first()?->paket_ujian_id;

            if (!$tahunAjaranId || $jadwalAsliList->contains(fn($jadwal) => $jadwal->tahun_ajaran_id !== $tahunAjaranId)) {
                throw new \Exception('Semua jadwal susulan harus berada pada tahun ajaran yang sama.');
            }

            if ($jadwalAsliList->contains(fn($jadwal) => $jadwal->tahunAjaran?->isReadOnly())) {
                throw new \Exception('Jadwal pada tahun ajaran arsip hanya dapat dilihat.');
            }

            // Hitung total siswa yang akan diikutkan (dari semua jadwal asli)
            $totalSiswa = 0;
            foreach ($request->jadwal_ids as $id) {
                $totalSiswa += EnrollmentUjian::where('jadwal_ujian_id', $id)
                    ->where('status_enrollment', 'enrolled')
                    ->count();
            }

            // 1. Buat ruangan baru
            $ruangan = Ruangan::create([
                'tahun_ajaran_id' => $tahunAjaranId,
                'kode_ruangan' => 'RS' . strtoupper(Str::random(2)),
                'nama_ruangan' => 'Ruang Ujian Susulan ' . date('Y-m-d H:i'),
                'kapasitas' => $totalSiswa,
                'status' => 'aktif',
                'paket_ujian_id' => $paketUjianId,
                // tambahkan field lain sesuai kebutuhan (lokasi, dll)
            ]);

            // 2. Buat sesi ruangan
            $sesi = SesiRuangan::create([
                'tahun_ajaran_id' => $tahunAjaranId,
                'ruangan_id' => $ruangan->id,
                'nama_sesi' => 'Sesi Ujian Susulan',
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => 'belum_mulai',
                'paket_ujian_id' => $paketUjianId,
                'pengaturan' => null,
            ]);

            // 3. Proses setiap jadwal yang dipilih
            foreach ($request->jadwal_ids as $jadwalId) {
                $jadwalAsli = JadwalUjian::findOrFail($jadwalId);

                // Duplikasi jadwal
                $jadwalBaru = $jadwalAsli->replicate();
                $jadwalBaru->judul = 'Susulan - ' . $jadwalAsli->judul;
                $jadwalBaru->tanggal = $request->tanggal;
                $jadwalBaru->status = 'aktif';
                $jadwalBaru->kode_ujian = 'S' . date('Ymd') . strtoupper(Str::random(5));
                $jadwalBaru->created_by = auth()->id();
                $jadwalBaru->tahun_ajaran_id = $tahunAjaranId;
                $jadwalBaru->paket_ujian_id = $paketUjianId;
                $jadwalBaru->auto_assign_sesi = false; // kita attach manual
                $jadwalBaru->auto_enroll = false;       // kita enroll manual
                $jadwalBaru->save();

                // Hubungkan jadwal baru dengan sesi
                $jadwalBaru->sesiRuangans()->attach($sesi->id);

                // Ambil semua siswa dengan status enrolled dari jadwal asli
                $enrollments = EnrollmentUjian::where('jadwal_ujian_id', $jadwalId)
                    ->where('status_enrollment', 'enrolled')
                    ->with('siswa')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $siswa = $enrollment->siswa;

                    // Daftarkan siswa ke sesi (sesi_ruangan_siswa) – hanya jika belum ada
                    $exists = SesiRuanganSiswa::where('sesi_ruangan_id', $sesi->id)
                        ->where('siswa_id', $siswa->id)
                        ->exists();

                    if (!$exists) {
                        SesiRuanganSiswa::create([
                            'sesi_ruangan_id' => $sesi->id,
                            'siswa_id' => $siswa->id,
                            'status_kehadiran' => 'tidak_hadir',
                        ]);
                    }

                    // Buat enrollment baru untuk jadwal susulan – hanya jika belum ada
                    $enrollmentExists = EnrollmentUjian::where('siswa_id', $siswa->id)
                        ->where('jadwal_ujian_id', $jadwalBaru->id)
                        ->exists();

                    if (!$enrollmentExists) {
                        EnrollmentUjian::create([
                            'siswa_id' => $siswa->id,
                            'jadwal_ujian_id' => $jadwalBaru->id,
                            'sesi_ruangan_id' => $sesi->id,
                            'status_enrollment' => 'enrolled',
                            'catatan' => 'Ujian susulan',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('naskah.jadwal.index')
                ->with('success', 'Ujian susulan berhasil dibuat untuk ' . count($request->jadwal_ids) . ' jadwal.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal membuat ujian susulan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}
