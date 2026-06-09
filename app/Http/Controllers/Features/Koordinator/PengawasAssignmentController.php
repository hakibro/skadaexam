<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalUjian;
use App\Models\JadwalUjianSesiRuangan;
use App\Models\SesiRuangan;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PengawasAssignmentController extends Controller
{
    /**
     * Display assignment management page
     */
    public function index(Request $request)
    {
        // Filter by tahun ajaran, default to active
        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? $request->tahun_ajaran_id
            : app(TahunAjaranService::class)->activeId();

        // Get list of tahun ajaran for dropdown
        $tahunAjaranList = \App\Models\TahunAjaran::orderByDesc('is_active')
            ->orderByRaw("CASE status WHEN 'aktif' THEN 1 WHEN 'draft' THEN 2 ELSE 3 END")
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        // Get paket ujian list filtered by selected tahun ajaran
        $paketUjianList = \App\Models\PaketUjian::forTahunAjaran($tahunAjaranId)
            ->where('status', '!=', 'arsip')
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 1 ELSE 2 END")
            ->orderByDesc('tanggal_mulai')
            ->get();

        // Pre-select first active paket ujian if not specified
        $selectedPaketUjianId = $request->filled('paket_ujian_id')
            ? $request->paket_ujian_id
            : $paketUjianList->firstWhere('status', 'aktif')?->id;

        // Filter by date, default to today
        $tanggal = $request->filled('tanggal') ? $request->tanggal : now()->format('Y-m-d');

        // Get all session pivot data for the selected date
        // We use the pivot model directly to get all jadwals for that date
        $pivotQuery = JadwalUjianSesiRuangan::whereHas('jadwalUjian', function ($q) use ($tanggal, $tahunAjaranId, $selectedPaketUjianId) {
            $q->forTahunAjaran($tahunAjaranId)
                ->when($selectedPaketUjianId, fn($query) => $query->where('paket_ujian_id', $selectedPaketUjianId))
                ->whereDate('tanggal', $tanggal);
        })
            ->with(['sesiRuangan.ruangan', 'jadwalUjian.mapel', 'pengawas']);

        // Clone for stats
        $allPivots = (clone $pivotQuery)->get();

        // Map data for the view
        $sesiRuangans = $allPivots->map(function ($pivot) {
            $sesi = $pivot->sesiRuangan;
            $jadwal = $pivot->jadwalUjian;

            return (object) [
                'id' => $sesi->id, // Sesi ID
                'pivot_id' => $pivot->id, // Pivot ID for unique reference
                'jadwal_ujian_id' => $jadwal->id, // Jadwal ID
                'nama_sesi' => $sesi->nama_sesi,
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'status' => $sesi->status,
                'ruangan' => $sesi->ruangan,
                'mapel' => $jadwal->mapel->nama_mapel ?? $jadwal->judul,
                'pengawas_for_jadwal' => $pivot->pengawas, // The assigned guru
            ];
        });

        // Get available pengawas
        $availablePengawas = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->orderBy('nama')->get();

        // Calculate Stats based on unique sessions assigned
        $uniqueSessions = $sesiRuangans->unique('id');
        $totalSesi = $uniqueSessions->count();

        // A session is "assigned" if at least one jadwal has a pengawas (assuming logic syncs them)
        // or strictly check if pengawas exists
        $assignedSesi = $uniqueSessions->filter(function ($s) {
            return !is_null($s->pengawas_for_jadwal);
        })->count();

        $stats = [
            'total_sesi' => $totalSesi,
            'assigned' => $assignedSesi,
            'unassigned' => $totalSesi - $assignedSesi,
            'total_pengawas' => $availablePengawas->count(),
        ];

        // Pass jadwalUjians for filter dropdown if needed, or just date
        $jadwalUjians = JadwalUjian::forTahunAjaran($tahunAjaranId)
            ->when($selectedPaketUjianId, fn($query) => $query->where('paket_ujian_id', $selectedPaketUjianId))
            ->whereDate('tanggal', $tanggal)
            ->get();

        return view('features.koordinator.pengawas_assignment.index', compact(
            'jadwalUjians',
            'sesiRuangans',
            'availablePengawas',
            'tanggal',
            'stats',
            'tahunAjaranList',
            'paketUjianList',
            'tahunAjaranId',
            'selectedPaketUjianId'
        ));
    }

    /**
     * Assign pengawas to a specific session for a specific jadwal ujian
     */
    public function assign(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'pengawas_id' => 'required|exists:guru,id'
        ]);

        $jadwal = JadwalUjian::findOrFail($request->jadwal_ujian_id);
        $sesi = SesiRuangan::findOrFail($request->sesi_ruangan_id);

        $result = $this->assignPengawasToSession($jadwal, $sesi, $request->pengawas_id);

        return response()->json($result);
    }

    /**
     * Remove pengawas assignment
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id'
        ]);

        $jadwal = JadwalUjian::findOrFail($request->jadwal_ujian_id);
        $sesi = SesiRuangan::findOrFail($request->sesi_ruangan_id);

        $result = $this->removePengawasFromSession($jadwal, $sesi);

        return response()->json($result);
    }

    /**
     * Get schedule for specific pengawas and date
     */
    public function getSchedule($pengawasId, $tanggal)
    {
        $tahunAjaranId = app(TahunAjaranService::class)->activeId();
        $pengawas = Guru::findOrFail($pengawasId);

        // Get all session assignments for this pengawas on the specified date
        $assignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawasId)
            ->whereHas('jadwalUjian', function ($q) use ($tanggal, $tahunAjaranId) {
                $q->forTahunAjaran($tahunAjaranId)
                    ->whereDate('tanggal', $tanggal);
            })
            ->with(['jadwalUjian', 'sesiRuangan.ruangan'])
            ->get();

        $sessions = $assignments->map(function ($assignment) {
            $sesi = $assignment->sesiRuangan;
            $jadwal = $assignment->jadwalUjian;

            return [
                'id' => $assignment->id,
                'sesi_id' => $sesi->id,
                'jadwal_id' => $jadwal->id,
                'nama_sesi' => $sesi->nama_sesi,
                'ruangan' => $sesi->ruangan->nama_ruangan ?? 'Unknown',
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'mapel' => $jadwal->mapel->nama ?? 'Unknown',
                'status' => $sesi->status
            ];
        });

        return view('features.koordinator.pengawas_assignment.schedule', compact('pengawas', 'sessions', 'tanggal'));
    }

    /**
     * Get pengawas availability for a specific date and session time
     */
    public function getPengawasAvailability(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id'
        ]);

        $jadwalUjian = JadwalUjian::findOrFail($request->jadwal_ujian_id);
        $sesiRuangan = SesiRuangan::findOrFail($request->sesi_ruangan_id);
        if ($jadwalUjian->tahun_ajaran_id !== $sesiRuangan->tahun_ajaran_id) {
            return response()->json([
                'available' => [],
                'unavailable' => [],
                'message' => 'Jadwal dan sesi ruangan harus berada pada tahun ajaran yang sama.'
            ], 422);
        }

        $date = $jadwalUjian->tanggal->format('Y-m-d');

        // Get all pengawas
        $allPengawas = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->get();

        $availablePengawas = [];
        $unavailablePengawas = [];

        foreach ($allPengawas as $pengawas) {
            // We need to check if pengawas is assigned to another room at the same time
            $currentRuangan = $sesiRuangan->ruangan;
            $currentRuanganId = $currentRuangan ? $currentRuangan->id : null;

            // Get all assignments for this pengawas that overlap with the current session
            $existingAssignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawas->id)
                ->whereHas('jadwalUjian', function ($q) use ($date, $jadwalUjian) {
                    $q->forTahunAjaran($jadwalUjian->tahun_ajaran_id)
                        ->whereDate('tanggal', $date);
                })
                ->whereHas('sesiRuangan', function ($q) use ($sesiRuangan) {
                    $q->where('tahun_ajaran_id', $sesiRuangan->tahun_ajaran_id)
                        ->where(function ($query) use ($sesiRuangan) {
                            $query->where(function ($q) use ($sesiRuangan) {
                                $q->where('waktu_mulai', '<=', $sesiRuangan->waktu_mulai)
                                    ->where('waktu_selesai', '>', $sesiRuangan->waktu_mulai);
                            })->orWhere(function ($q) use ($sesiRuangan) {
                                $q->where('waktu_mulai', '<', $sesiRuangan->waktu_selesai)
                                    ->where('waktu_selesai', '>=', $sesiRuangan->waktu_selesai);
                            })->orWhere(function ($q) use ($sesiRuangan) {
                                $q->where('waktu_mulai', '>=', $sesiRuangan->waktu_mulai)
                                    ->where('waktu_selesai', '<=', $sesiRuangan->waktu_selesai);
                            });
                        });
                })
                ->with(['jadwalUjian', 'sesiRuangan.ruangan'])
                ->get();

            // Check if any assignments are in different rooms
            $conflictInDifferentRoom = false;
            $conflictAssignment = null;

            foreach ($existingAssignments as $assignment) {
                $assignmentRuangan = $assignment->sesiRuangan->ruangan;
                $assignmentRuanganId = $assignmentRuangan ? $assignmentRuangan->id : null;

                // If assignment is in a different room, it's a conflict
                if ($assignmentRuanganId && $currentRuanganId && $assignmentRuanganId != $currentRuanganId) {
                    $conflictInDifferentRoom = true;
                    $conflictAssignment = $assignment;
                    break;
                }
            }

            // If no conflict in different room, pengawas is available
            if (!$conflictInDifferentRoom) {
                $availablePengawas[] = [
                    'id' => $pengawas->id,
                    'nama' => $pengawas->nama,
                    'nip' => $pengawas->nip,
                    'email' => $pengawas->email
                ];

                // If there are assignments in the same room, add a note
                if (!$existingAssignments->isEmpty()) {
                    $firstAssignment = $existingAssignments->first();
                    $conflictSesi = $firstAssignment->sesiRuangan;
                    $conflictJadwal = $firstAssignment->jadwalUjian;

                    $unavailablePengawas[] = [
                        'id' => $pengawas->id,
                        'nama' => $pengawas->nama,
                        'conflict_session' => $conflictSesi->nama_sesi,
                        'conflict_ruangan' => $conflictSesi->ruangan->nama_ruangan ?? 'Unknown',
                        'conflict_jadwal' => $conflictJadwal->judul,
                        'conflict_time' => $conflictSesi->waktu_mulai . ' - ' . $conflictSesi->waktu_selesai,
                        'note' => 'Sudah ditugaskan di ruangan yang sama, dapat ditugaskan untuk mapel lain'
                    ];
                }
            } else {
                // Pengawas has a conflict in a different room
                $conflictSesi = $conflictAssignment->sesiRuangan;
                $conflictJadwal = $conflictAssignment->jadwalUjian;

                $unavailablePengawas[] = [
                    'id' => $pengawas->id,
                    'nama' => $pengawas->nama,
                    'conflict_session' => $conflictSesi->nama_sesi,
                    'conflict_ruangan' => $conflictSesi->ruangan->nama_ruangan ?? 'Unknown',
                    'conflict_jadwal' => $conflictJadwal->judul,
                    'conflict_time' => $conflictSesi->waktu_mulai . ' - ' . $conflictSesi->waktu_selesai,
                    'note' => 'Tidak dapat ditugaskan karena sudah ditugaskan di ruangan lain pada waktu yang sama'
                ];
            }
        }

        return response()->json([
            'available' => $availablePengawas,
            'unavailable' => $unavailablePengawas
        ]);
    }

    /**
     * Bulk assign pengawas to multiple sessions
     * Updated to handle 'selections' array from frontend
     */
    public function bulkAssign(Request $request)
    {
        // Validasi disesuaikan dengan format JSON yang dikirim dari JS
        $request->validate([
            'selections' => 'required|array',
            'selections.*.sesi_id' => 'required|exists:sesi_ruangan,id',
            'selections.*.jadwal_id' => 'required|exists:jadwal_ujian,id',
            'pengawas_id' => 'required|exists:guru,id'
        ]);

        $pengawasId = $request->pengawas_id;
        $assignedCount = 0;
        $conflictMessages = [];

        try {
            DB::beginTransaction();

            // Cek peran pengawas sekali saja di awal
            $pengawas = Guru::findOrFail($pengawasId);
            if (!$pengawas->user || !$pengawas->user->hasRole('pengawas')) {
                throw new \Exception('Guru yang dipilih tidak memiliki peran pengawas');
            }

            // Loop melalui setiap seleksi yang dikirim
            foreach ($request->selections as $selection) {
                // Ambil jadwal dan sesi berdasarkan ID yang dikirim
                // Catatan: jadwal_id di sini hanya sebagai "anchor" untuk menentukan tanggal.
                // Logic di dalam assignPengawasToSession akan otomatis menyebar ke semua jadwal di tanggal yang sama.
                $jadwal = JadwalUjian::find($selection['jadwal_id']);
                $sesi = SesiRuangan::find($selection['sesi_id']);

                if ($jadwal && $sesi) {
                    // Panggil method internal yang sudah ada logic lengkapnya
                    $result = $this->assignPengawasToSession($jadwal, $sesi, $pengawasId);

                    if ($result['success']) {
                        $assignedCount++;
                    } else {
                        // Catat pesan konflik jika ada
                        $conflictMessages[] = $sesi->nama_sesi . ': ' . ($result['message'] ?? 'Gagal');
                    }
                }
            }

            DB::commit();

            $message = "{$assignedCount} sesi berhasil ditugaskan.";
            if (count($conflictMessages) > 0) {
                $message .= " Beberapa sesi gagal: " . implode('; ', $conflictMessages);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned' => $assignedCount,
                'conflicts' => $conflictMessages
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk assign pengawas', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign pengawas to a session (internal method)
     * Logic updated: Assigns pengawas to ALL jadwal on the same date for this session.
     */
    private function assignPengawasToSession(JadwalUjian $jadwal, SesiRuangan $sesi, $pengawasId)
    {
        try {
            if ($jadwal->tahun_ajaran_id !== $sesi->tahun_ajaran_id) {
                return [
                    'success' => false,
                    'message' => 'Jadwal dan sesi ruangan harus berada pada tahun ajaran yang sama'
                ];
            }

            if ($jadwal->tahunAjaran?->isReadOnly() || $sesi->tahunAjaran?->isReadOnly()) {
                return [
                    'success' => false,
                    'message' => 'Tahun ajaran arsip hanya dapat dilihat'
                ];
            }

            // Verify that the selected guru has pengawas role
            $pengawas = Guru::findOrFail($pengawasId);
            if (!$pengawas->user || !$pengawas->user->hasRole('pengawas')) {
                return [
                    'success' => false,
                    'message' => 'Selected guru does not have pengawas role'
                ];
            }

            $date = $jadwal->tanggal->format('Y-m-d');
            $currentRuangan = $sesi->ruangan;

            if (!$currentRuangan) {
                return [
                    'success' => false,
                    'message' => 'Sesi ruangan tidak valid'
                ];
            }

            $currentRuanganId = $currentRuangan->id;

            // Check if pengawas is already assigned to another room at the same date and session time
            $conflictAssignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawasId)
                ->whereHas('jadwalUjian', function ($q) use ($date, $jadwal) {
                    $q->forTahunAjaran($jadwal->tahun_ajaran_id)
                        ->whereDate('tanggal', $date);
                })
                ->whereHas('sesiRuangan', function ($q) use ($sesi) {
                    // Check for time overlap
                    $q->where('tahun_ajaran_id', $sesi->tahun_ajaran_id)
                        ->where(function ($query) use ($sesi) {
                        $query->where(function ($q) use ($sesi) {
                            $q->where('waktu_mulai', '<=', $sesi->waktu_mulai)
                                ->where('waktu_selesai', '>', $sesi->waktu_mulai);
                        })->orWhere(function ($q) use ($sesi) {
                            $q->where('waktu_mulai', '<', $sesi->waktu_selesai)
                                ->where('waktu_selesai', '>=', $sesi->waktu_selesai);
                        })->orWhere(function ($q) use ($sesi) {
                            $q->where('waktu_mulai', '>=', $sesi->waktu_mulai)
                                ->where('waktu_selesai', '<=', $sesi->waktu_selesai);
                        });
                    });
                })
                ->with(['sesiRuangan.ruangan'])
                ->get();

            // Check if any of the assignments are for a different room
            $conflict = false;
            foreach ($conflictAssignments as $assignment) {
                $assignmentRuanganId = $assignment->sesiRuangan->ruangan->id ?? null;
                // If assignment is in a different room, it's a conflict
                if ($assignmentRuanganId && $assignmentRuanganId != $currentRuanganId) {
                    $conflict = true;
                    break;
                }
            }

            if ($conflict) {
                return [
                    'success' => false,
                    'message' => 'Pengawas sudah ditugaskan di ruangan lain pada waktu yang sama'
                ];
            }

            // --- NEW LOGIC START ---
            // Find all Jadwal IDs that occur on the same date
            $jadwalIdsOnDate = JadwalUjian::forTahunAjaran($jadwal->tahun_ajaran_id)
                ->whereDate('tanggal', $date)
                ->pluck('id');

            // Update all pivot records for this session and those jadwal IDs
            // This assigns the pengawas to this session for ALL jadwals on this date
            JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesi->id)
                ->whereIn('jadwal_ujian_id', $jadwalIdsOnDate)
                ->update(['pengawas_id' => $pengawasId]);
            // --- NEW LOGIC END ---

            Log::info('Pengawas assigned to session for all jadwal on date', [
                'date' => $date,
                'session_id' => $sesi->id,
                'pengawas_id' => $pengawasId,
                'affected_jadwals' => $jadwalIdsOnDate,
                'assigned_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Pengawas berhasil ditugaskan untuk semua jadwal di tanggal yang sama',
                'pengawas_name' => $pengawas->nama
            ];
        } catch (\Exception $e) {
            Log::error('Error assigning pengawas', [
                'jadwal_id' => $jadwal->id,
                'session_id' => $sesi->id,
                'pengawas_id' => $pengawasId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Remove pengawas assignment (internal method)
     * Logic updated: Removes pengawas from ALL jadwal on the same date for this session.
     */
    private function removePengawasFromSession(JadwalUjian $jadwal, SesiRuangan $sesi)
    {
        try {
            if ($jadwal->tahun_ajaran_id !== $sesi->tahun_ajaran_id) {
                return [
                    'success' => false,
                    'message' => 'Jadwal dan sesi ruangan harus berada pada tahun ajaran yang sama'
                ];
            }

            if ($jadwal->tahunAjaran?->isReadOnly() || $sesi->tahunAjaran?->isReadOnly()) {
                return [
                    'success' => false,
                    'message' => 'Tahun ajaran arsip hanya dapat dilihat'
                ];
            }

            $date = $jadwal->tanggal->format('Y-m-d');

            // --- NEW LOGIC START ---
            // Find all Jadwal IDs that occur on the same date
            $jadwalIdsOnDate = JadwalUjian::forTahunAjaran($jadwal->tahun_ajaran_id)
                ->whereDate('tanggal', $date)
                ->pluck('id');

            // Update all pivot records for this session and those jadwal IDs
            $updatedCount = JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesi->id)
                ->whereIn('jadwal_ujian_id', $jadwalIdsOnDate)
                ->update(['pengawas_id' => null]);
            // --- NEW LOGIC END ---

            Log::info('Pengawas assignment removed for all jadwal on date', [
                'date' => $date,
                'session_id' => $sesi->id,
                'removed_by' => auth()->id(),
                'records_updated' => $updatedCount
            ]);

            return [
                'success' => true,
                'message' => 'Penugasan pengawas berhasil dibatalkan untuk semua jadwal di tanggal yang sama'
            ];
        } catch (\Exception $e) {
            Log::error('Error removing pengawas assignment', [
                'jadwal_id' => $jadwal->id,
                'session_id' => $sesi->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get all schedules for specific pengawas regardless of date
     */
    public function getAllSchedules($pengawasId)
    {
        $tahunAjaranId = app(TahunAjaranService::class)->activeId();
        $pengawas = Guru::findOrFail($pengawasId);

        // Get all session assignments for this pengawas on any date
        $assignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawasId)
            ->whereHas('jadwalUjian', fn($q) => $q->forTahunAjaran($tahunAjaranId))
            ->with(['jadwalUjian.mapel', 'sesiRuangan.ruangan'])
            ->get();

        // Group sessions by date
        $sessionsByDate = $assignments->groupBy(function ($assignment) {
            return $assignment->jadwalUjian->tanggal->format('Y-m-d');
        })->sortKeys(); // Sort by date in ascending order

        $groupedSessions = [];
        foreach ($sessionsByDate as $date => $dateAssignments) {
            $formattedDate = Carbon::parse($date)->format('d M Y');

            // Map and collect session data
            $sessions = $dateAssignments->map(function ($assignment) {
                $sesi = $assignment->sesiRuangan;
                $jadwal = $assignment->jadwalUjian;

                // Get proper subject name with more robust handling
                $mapel = null;
                if ($jadwal->mapel && isset($jadwal->mapel->nama)) {
                    $mapel = $jadwal->mapel->nama;
                } elseif (isset($jadwal->judul)) {
                    $mapel = $jadwal->judul;
                } else {
                    $mapel = 'Mata Pelajaran Tidak Diketahui';
                }

                return [
                    'id' => $assignment->id,
                    'sesi_id' => $sesi->id,
                    'jadwal_id' => $jadwal->id,
                    'nama_sesi' => $sesi->nama_sesi,
                    'ruangan' => $sesi->ruangan ? $sesi->ruangan->nama_ruangan : 'Ruangan Tidak Diketahui',
                    'waktu_mulai' => $sesi->waktu_mulai,
                    'waktu_selesai' => $sesi->waktu_selesai,
                    'mapel' => $mapel,
                    'status' => $sesi->status
                ];
            });

            // Sort sessions by start time within each date
            $sessions = $sessions->sortBy('waktu_mulai')->values()->all();

            $groupedSessions[$formattedDate] = $sessions;
        }

        return view('features.koordinator.pengawas_assignment.all_schedules', compact('pengawas', 'groupedSessions'));
    }
}
