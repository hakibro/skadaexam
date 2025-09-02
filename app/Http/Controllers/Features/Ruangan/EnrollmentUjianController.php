<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnrollmentUjianController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $jadwalId = $request->input('jadwal_id');
        $sesiId = $request->input('sesi_id');
        $status = $request->input('status');
        $kehadiran = $request->input('kehadiran');

        $query = EnrollmentUjian::with(['siswa.kelas', 'sesiRuangan', 'jadwalUjian']);

        if ($jadwalId) {
            $query->where('jadwal_ujian_id', $jadwalId);
        }

        if ($sesiId) {
            $query->where('sesi_ruangan_id', $sesiId);
        }

        if ($status) {
            $query->where('status_enrollment', $status);
        }

        if ($kehadiran) {
            $query->where('status_kehadiran', $kehadiran);
        }

        $enrollments = $query->latest()->paginate(15);
        $jadwalUjians = JadwalUjian::orderBy('judul')->get();
        $sesiRuangans = SesiRuangan::orderBy('nama_sesi')->get();
        $kelasList = Kelas::orderBy('nama')->get();

        return view('features.naskah.enrollment_ujian.index', compact(
            'enrollments',
            'jadwalUjians',
            'sesiRuangans',
            'kelasList'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jadwalUjians = JadwalUjian::orderBy('judul')->get();
        $kelasList = Kelas::orderBy('nama')->get();

        return view('features.naskah.enrollment_ujian.create', compact('jadwalUjians', 'kelasList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
            'sesi_ujian_id' => 'required|exists:sesi_ujians,id',
            'siswa_id' => 'required|exists:siswas,id',
            'catatan' => 'nullable|string|max:255',
        ]);

        try {
            // Check if student is already enrolled in this exam schedule
            $existingEnrollment = EnrollmentUjian::where('siswa_id', $request->siswa_id)
                ->whereHas('sesiUjian.jadwalUjian', function ($query) use ($request) {
                    $query->where('jadwal_ujians.id', $request->jadwal_ujian_id);
                })
                ->first();

            if ($existingEnrollment) {
                return redirect()
                    ->route('naskah.enrollment-ujian.create')
                    ->with('error', 'Siswa sudah terdaftar di jadwal ujian ini')
                    ->withInput();
            }

            // Create enrollment
            EnrollmentUjian::create([
                'siswa_id' => $request->siswa_id,
                'sesi_ujian_id' => $request->sesi_ujian_id,
                'status_enrollment' => 'enrolled',
                'status_kehadiran' => 'belum_hadir',
                'token_login' => $this->generateUniqueToken(),
                'token_dibuat_pada' => now(),
                'catatan' => $request->catatan,
            ]);

            return redirect()
                ->route('naskah.enrollment-ujian.index')
                ->with('success', 'Enrollment ujian berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()
                ->route('naskah.enrollment-ujian.create')
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EnrollmentUjian $enrollmentUjian)
    {
        $enrollment = $enrollmentUjian->load(['siswa.kelas', 'sesiUjian.jadwalUjian.mapel', 'hasilUjian']);

        return view('features.naskah.enrollment_ujian.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EnrollmentUjian $enrollmentUjian)
    {
        $enrollment = $enrollmentUjian->load(['siswa', 'sesiRuangan', 'jadwalUjian']);

        // Get all sessions for the same jadwal ujian
        $jadwalId = $enrollment->jadwal_ujian_id;
        
        $sesiRuangans = SesiRuangan::whereHas('beritaAcaraUjian', function ($query) use ($jadwalId) {
            $query->where('jadwal_ujian_id', $jadwalId);
        })
        ->orderBy('nama_sesi')
        ->get();

        return view('features.naskah.enrollment_ujian.edit', compact('enrollment', 'sesiRuangans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EnrollmentUjian $enrollmentUjian)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'status_enrollment' => 'required|in:enrolled,completed,absent,cancelled',
            'status_kehadiran' => 'required|in:belum_hadir,hadir,tidak_hadir',
            'catatan' => 'nullable|string|max:255',
        ]);

        try {
            // Ensure selected session belongs to the same jadwal_ujian
            $currentJadwalId = $enrollmentUjian->jadwal_ujian_id;
            $newSesi = SesiRuangan::findOrFail($request->sesi_ruangan_id);
            
            // Memeriksa apakah sesi ruangan terkait dengan jadwal ujian yang sama
            $relatedToSameJadwal = $newSesi->beritaAcaraUjian && 
                                   $newSesi->beritaAcaraUjian->jadwal_ujian_id == $currentJadwalId;
            
            if (!$relatedToSameJadwal) {
                return redirect()
                    ->route('naskah.enrollment-ujian.edit', $enrollmentUjian->id)
                    ->with('error', 'Sesi ruangan yang dipilih harus dari jadwal ujian yang sama')
                    ->withInput();
            }

            // Update enrollment
            $enrollmentUjian->update([
                'sesi_ruangan_id' => $request->sesi_ruangan_id,
                'status_enrollment' => $request->status_enrollment,
                'status_kehadiran' => $request->status_kehadiran,
                'catatan' => $request->catatan,
            ]);

            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('success', 'Data enrollment berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()
                ->route('naskah.enrollment-ujian.edit', $enrollmentUjian->id)
                ->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EnrollmentUjian $enrollmentUjian)
    {
        try {
            $enrollmentUjian->delete();

            return redirect()
                ->route('naskah.enrollment-ujian.index')
                ->with('success', 'Enrollment ujian berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('naskah.enrollment-ujian.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Get session options for a given jadwal ujian
     */
    public function getSesiOptions(Request $request)
    {
        $jadwalId = $request->input('jadwal_id');

        if (!$jadwalId) {
            return response()->json([]);
        }

        $sesiList = SesiRuangan::whereHas('beritaAcaraUjian', function ($query) use ($jadwalId) {
            $query->where('jadwal_ujian_id', $jadwalId);
        })
        ->orderBy('waktu_mulai')
        ->get()
        ->map(function ($sesi) {
            return [
                'id' => $sesi->id,
                'text' => $sesi->nama_sesi . ' (' . $sesi->waktu_mulai->format('d M Y H:i') . ')'
            ];
        });

        return response()->json($sesiList);
    }

    /**
     * Get siswa options for select2 dropdown
     */
    public function getSiswaOptions(Request $request)
    {
        $search = $request->input('search');
        $kelasId = $request->input('kelas_id');

        $query = Siswa::query();

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $siswas = $query->orderBy('nama')->limit(10)->get();

        $formattedSiswa = $siswas->map(function ($siswa) {
            return [
                'id' => $siswa->id,
                'text' => $siswa->nama . ' (' . $siswa->nis . ')'
            ];
        });

        return response()->json($formattedSiswa);
    }

    /**
     * Handle bulk enrollment of students by class
     */
    public function bulkEnrollment(Request $request)
    {
        $request->validate([
            'jadwal_id' => 'required|exists:jadwal_ujians,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id'
        ]);

        try {
            DB::beginTransaction();

            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);
            $jadwalId = $request->jadwal_id;
            $kelasIds = $request->kelas_ids;

            // Get students from selected classes who aren't already enrolled
            $enrolledStudentIds = EnrollmentUjian::where('jadwal_ujian_id', $jadwalId)
                ->pluck('siswa_id');

            $eligibleStudents = Siswa::whereIn('kelas_id', $kelasIds)
                ->whereNotIn('id', $enrolledStudentIds)
                ->get();

            $count = 0;
            foreach ($eligibleStudents as $student) {
                EnrollmentUjian::create([
                    'siswa_id' => $student->id,
                    'sesi_ruangan_id' => $request->sesi_ruangan_id,
                    'jadwal_ujian_id' => $jadwalId,
                    'status_enrollment' => 'enrolled',
                    'status_kehadiran' => 'belum_hadir',
                    'token_login' => $this->generateUniqueToken(),
                    'token_dibuat_pada' => now(),
                ]);
                $count++;
            }

            DB::commit();

            return redirect()
                ->route('naskah.enrollment-ujian.index', ['jadwal_id' => $jadwalId, 'sesi_id' => $sesiRuangan->id])
                ->with('success', "Berhasil mendaftarkan {$count} siswa ke ujian");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('naskah.enrollment-ujian.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Generate a new token for an enrollment
     */
    public function generateToken(EnrollmentUjian $enrollmentUjian)
    {
        try {
            $enrollmentUjian->update([
                'token_login' => $this->generateUniqueToken(),
                'token_dibuat_pada' => now(),
                'token_digunakan_pada' => null
            ]);

            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('success', 'Token login baru berhasil dibuat');
        } catch (\Exception $e) {
            return redirect()
                ->route('naskah.enrollment-ujian.show', $enrollmentUjian->id)
                ->with('error', 'Error: ' . $e->getMessage());
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
                'status_kehadiran' => $status == 'completed' ? 'hadir' : ($status == 'absent' ? 'tidak_hadir' : $enrollmentUjian->status_kehadiran)
            ]);

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
     * Generate a unique 6-character token for login
     */
    protected function generateUniqueToken()
    {
        do {
            $token = strtoupper(Str::random(6));
        } while (EnrollmentUjian::where('token_login', $token)->exists());

        return $token;
    }
}
