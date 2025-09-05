<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JadwalUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = JadwalUjian::with(['mapel', 'bankSoal', 'creator'])
            ->withCount('sesiRuangan');

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

        $jadwalUjians = $query->orderBy('tanggal', 'desc')->paginate(10);
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();

        return view('features.naskah.jadwal.index', compact('jadwalUjians', 'mapels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        // $bankSoals = BankSoal::orderBy('judul', 'asc')->get();
        $bankSoals = BankSoal::withCount('soals')->orderBy('judul', 'asc')->get();

        return view('features.naskah.jadwal.create', compact('mapels', 'bankSoals'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tanggal' => 'required|date',
            'durasi_menit' => 'required|integer|min:1',
            'jumlah_soal' => 'required|integer|min:1',
            'jenis_ujian' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        // Generate unique exam code
        $kodeUjian = 'U' . date('Ymd') . strtoupper(Str::random(5));

        // Create new jadwal ujian
        $jadwalUjian = JadwalUjian::create([
            'kode_ujian' => $kodeUjian,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $request->bank_soal_id,
            'jenis_ujian' => $request->jenis_ujian,
            'tanggal' => $request->tanggal,
            'durasi_menit' => $request->durasi_menit,
            'jumlah_soal' => $request->jumlah_soal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwalUjian->id)
            ->with('success', 'Jadwal ujian berhasil dibuat');
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
            // Return a super-simplified response first to test if the issue is in the controller or view
            file_put_contents($logFile, "Testing response\n", FILE_APPEND);

            // OPTION 1: Bare HTML response (uncomment to use)
            /*
            file_put_contents($logFile, "Using bare HTML response\n", FILE_APPEND);
            return response()->make('
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Basic Response Test</title>
                </head>
                <body>
                    <h1>Basic Response Test</h1>
                    <p>This is a test to see if a simple response works.</p>
                    <p>Jadwal ID: ' . $jadwal->id . '</p>
                    <p>Jadwal Title: ' . $jadwal->judul . '</p>
                    <p><a href="' . route('naskah.jadwal.index') . '">Back to List</a></p>
                </body>
                </html>
            ');
            */

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
                $jadwal->load('sesiRuangan');
                file_put_contents($logFile, "✓ Loaded sesiRuangan\n", FILE_APPEND);
            } catch (\Exception $e) {
                file_put_contents($logFile, "✗ Failed to load sesiRuangan: " . $e->getMessage() . "\n", FILE_APPEND);
            }

            // Log waktu_mulai and waktu_selesai to verify accessor methods
            file_put_contents($logFile, "Testing accessor methods\n", FILE_APPEND);
            file_put_contents($logFile, "waktu_mulai: " . ($jadwal->waktu_mulai ? $jadwal->waktu_mulai->format('H:i:s') : 'NULL') . "\n", FILE_APPEND);
            file_put_contents($logFile, "waktu_selesai: " . ($jadwal->waktu_selesai ? $jadwal->waktu_selesai->format('H:i:s') : 'NULL') . "\n", FILE_APPEND);

            // Log data being passed to view
            file_put_contents($logFile, "Data ready for view\n", FILE_APPEND);

            // OPTION 2: Ultra simple view (commented out now that it's working)
            /*
            file_put_contents($logFile, "Using ultra simple view\n", FILE_APPEND);
            return view('features.naskah.jadwal.ultra_simple', [
                'jadwal' => $jadwal, 
                'timestamp' => $timestamp,
                'uniqueId' => $uniqueId
            ]);
            */

            // OPTION 3: Standard debug view (now active since ultra-simple view works)
            file_put_contents($logFile, "Using standard debug view\n", FILE_APPEND);
            return view('features.naskah.jadwal.show', [
                'jadwal' => $jadwal,
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
        $mapels = Mapel::orderBy('nama_mapel', 'asc')->get();
        $bankSoals = BankSoal::orderBy('judul', 'asc')->get();

        return view('features.naskah.jadwal.edit', compact('jadwal', 'mapels', 'bankSoals'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JadwalUjian $jadwal)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel_id' => 'required|exists:mapel,id',
            'bank_soal_id' => 'required|exists:bank_soal,id',
            'tanggal' => 'required|date',
            'durasi_menit' => 'required|integer|min:1',
            'jumlah_soal' => 'required|integer|min:1',
            'jenis_ujian' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        $jadwal->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'mapel_id' => $request->mapel_id,
            'bank_soal_id' => $request->bank_soal_id,
            'jenis_ujian' => $request->jenis_ujian,
            'tanggal' => $request->tanggal,
            'durasi_menit' => $request->durasi_menit,
            'jumlah_soal' => $request->jumlah_soal,
            'acak_soal' => $request->has('acak_soal'),
            'acak_jawaban' => $request->has('acak_jawaban'),
            'tampilkan_hasil' => $request->has('tampilkan_hasil'),
        ]);

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', 'Jadwal ujian berhasil diperbarui');
    }

    /**
     * Update status of the resource.
     */
    public function updateStatus(Request $request, JadwalUjian $jadwal)
    {
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
        $request->validate([
            'sesi_id' => 'required|exists:sesi_ruangan,id',
        ]);

        $sesi = SesiRuangan::findOrFail($request->sesi_id);

        // Update the sesi with the new jadwal
        $sesi->update([
            'jadwal_ujian_id' => $jadwal->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil ditambahkan ke jadwal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JadwalUjian $jadwal)
    {
        // Check if there are any results associated with this exam
        if ($jadwal->hasilUjians()->count() > 0) {
            return redirect()->route('naskah.jadwal.index')
                ->with('error', 'Jadwal ujian tidak dapat dihapus karena sudah memiliki hasil ujian');
        }

        $jadwal->delete();

        return redirect()->route('naskah.jadwal.index')
            ->with('success', 'Jadwal ujian berhasil dihapus');
    }
}
