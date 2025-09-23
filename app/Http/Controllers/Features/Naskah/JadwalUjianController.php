<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Services\SesiAssignmentService;
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
            ->withCount('sesiRuangans');

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
        $bankSoals = BankSoal::withCount('soals')->orderBy('judul', 'asc')->get();
        $kelasList = Kelas::orderBy('tingkat')->orderBy('jurusan')->orderBy('nama_kelas')->get();

        return view('features.naskah.jadwal.create', compact('mapels', 'bankSoals', 'kelasList'));
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
            'scheduling_mode' => 'nullable|in:fixed,flexible',
            'auto_assign_sesi' => 'nullable|boolean',
            'kelas_target' => 'nullable|array',
            'kelas_target.*' => 'exists:kelas,id',
        ]);

        // Generate unique exam code
        $kodeUjian = 'U' . date('Ymd') . strtoupper(Str::random(5));

        // Get target classes based on mapel
        $kelasTarget = [];

        // If kelas_target is provided in the request, use it
        if ($request->has('kelas_target')) {
            $kelasTarget = $request->kelas_target;
        } else {
            // Otherwise, try to find matching classes based on mapel's tingkat and jurusan
            $mapel = Mapel::find($request->mapel_id);
            if ($mapel) {
                $query = Kelas::query();

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
            'aktifkan_auto_logout' => $request->has('aktifkan_auto_logout'),
            'scheduling_mode' => $request->get('scheduling_mode', 'flexible'),
            'auto_assign_sesi' => $request->get('auto_assign_sesi', true),
            'kelas_target' => $kelasTarget, // Add the kelas_target field
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Auto assign sesi ruangan if flexible scheduling is enabled
        if ($jadwalUjian->scheduling_mode === 'flexible' && $jadwalUjian->auto_assign_sesi) {
            $sesiAssignmentService = new SesiAssignmentService();
            $assignedCount = $sesiAssignmentService->autoAssignSesiByDate($jadwalUjian);

            if ($assignedCount > 0) {
                session()->flash('info', "Berhasil mengaitkan {$assignedCount} sesi ruangan secara otomatis berdasarkan tanggal yang sama.");
            }
        }

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

            // Standard debug view
            file_put_contents($logFile, "Using standard debug view\n", FILE_APPEND);
            return view('features.naskah.jadwal.show', [
                'jadwal' => $jadwal,
                'scheduleInfo' => $scheduleInfo,
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
        $kelasList = Kelas::orderBy('tingkat')->orderBy('jurusan')->orderBy('nama_kelas')->get();

        return view('features.naskah.jadwal.edit', compact('jadwal', 'mapels', 'bankSoals', 'kelasList'));
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
            'kelas_target' => 'nullable|array',
            'kelas_target.*' => 'exists:kelas,id',
        ]);

        // Get target classes based on mapel if not provided
        $kelasTarget = [];

        // If kelas_target is provided in the request, use it
        if ($request->has('kelas_target')) {
            $kelasTarget = $request->kelas_target;
        } else {
            // Otherwise, try to find matching classes based on mapel's tingkat and jurusan
            $mapel = Mapel::find($request->mapel_id);
            if ($mapel) {
                $query = Kelas::query();

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

        // Attach the sesi to the jadwal using the many-to-many relationship
        if (!$jadwal->sesiRuangans()->where('sesi_ruangan_id', $sesi->id)->exists()) {
            $jadwal->sesiRuangans()->attach($sesi->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi ujian berhasil ditambahkan ke jadwal'
        ]);
    }

    /**
     * Detach a sesi from this jadwal
     */
    public function detachSesi(Request $request, JadwalUjian $jadwal)
    {
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
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,force_delete,status_change',
            'jadwal_ids' => 'required|array|min:1',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
            'new_status' => 'nullable|in:draft,aktif,nonaktif,selesai'
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

    public function forceDestroy(JadwalUjian $jadwal)
    {
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
        return JadwalUjian::whereIn('id', $jadwalIds)->update(['status' => $newStatus]);
    }

    /**
     * Re-assign sesi ruangan untuk jadwal ujian
     */
    public function reassignSesi(Request $request, JadwalUjian $jadwal)
    {
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
        $autoAssign = $request->get('auto_assign', false);

        $jadwal->update([
            'auto_assign_sesi' => $autoAssign
        ]);

        // If enabling auto assign, run assignment now
        if ($autoAssign && $jadwal->scheduling_mode === 'flexible') {
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
        $request->validate([
            'scheduling_mode' => 'required|in:fixed,flexible'
        ]);

        $newMode = $request->scheduling_mode;
        $oldMode = $jadwal->scheduling_mode;

        $jadwal->update([
            'scheduling_mode' => $newMode
        ]);

        // Handle mode switching logic
        if ($newMode === 'flexible' && $oldMode === 'fixed') {
            // Switching to flexible - enable auto assignment
            $jadwal->update(['auto_assign_sesi' => true]);

            $sesiAssignmentService = new SesiAssignmentService();
            $assignedCount = $sesiAssignmentService->autoAssignSesiByDate($jadwal);

            $message = "Mode penjadwalan diubah ke fleksibel.";
            if ($assignedCount > 0) {
                $message .= " {$assignedCount} sesi ruangan berhasil dikaitkan.";
            }
        } elseif ($newMode === 'fixed' && $oldMode === 'flexible') {
            // Switching to fixed - clear sesi assignments
            $jadwal->sesiRuangans()->detach();
            $jadwal->update(['auto_assign_sesi' => false]);

            $message = "Mode penjadwalan diubah ke tetap. Semua kaitan sesi ruangan telah dihapus.";
        } else {
            $message = "Mode penjadwalan berhasil diperbarui.";
        }

        return redirect()->route('naskah.jadwal.show', $jadwal->id)
            ->with('success', $message);
    }

    /**
     * Apply exam schedule to all session rooms with the same date
     */
    public function applyToSessions(JadwalUjian $jadwal)
    {
        try {
            $jadwalDate = $jadwal->tanggal->format('Y-m-d');

            // Since sesi ruangan no longer has tanggal field, we'll match based on other criteria
            $matchingSessions = SesiRuangan::whereDoesntHave('jadwalUjians', function ($query) use ($jadwal) {
                $query->where('jadwal_ujian_id', $jadwal->id);
            })
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
                    $siswaList = $sesi->sesiRuanganSiswa()->with('siswa.kelas')->get();
                    foreach ($siswaList as $sesiSiswa) {
                        if ($sesiSiswa->siswa && $sesiSiswa->siswa->kelas) {
                            $kelasJurusan = $sesiSiswa->siswa->kelas->jurusan;

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
}
