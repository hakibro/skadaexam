<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\SesiRuangan;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
        $this->middleware(['auth', 'role:admin,guru']);
    }

    /**
     * Show enrollment management page
     */
    public function index()
    {
        $jadwalUjianList = JadwalUjian::where('status', 'open')
            ->orderBy('tanggal_mulai')
            ->get();

        return view('enrollment.index', compact('jadwalUjianList'));
    }

    /**
     * Show the enrollment form for a specific jadwal ujian
     */
    public function create($jadwalId)
    {
        $jadwalUjian = JadwalUjian::findOrFail($jadwalId);

        // Only allow enrollment if jadwal is open
        if (!$jadwalUjian->isOpen()) {
            return redirect()->route('enrollment.index')
                ->with('error', 'Jadwal ujian tidak dalam status open');
        }

        // Mendapatkan semua sesi ruangan yang terkait dengan jadwal ujian ini melalui berita acara
        $sesiList = SesiRuangan::whereHas('beritaAcaraUjian', function ($query) use ($jadwalId) {
            $query->where('jadwal_ujian_id', $jadwalId);
        })
        ->orderBy('tanggal')
        ->orderBy('waktu_mulai')
        ->get();

        $kelasList = Kelas::orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        return view('enrollment.create', compact('jadwalUjian', 'sesiList', 'kelasList'));
    }

    /**
     * Process enrollment request
     */
    public function store(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
            'sesi_ruangan_id' => 'nullable|exists:sesi_ruangan,id'
        ]);

        $jadwalUjian = JadwalUjian::findOrFail($request->jadwal_ujian_id);
        $sesiRuangan = null;

        if ($request->sesi_ruangan_id) {
            $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);
        }

        $result = $this->enrollmentService->enrollStudentsByKelas(
            $jadwalUjian,
            $request->kelas_ids,
            $sesiRuangan
        );

        if (!empty($result['errors'])) {
            return redirect()->back()
                ->with('warning', 'Sebagian siswa berhasil didaftarkan. ' . implode(', ', $result['errors']))
                ->with('enrollmentResult', $result);
        }

        return redirect()->route('enrollment.index')
            ->with('success', "Berhasil mendaftarkan {$result['success']} siswa ke ujian {$jadwalUjian->nama}")
            ->with('enrollmentResult', $result);
    }

    /**
     * View students enrolled in a specific exam
     */
    public function show($jadwalId)
    {
        $jadwalUjian = JadwalUjian::with(['enrollmentUjian.siswa', 'enrollmentUjian.sesiRuangan'])
            ->findOrFail($jadwalId);

        return view('enrollment.show', compact('jadwalUjian'));
    }

    /**
     * Generate tokens for students in a specific session
     */
    public function generateTokens(Request $request)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id'
        ]);

        $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);

        // Check if current user is authorized to generate tokens
        if (
            !Auth::user()->hasRole(['admin', 'pengawas']) &&
            $sesiRuangan->pengawas_id != Auth::user()->id
        ) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin untuk generate token pada sesi ini');
        }

        // Generate tokens untuk semua enrollment yang terkait dengan sesi ini
        $enrollments = $sesiRuangan->sesiRuanganSiswa()
            ->with('enrollment')
            ->get()
            ->pluck('enrollment')
            ->filter(); // Menghilangkan nilai null
            
        $generatedCount = 0;
        foreach ($enrollments as $enrollment) {
            if ($enrollment) {
                $enrollment->generateToken();
                $generatedCount++;
            }
        }

        return redirect()->back()
            ->with('success', "Berhasil generate token untuk {$generatedCount} siswa");
    }
}
