<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EnrollmentUjianController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService = null)
    {
        $this->enrollmentService = $enrollmentService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjian', 'sesiRuanganSiswa']);

        // Apply filters
        if ($request->filled('jadwal_id')) {
            $query->whereHas('sesiRuangan.jadwalUjians', function ($q) use ($request) {
                $q->where('jadwal_ujian.id', $request->jadwal_id);
            });
        }

        if ($request->filled('sesi_id')) {
            $query->where('sesi_ruangan_id', $request->sesi_id);
        }

        if ($request->filled('status')) {
            $query->where('status_enrollment', $request->status);
        }

        if ($request->filled('kehadiran')) {
            $query->whereHas('sesiRuanganSiswa', function ($q) use ($request) {
                $q->where('status_kehadiran', $request->kehadiran);
            });
        }

        $perPage = $request->get('per_page', 15);
        $enrollments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Data untuk filter dropdown
        $jadwalUjians = JadwalUjian::where('status', 'aktif')
            ->orderBy('tanggal', 'desc')
            ->get();

        $sesiRuangans = SesiRuangan::whereIn('status', ['belum_mulai', 'berlangsung'])
            ->orderBy('waktu_mulai', 'desc')
            ->get();
        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('features.naskah.enrollment_ujian.index', compact('enrollments', 'jadwalUjians', 'sesiRuangans', 'kelasList'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jadwalUjians = JadwalUjian::where('status', 'aktif')->orderBy('tanggal', 'desc')->get();
        $sesiRuangans = collect(); // Will be populated via AJAX
        $kelasList = Kelas::orderBy('nama_kelas')->get();

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

            $sesiRuangan = SesiRuangan::with('jadwalUjians')->findOrFail($request->sesi_ruangan_id);

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

                // Get the first jadwal ujian from the sesi ruangan relationship
                $jadwalUjian = $sesiRuangan->jadwalUjians()->first();

                // Create new enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswaId,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $jadwalUjian ? $jadwalUjian->id : null,
                    'status_enrollment' => 'enrolled',
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
        $enrollment = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjian', 'hasilUjian', 'sesiRuanganSiswa'])
            ->findOrFail($id);

        return view('features.naskah.enrollment_ujian.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $enrollment = EnrollmentUjian::with(['siswa', 'sesiRuangan.jadwalUjians'])
            ->findOrFail($id);

        // Get the first jadwal ujian from the sesi ruangan
        $jadwalUjian = $enrollment->sesiRuangan->jadwalUjians->first();

        if ($jadwalUjian) {
            // Find sesi ruangans that are associated with this jadwal ujian
            $sesiRuangans = SesiRuangan::whereHas('jadwalUjians', function ($query) use ($jadwalUjian) {
                $query->where('jadwal_ujian.id', $jadwalUjian->id);
            })
                ->whereIn('status', ['belum_mulai', 'berlangsung'])
                ->get();
        } else {
            $sesiRuangans = collect(); // Empty collection if no jadwal ujian
        }

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
            'catatan' => 'nullable|string'
        ]);

        try {
            $enrollment = EnrollmentUjian::findOrFail($id);

            $sesiRuangan = SesiRuangan::with('jadwalUjians')->findOrFail($request->sesi_ruangan_id);
            $oldSesiRuangan = SesiRuangan::with('jadwalUjians')->findOrFail($enrollment->sesi_ruangan_id);

            // Check if both sesi belong to same jadwal ujians
            $newJadwalIds = $sesiRuangan->jadwalUjians->pluck('id')->toArray();
            $oldJadwalIds = $oldSesiRuangan->jadwalUjians->pluck('id')->toArray();

            // Check if there's any common jadwal ujian between the two sesi
            if (empty(array_intersect($newJadwalIds, $oldJadwalIds))) {
                return redirect()->back()->withInput()
                    ->with('error', 'Sesi ruangan harus berasal dari jadwal ujian yang sama.');
            }

            $enrollment->update([
                'sesi_ruangan_id' => $request->sesi_ruangan_id,
                'status_enrollment' => $request->status_enrollment,
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

        if (!$jadwalId) {
            return response()->json([]);
        }

        // Use the many-to-many relationship through jadwal_ujian_sesi_ruangan pivot table
        $sesiList = SesiRuangan::whereHas('jadwalUjians', function ($query) use ($jadwalId) {
            $query->where('jadwal_ujian.id', $jadwalId);
        })
            ->whereIn('status', ['belum_mulai', 'berlangsung'])
            ->with(['jadwalUjians' => function ($query) use ($jadwalId) {
                $query->where('jadwal_ujian.id', $jadwalId);
            }])
            ->get(['id', 'nama_sesi', 'waktu_mulai', 'waktu_selesai', 'status'])
            ->map(function ($sesi) {
                $jadwalUjian = $sesi->jadwalUjians->first();
                $tanggal = $jadwalUjian ? $jadwalUjian->tanggal->format('d M Y') : 'N/A';

                return [
                    'id' => $sesi->id,
                    'text' => $sesi->nama_sesi . ' - ' . $tanggal . ' ' . $sesi->waktu_mulai . ' (' . ucfirst($sesi->status) . ')'
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
     * Get students for Select2 AJAX (for create form)
     */
    public function getSiswaOptions(Request $request)
    {
        $search = $request->get('search', '');
        $kelasId = $request->get('kelas_id');

        $query = Siswa::with('kelas')
            ->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%");
            });

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        $siswaList = $query->limit(50)->get()
            ->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'text' => $siswa->nama . ' (' . ($siswa->nis ?? $siswa->idyayasan) . ') - ' . ($siswa->kelas->nama ?? 'No Class')
                ];
            });

        return response()->json($siswaList);
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
                // Get the first jadwal ujian from the sesi ruangan relationship
                $jadwalUjian = $sesiRuangan->jadwalUjians()->first();

                EnrollmentUjian::create([
                    'siswa_id' => $siswa->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $jadwalUjian ? $jadwalUjian->id : null,
                    'status_enrollment' => 'enrolled',
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
     * Update enrollment status
     */
    public function updateStatus(EnrollmentUjian $enrollmentUjian, $status)
    {
        $validStatuses = ['enrolled', 'completed', 'absent', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('error', 'Status tidak valid');
        }

        try {
            $enrollmentUjian->update([
                'status_enrollment' => $status,
            ]);

            // Update kehadiran status in sesi_ruangan_siswa if needed
            if (in_array($status, ['completed', 'absent'])) {
                $sesiRuanganSiswa = \App\Models\SesiRuanganSiswa::where('sesi_ruangan_id', $enrollmentUjian->sesi_ruangan_id)
                    ->where('siswa_id', $enrollmentUjian->siswa_id)
                    ->first();

                if ($sesiRuanganSiswa) {
                    $sesiRuanganSiswa->update([
                        'status_kehadiran' => $status == 'completed' ? 'hadir' : 'tidak_hadir'
                    ]);
                }
            }

            $statusText = [
                'enrolled' => 'Terdaftar',
                'completed' => 'Selesai',
                'absent' => 'Tidak Hadir',
                'cancelled' => 'Dibatalkan'
            ];

            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('success', "Status berhasil diubah menjadi {$statusText[$status]}");
        } catch (\Exception $e) {
            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Synchronize enrollments based on session room assignments
     */
    public function syncEnrollments($sesiId)
    {
        try {
            $sesiRuangan = SesiRuangan::with(['jadwalUjian', 'sesiRuanganSiswa.siswa'])
                ->findOrFail($sesiId);

            $jadwalUjian = $sesiRuangan->jadwalUjian;

            if (!$jadwalUjian) {
                return redirect()->back()
                    ->with('error', 'Sesi ruangan ini tidak terkait dengan jadwal ujian manapun.');
            }

            // Get all students assigned to this session room
            $assignedStudents = $sesiRuangan->sesiRuanganSiswa;

            DB::beginTransaction();

            $enrolledCount = 0;
            $skippedCount = 0;

            foreach ($assignedStudents as $assignment) {
                // Check if student is already enrolled
                $existingEnrollment = EnrollmentUjian::where('siswa_id', $assignment->siswa_id)
                    ->where('jadwal_ujian_id', $jadwalUjian->id)
                    ->first();

                if ($existingEnrollment) {
                    // Update existing enrollment if needed
                    if ($existingEnrollment->sesi_ruangan_id != $sesiRuangan->id) {
                        $existingEnrollment->update([
                            'sesi_ruangan_id' => $sesiRuangan->id
                        ]);
                        $enrolledCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // Create new enrollment
                    EnrollmentUjian::create([
                        'siswa_id' => $assignment->siswa_id,
                        'jadwal_ujian_id' => $jadwalUjian->id,
                        'sesi_ruangan_id' => $sesiRuangan->id,
                        'status_enrollment' => 'enrolled',
                    ]);
                    $enrolledCount++;
                }
            }

            DB::commit();

            $message = "Sinkronisasi berhasil: {$enrolledCount} siswa di-enroll atau diperbarui";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} siswa dilewati (sudah terdaftar)";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyinkronkan enrollment: ' . $e->getMessage());
        }
    }

    /**
     * Print QR code for enrollment
     */
    public function printQR(EnrollmentUjian $enrollmentUjian)
    {
        return view('features.naskah.enrollment_ujian.print-qr', compact('enrollmentUjian'));
    }
}
