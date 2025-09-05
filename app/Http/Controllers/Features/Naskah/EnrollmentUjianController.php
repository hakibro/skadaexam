<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EnrollmentUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjian']);

        // Apply filters
        if ($request->filled('jadwal_id')) {
            $query->whereHas('sesiRuangan', function ($q) use ($request) {
                $q->where('jadwal_ujian_id', $request->jadwal_id);
            });
        }

        if ($request->filled('sesi_id')) {
            $query->where('sesi_ruangan_id', $request->sesi_id);
        }

        if ($request->filled('status')) {
            $query->where('status_enrollment', $request->status);
        }

        if ($request->filled('kehadiran')) {
            $query->where('status_kehadiran', $request->kehadiran);
        }

        $perPage = $request->get('per_page', 15);
        $enrollments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Data untuk filter dropdown
        $jadwalUjians = JadwalUjian::where('status', 'active')
            ->orderBy('tanggal', 'desc')
            ->get();

        $sesiRuangans = SesiRuangan::where('status', '!=', 'cancelled')
            ->orderBy('waktu_mulai', 'desc')
            ->get();

        return view('features.naskah.enrollment_ujian.index', compact('enrollments', 'jadwalUjians', 'sesiRuangans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jadwalUjians = JadwalUjian::where('status', 'active')->orderBy('tanggal', 'desc')->get();
        $sesiRuangans = collect(); // Will be populated via AJAX
        $kelasList = Kelas::orderBy('nama')->get();

        return view('features.naskah.enrollment_ujian.create', compact('jadwalUjians', 'sesiRuangans', 'kelasList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id'
        ]);

        try {
            DB::beginTransaction();

            $sesiRuangan = SesiRuangan::with('jadwalUjian')->findOrFail($request->sesi_ruangan_id);

            $enrolled = 0;
            $skipped = 0;

            foreach ($request->siswa_ids as $siswaId) {
                // Check if enrollment already exists
                $existingEnrollment = EnrollmentUjian::where('siswa_id', $siswaId)
                    ->where('sesi_ruangan_id', $sesiRuangan->id)
                    ->first();

                if ($existingEnrollment) {
                    $skipped++;
                    continue;
                }

                // Create new enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswaId,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $sesiRuangan->jadwal_ujian_id,
                    'status_enrollment' => 'enrolled',
                    'status_kehadiran' => 'belum_hadir',
                ]);

                $enrolled++;
            }

            DB::commit();

            if ($enrolled > 0) {
                $message = "{$enrolled} siswa berhasil didaftarkan ke ujian.";
                if ($skipped > 0) {
                    $message .= " {$skipped} siswa dilewati karena sudah terdaftar.";
                }
                return redirect()->route('naskah.enrollment-ujian.index')->with('success', $message);
            } else {
                return redirect()->back()->with('warning', 'Tidak ada siswa yang didaftarkan. Semua siswa sudah terdaftar sebelumnya.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error enrolling students to exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $enrollment = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjian', 'hasilUjian'])
            ->findOrFail($id);

        return view('features.naskah.enrollment_ujian.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $enrollment = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjian'])
            ->findOrFail($id);

        $jadwalUjian = $enrollment->sesiRuangan->jadwalUjian;
        $sesiRuangans = SesiRuangan::where('jadwal_ujian_id', $jadwalUjian->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('features.naskah.enrollment_ujian.edit', compact('enrollment', 'sesiRuangans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'status_enrollment' => 'required|in:enrolled,completed,absent,cancelled',
            'status_kehadiran' => 'required|in:belum_hadir,hadir,tidak_hadir',
            'catatan' => 'nullable|string'
        ]);

        try {
            $enrollment = EnrollmentUjian::findOrFail($id);

            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);
            $oldSesiRuangan = SesiRuangan::findOrFail($enrollment->sesi_ruangan_id);

            // Check if sesi belongs to same jadwal
            if ($sesiRuangan->jadwal_ujian_id != $oldSesiRuangan->jadwal_ujian_id) {
                return redirect()->back()->withInput()
                    ->with('error', 'Sesi ruangan harus berasal dari jadwal ujian yang sama.');
            }

            $enrollment->update([
                'sesi_ruangan_id' => $request->sesi_ruangan_id,
                'status_enrollment' => $request->status_enrollment,
                'status_kehadiran' => $request->status_kehadiran,
                // Jika siswa hadir, dan belum ada token, atau token sudah digunakan, generate token baru
                'token_login' => ($request->status_kehadiran == 'hadir' &&
                    (!$enrollment->token_login || $enrollment->token_digunakan_pada)) ?
                    strtoupper(substr(md5(time() . $enrollment->siswa_id . rand(1000, 9999)), 0, 8)) :
                    $enrollment->token_login,
                'token_dibuat_pada' => ($request->status_kehadiran == 'hadir' &&
                    (!$enrollment->token_login || $enrollment->token_digunakan_pada)) ?
                    Carbon::now() : $enrollment->token_dibuat_pada,
            ]);

            return redirect()->route('naskah.enrollment-ujian.show', $enrollment->id)
                ->with('success', 'Data pendaftaran ujian berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating exam enrollment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $enrollment = EnrollmentUjian::findOrFail($id);

            // Check if there's already a hasil ujian
            if ($enrollment->hasilUjian()->exists()) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus pendaftaran karena sudah memiliki hasil ujian.');
            }

            $enrollment->delete();

            return redirect()->route('naskah.enrollment-ujian.index')
                ->with('success', 'Pendaftaran ujian berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting exam enrollment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get available sesi ruangan for a given jadwal ujian.
     */
    public function getSesiOptions(Request $request)
    {
        $jadwalId = $request->jadwal_id;

        $sesiList = SesiRuangan::where('jadwal_ujian_id', $jadwalId)
            ->where('status', '!=', 'cancelled')
            ->get(['id', 'nama', 'waktu_mulai', 'status'])
            ->map(function ($sesi) {
                return [
                    'id' => $sesi->id,
                    'text' => $sesi->nama . ' - ' . $sesi->waktu_mulai->format('d M Y H:i') . ' (' . $sesi->status . ')'
                ];
            });

        return response()->json($sesiList);
    }

    /**
     * Get all students from selected kelas (AJAX)
     */
    public function getSiswaByKelas(Request $request)
    {
        $request->validate([
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
            'sesi_id' => 'required|exists:sesi_ruangan,id'
        ]);

        try {
            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_id);

            // Get already enrolled students for this session
            $enrolledSiswaIds = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuangan->id)
                ->pluck('siswa_id')
                ->toArray();

            // Get all students from the selected classes who are not already enrolled
            $siswaList = Siswa::whereIn('kelas_id', $request->kelas_ids)
                ->whereNotIn('id', $enrolledSiswaIds)
                ->with('kelas')
                ->get(['id', 'nis', 'nama', 'kelas_id'])
                ->map(function ($siswa) {
                    return [
                        'id' => $siswa->id,
                        'nis' => $siswa->nis,
                        'nama' => $siswa->nama,
                        'kelas' => $siswa->kelas->nama ?? 'Tidak ada kelas'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $siswaList,
                'count' => $siswaList->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting students by kelas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign students to an exam and session.
     */
    public function bulkEnrollment(Request $request)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        try {
            DB::beginTransaction();

            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);

            // Get all students from selected kelas
            $siswaList = Siswa::whereIn('kelas_id', $request->kelas_ids)
                ->where('status', 'active')
                ->get();

            // Get already enrolled students
            $enrolledSiswaIds = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuangan->id)
                ->pluck('siswa_id')
                ->toArray();

            $enrollmentCount = 0;
            $skippedCount = 0;

            foreach ($siswaList as $siswa) {
                // Skip if already enrolled
                if (in_array($siswa->id, $enrolledSiswaIds)) {
                    $skippedCount++;
                    continue;
                }

                // Create new enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswa->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $sesiRuangan->jadwal_ujian_id,
                    'status_enrollment' => 'enrolled',
                    'status_kehadiran' => 'belum_hadir',
                ]);

                $enrollmentCount++;
            }

            DB::commit();

            if ($enrollmentCount > 0) {
                $message = "{$enrollmentCount} siswa berhasil didaftarkan ke ujian.";
                if ($skippedCount > 0) {
                    $message .= " {$skippedCount} siswa dilewati karena sudah terdaftar.";
                }
                return redirect()->route('naskah.enrollment-ujian.index')->with('success', $message);
            } else {
                return redirect()->back()->with('warning', 'Tidak ada siswa yang didaftarkan. Semua siswa sudah terdaftar sebelumnya.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk enrolling students', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate tokens for all students in a session
     */
    public function generateTokens(Request $request)
    {
        $request->validate([
            'sesi_id' => 'required|exists:sesi_ruangan,id'
        ]);

        try {
            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_id);

            // Check authorization
            if (
                !Auth::user()->hasRole(['admin', 'naskah']) &&
                $sesiRuangan->guru_id != Auth::id()
            ) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki izin untuk generate token pada sesi ini.');
            }

            // Get enrollments that need tokens
            $enrollments = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuangan->id)
                ->where(function ($query) {
                    $query->whereNull('token_login')
                        ->orWhereNull('token_dibuat_pada')
                        ->orWhere('token_dibuat_pada', '<', Carbon::now()->subHours(2));
                })
                ->get();

            $generateCount = 0;

            foreach ($enrollments as $enrollment) {
                $enrollment->generateToken();
                $generateCount++;
            }

            return redirect()->back()->with('success', "Token berhasil dibuat untuk {$generateCount} siswa.");
        } catch (\Exception $e) {
            Log::error('Error generating tokens', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat generate token: ' . $e->getMessage());
        }
    }
}
