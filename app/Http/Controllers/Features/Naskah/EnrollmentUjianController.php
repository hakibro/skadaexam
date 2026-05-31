<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\EnrollmentService;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Imports\EnrollmentImport;
use Maatwebsite\Excel\Facades\Excel;

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
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjianId = $request->get('paket_ujian_id');

        $query = EnrollmentUjian::with(['siswa.tahunAjaranRecords.kelas', 'sesiRuangan.jadwalUjian', 'sesiRuanganSiswa', 'jadwalUjian']);

        if ($tahunAjaranId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        if ($paketUjianId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('paket_ujian_id', $paketUjianId));
        }

        // Apply filters

        if ($request->filled('jadwal_id')) {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        if ($request->filled('sesi_ruangan_id')) {
            $query->where('sesi_ruangan_id', $request->sesi_ruangan_id);
        }

        if ($request->filled('status')) {
            $query->where('status_enrollment', $request->status);
        }

        if ($request->filled('kehadiran')) {
            $query->whereHas('sesiRuanganSiswa', function ($q) use ($request) {
                $q->where('status_kehadiran', $request->kehadiran);
            });
        }
        // Filter berdasarkan nama siswa atau ID yayasan
        if ($request->filled('siswa_search')) {
            $search = $request->siswa_search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%"); // pastikan kolom id_yayasan ada di tabel siswa
            });
        }

        // Filter berdasarkan kelas
        if ($request->filled('kelas_id')) {
            $kelasId = $request->kelas_id;
            $query->whereHas('siswa.tahunAjaranRecords', function ($q) use ($kelasId, $tahunAjaranId) {
                $q->where('kelas_id', $kelasId)
                    ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId));
            });
        }

        $perPage = $request->get('per_page', 50);
        $enrollments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Data untuk filter dropdown
        $jadwalUjians = JadwalUjian::forTahunAjaran($tahunAjaranId)
            ->forPaketUjian($paketUjianId)
            ->where('status', 'aktif')
            ->orderBy('tanggal', 'asc')
            ->get();

        $sesiRuangans = SesiRuangan::whereIn('status', ['belum_mulai', 'berlangsung'])
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('waktu_mulai', 'desc')
            ->get();
        $kelasList = Kelas::forTahunAjaran($tahunAjaranId)->orderBy('nama_kelas')->get();
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $paketUjians = \App\Models\PaketUjian::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))->orderBy('nama')->get();

        return view('features.naskah.enrollment_ujian.index', compact('enrollments', 'jadwalUjians', 'sesiRuangans', 'kelasList', 'tahunAjarans', 'tahunAjaranId', 'paketUjians', 'paketUjianId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $activeYear = app(TahunAjaranService::class)->active();
        if (!$activeYear) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu sebelum membuat enrollment.');
        }

        $jadwalUjians = JadwalUjian::forTahunAjaran($activeYear->id)->where('status', 'aktif')->orderBy('tanggal', 'desc')->get();
        $sesiRuangans = collect(); // Will be populated via AJAX
        $kelasList = Kelas::forTahunAjaran($activeYear->id)->orderBy('nama_kelas')->get();

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
            $jadwalUjian = $sesiRuangan->jadwalUjians()->first();

            if (!$jadwalUjian) {
                throw new \Exception('Sesi ruangan belum terhubung ke jadwal ujian.');
            }

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

                $siswa = Siswa::with('tahunAjaranRecords.kelas')->findOrFail($siswaId);
                $kelasTahun = $siswa->kelasForTahunAjaran($jadwalUjian->tahun_ajaran_id);
                $kelasTarget = collect($jadwalUjian->kelas_target ?? [])->map(fn($id) => (string) $id)->all();

                if (!$kelasTahun || (!empty($kelasTarget) && !in_array((string) $kelasTahun->id, $kelasTarget, true))) {
                    $skipped++;
                    continue;
                }

                // Create new enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswaId,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $jadwalUjian->id,
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
            'status_enrollment' => 'required|in:enrolled,active,completed,cancelled',
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
        $jadwalId = $request->query('jadwal_id');

        if (!$jadwalId) {
            return response()->json([]);
        }

        $jadwal = JadwalUjian::findOrFail($jadwalId);

        // Ambil sesi melalui relasi pivot jadwal_ujian_sesi_ruangan
        $sesiRuangans = SesiRuangan::whereHas('jadwalUjians', function ($q) use ($jadwalId) {
            $q->where('jadwal_ujian_id', $jadwalId);
        })
            ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
            ->get();

        $options = $sesiRuangans->map(function ($sesi) {
            return [
                'id' => $sesi->id,
                'text' => $sesi->ruangan->nama_ruangan . ' - ' . $sesi->nama_sesi,
            ];
        });

        return response()->json($options);
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
            $sesiRuangan = SesiRuangan::with('jadwalUjians')->findOrFail($request->sesi_id);
            $jadwal = $sesiRuangan->jadwalUjians->first();
            $tahunAjaranId = $sesiRuangan->tahun_ajaran_id ?: $jadwal?->tahun_ajaran_id;

            // Get already enrolled students for this session
            $enrolledSiswaIds = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuangan->id)
                ->pluck('siswa_id')
                ->toArray();

            // Get all students from the selected classes who are not already enrolled
            $siswaList = Siswa::whereHas('tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereIn('kelas_id', $request->kelas_ids))
                ->whereNotIn('id', $enrolledSiswaIds)
                ->with('kelas', 'tahunAjaranRecords.kelas')
                ->get(['id', 'nis', 'idyayasan', 'nama', 'kelas_id'])
                ->map(function ($siswa) use ($tahunAjaranId) {
                    $kelas = $this->kelasForTahun($siswa, $tahunAjaranId);

                    return [
                        'id' => $siswa->id,
                        'nis' => $siswa->nis,
                        'nama' => $siswa->nama,
                        'kelas' => $kelas?->nama_kelas ?? 'Tidak ada kelas'
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
        $tahunAjaranId = $request->get('tahun_ajaran_id', app(TahunAjaranService::class)->activeId());

        $query = Siswa::with('kelas', 'tahunAjaranRecords.kelas')
            ->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('idyayasan', 'like', "%{$search}%");
            });

        if ($kelasId) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->where('kelas_id', $kelasId));
        } elseif ($tahunAjaranId) {
            $query->whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        $siswaList = $query->limit(50)->get()
            ->map(function ($siswa) use ($tahunAjaranId) {
                $kelas = $this->kelasForTahun($siswa, $tahunAjaranId);

                return [
                    'id' => $siswa->id,
                    'text' => $siswa->nama . ' (' . ($siswa->nis ?? $siswa->idyayasan) . ') - ' . ($kelas?->nama_kelas ?? 'No Class')
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
            // 'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'jadwal_id' => 'required|exists:jadwal_ujian,id',
            'kelas_ids' => 'required|array',
            'kelas_ids.*' => 'exists:kelas,id',
        ]);

        try {
            DB::beginTransaction();
            $jadwal = JadwalUjian::findOrFail($request->jadwal_id);

            // Get all students from selected kelas
            $siswaList = Siswa::whereHas('tahunAjaranRecords', fn($q) => $q
                ->when($jadwal->tahun_ajaran_id, fn($pivot) => $pivot->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id))
                ->whereIn('kelas_id', $request->kelas_ids))
                ->with('tahunAjaranRecords.kelas')
                ->get();

            // Get already enrolled students
            $enrolledSiswaIds = EnrollmentUjian::where('jadwal_ujian_id', $request->jadwal_id)
                ->pluck('siswa_id')
                ->toArray();

            $enrollmentCount = 0;
            $skippedCount = 0;

            foreach ($siswaList as $siswa) {
                // 1. Skip jika sudah terdaftar
                if (in_array($siswa->id, $enrolledSiswaIds)) {
                    $skippedCount++;
                    continue;
                }

                // Ambil sesi ruangan yang terhubung ke jadwal_id tertentu DAN dimiliki oleh siswa ini
                $sesiRuangan = $siswa->sesiRuangan()
                    ->where('sesi_ruangan.tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                    ->whereHas('jadwalUjians', function ($q) use ($request) {
                        $q->where('jadwal_ujian.id', $request->jadwal_id);
                    })
                    ->first();

                if (!$sesiRuangan) {
                    // Tambahkan penanganan jika sesi tidak ditemukan agar tidak error saat create
                    Log::warning("Siswa {$siswa->nama} belum diplot ke ruangan untuk jadwal ini.");
                    continue;
                }

                // 4. Create Enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $siswa->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'jadwal_ujian_id' => $request->jadwal_id,
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
     * Enroll selected students to a specific jadwal, automatically assigning to appropriate sesi.
     * Skip if already enrolled.
     */
    public function enrollSelectedSiswa(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        try {
            DB::beginTransaction();

            $siswaIds = $request->siswa_ids;
            $activeYearId = app(TahunAjaranService::class)->activeId();
            $enrolledCount = 0;
            $skippedCount = 0;
            $noSesiCount = 0;

            foreach ($siswaIds as $siswaId) {
                $siswa = Siswa::with('kelas', 'tahunAjaranRecords.kelas')->find($siswaId);
                $kelas = $siswa ? $this->kelasForTahun($siswa, $activeYearId) : null;
                if (!$siswa || !$kelas) {
                    continue;
                }

                // Cari semua jadwal ujian yang sesuai dengan kelas siswa (berdasarkan kelas_target)
                $matchingJadwals = JadwalUjian::forTahunAjaran($activeYearId)
                    ->where('status', 'aktif')
                    ->where(function ($q) use ($kelas) {
                        $q->whereJsonContains('kelas_target', $kelas->id)
                            ->orWhereJsonContains('kelas_target', (string) $kelas->id);
                    })
                    ->get();

                if ($matchingJadwals->isEmpty()) {
                    continue; // tidak ada jadwal untuk kelas ini
                }

                // Ambil semua sesi ruangan tempat siswa sudah terdaftar
                $assignedSesiIds = $siswa->sesiRuanganSiswa()->pluck('sesi_ruangan_id')->toArray();

                foreach ($matchingJadwals as $jadwal) {
                    // Cari sesi ruangan yang dimiliki siswa DAN terhubung dengan jadwal ini
                    $sesiForJadwal = SesiRuangan::whereIn('id', $assignedSesiIds)
                        ->where('tahun_ajaran_id', $jadwal->tahun_ajaran_id)
                        ->whereHas('jadwalUjians', fn($q) => $q->where('jadwal_ujian_id', $jadwal->id))
                        ->first();

                    if (!$sesiForJadwal) {
                        $noSesiCount++;
                        continue;
                    }

                    // Cek apakah sudah terdaftar
                    $exists = EnrollmentUjian::where('siswa_id', $siswaId)
                        ->where('jadwal_ujian_id', $jadwal->id)
                        ->exists();

                    if ($exists) {
                        $skippedCount++;
                        continue;
                    }

                    // Buat enrollment baru
                    EnrollmentUjian::create([
                        'siswa_id' => $siswaId,
                        'jadwal_ujian_id' => $jadwal->id,
                        'sesi_ruangan_id' => $sesiForJadwal->id,
                        'status_enrollment' => 'enrolled',
                    ]);

                    $enrolledCount++;
                }
            }

            DB::commit();

            $message = "✅ {$enrolledCount} enrollment berhasil dibuat.";
            if ($skippedCount > 0)
                $message .= " ⏭️ {$skippedCount} dilewati (sudah ada).";
            if ($noSesiCount > 0)
                $message .= " 🏫 {$noSesiCount} siswa tidak memiliki sesi ruangan untuk beberapa jadwal.";

            if ($enrolledCount > 0) {
                return redirect()->back()->with('success', $message);
            } else {
                return redirect()->back()->with('warning', '⚠️ Tidak ada enrollment baru. ' . $message);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal enroll siswa terpilih', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
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
            $sesiRuangan = SesiRuangan::with(['jadwalUjians', 'sesiRuanganSiswa.siswa.tahunAjaranRecords.kelas'])
                ->findOrFail($sesiId);

            $jadwalUjian = $sesiRuangan->jadwalUjians->first();

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
                $kelas = $assignment->siswa ? $this->kelasForTahun($assignment->siswa, $jadwalUjian->tahun_ajaran_id) : null;
                $kelasTargets = collect($jadwalUjian->kelas_target ?? [])->map(fn($id) => (string) $id)->all();

                if (!$kelas || (!empty($kelasTargets) && !in_array((string) $kelas->id, $kelasTargets, true))) {
                    $skippedCount++;
                    continue;
                }

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

    public function bulkAction(Request $request)
    {
        $action = $request->action;
        $ids = $request->ids;

        if (!$action || empty($ids)) {
            return response()->json(['success' => false, 'message' => 'Data atau aksi tidak valid']);
        }

        $enrollments = EnrollmentUjian::whereIn('id', $ids)->get();

        foreach ($enrollments as $enrollment) {
            switch ($action) {
                case 'enrolled':
                    $enrollment->status_enrollment = 'enrolled';
                    $enrollment->save();

                    break;
                case 'cancelled':
                    $enrollment->status_enrollment = 'cancelled';
                    $enrollment->save();

                    break;
                case 'deleted':
                    $enrollment->delete();
                    break;
            }
        }

        return response()->json(['success' => true]);
    }


    /**
     * Print QR code for enrollment
     */
    public function printQR(EnrollmentUjian $enrollmentUjian)
    {
        return view('features.naskah.enrollment_ujian.print-qr', compact('enrollmentUjian'));
    }

    public function manageCancelledEnrollments($sesiRuanganId)
    {
        $siswaCancelled = EnrollmentUjian::with('siswa')
            ->where('sesi_ruangan_id', $sesiRuanganId)
            ->where('status_enrollment', 'cancelled')
            ->get();

        $siswaCompleted = EnrollmentUjian::with('siswa')
            ->where('sesi_ruangan_id', $sesiRuanganId)
            ->where('status_enrollment', 'completed')
            ->get();

        // Jika ingin menampilkan siswa yang dihapus (soft delete), pastikan model menggunakan SoftDeletes
        $siswaDeleted = EnrollmentUjian::withTrashed()
            ->with('siswa')
            ->where('sesi_ruangan_id', $sesiRuanganId)
            ->onlyTrashed()
            ->get();

        return view('features.pengawas.manage-enrollment', compact('siswaCancelled', 'siswaDeleted', 'siswaCompleted'));
    }



    public function restoreEnrollment($enrollmentId)
    {
        $enrollment = EnrollmentUjian::withTrashed()->findOrFail($enrollmentId);

        $enrollment->restore(); // jika soft delete
        $enrollment->status_enrollment = 'active';
        $enrollment->save();

        return redirect()->back()->with('success', 'Siswa berhasil diaktifkan kembali.');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new EnrollmentImport();
            Excel::import($import, $request->file('file'));

            $success = $import->getSuccessCount();
            $failed = $import->getFailedCount();
            $errors = $import->getErrors();

            $message = "Import selesai. Berhasil: {$success}, Gagal: {$failed}.";
            if ($failed > 0) {
                // Simpan errors ke session flash agar bisa ditampilkan
                session()->flash('import_errors', $errors);
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membaca file: ' . $e->getMessage());
        }
    }

    private function kelasForTahun(Siswa $siswa, ?int $tahunAjaranId)
    {
        if ($tahunAjaranId) {
            $record = $siswa->relationLoaded('tahunAjaranRecords')
                ? $siswa->tahunAjaranRecords->firstWhere('tahun_ajaran_id', $tahunAjaranId)
                : $siswa->tahunAjaranRecords()->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas')->first();

            if ($record?->kelas) {
                return $record->kelas;
            }
        }

        return $siswa->kelas;
    }

}
