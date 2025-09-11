<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalUjian;
use App\Models\JadwalUjianSesiRuangan;
use App\Models\SesiRuangan;
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
        // Filter by date, default to today
        $tanggal = $request->filled('tanggal') ? $request->tanggal : now()->format('Y-m-d');

        // Get exam schedules for the selected date
        $jadwalUjians = JadwalUjian::with(['mapel'])
            ->whereDate('tanggal', $tanggal)
            ->orderBy('tanggal')
            ->get();

        // If no specific jadwal is selected, use the first one from the date
        $selectedJadwalId = $request->filled('jadwal_id') ? $request->jadwal_id : ($jadwalUjians->isNotEmpty() ? $jadwalUjians->first()->id : null);

        // Get session rooms for the selected exam schedule
        $sesiRuangans = collect();
        $selectedJadwal = null;

        if ($selectedJadwalId) {
            $selectedJadwal = JadwalUjian::with(['mapel'])->find($selectedJadwalId);

            if ($selectedJadwal) {
                // Get all session rooms for this exam schedule with their supervisors
                $sesiRuangans = $selectedJadwal->sesiRuangans()
                    ->with(['ruangan', 'sesiRuanganSiswa'])
                    ->get()
                    ->map(function ($sesi) use ($selectedJadwalId) {
                        // Get the pivot to access the pengawas_id
                        $pivot = JadwalUjianSesiRuangan::where('jadwal_ujian_id', $selectedJadwalId)
                            ->where('sesi_ruangan_id', $sesi->id)
                            ->first();

                        // Get pengawas if assigned
                        $pengawas = null;
                        if ($pivot && $pivot->pengawas_id) {
                            $pengawas = Guru::find($pivot->pengawas_id);
                        }

                        // Add pengawas to the session
                        $sesi->pengawas_for_jadwal = $pengawas;
                        $sesi->pivot_id = $pivot ? $pivot->id : null;

                        return $sesi;
                    });
            }
        }

        // Get available pengawas
        $availablePengawas = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->orderBy('nama')->get();

        // Statistics
        $totalSesi = $sesiRuangans->count();
        $assignedSesi = $sesiRuangans->filter(function ($sesi) {
            return !is_null($sesi->pengawas_for_jadwal);
        })->count();

        $stats = [
            'total_sesi' => $totalSesi,
            'assigned' => $assignedSesi,
            'unassigned' => $totalSesi - $assignedSesi,
            'total_pengawas' => $availablePengawas->count(),
        ];

        return view('features.koordinator.pengawas_assignment.index', compact(
            'jadwalUjians',
            'selectedJadwal',
            'sesiRuangans',
            'availablePengawas',
            'tanggal',
            'stats'
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
        $pengawas = Guru::findOrFail($pengawasId);

        // Get all session assignments for this pengawas on the specified date
        $assignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawasId)
            ->whereHas('jadwalUjian', function ($q) use ($tanggal) {
                $q->whereDate('tanggal', $tanggal);
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
                ->where(function ($q) use ($jadwalUjian, $sesiRuangan) {
                    $q->where('jadwal_ujian_id', '!=', $jadwalUjian->id)
                        ->orWhere('sesi_ruangan_id', '!=', $sesiRuangan->id);
                })
                ->whereHas('jadwalUjian', function ($q) use ($date) {
                    $q->whereDate('tanggal', $date);
                })
                ->whereHas('sesiRuangan', function ($q) use ($sesiRuangan) {
                    $q->where(function ($query) use ($sesiRuangan) {
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
     * Bulk assign pengawas to multiple sessions in a jadwal
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'jadwal_ujian_id' => 'required|exists:jadwal_ujian,id',
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:sesi_ruangan,id',
            'pengawas_id' => 'required|exists:guru,id'
        ]);

        $jadwal = JadwalUjian::findOrFail($request->jadwal_ujian_id);

        try {
            DB::beginTransaction();

            // Verify that the selected guru has pengawas role
            $pengawas = Guru::findOrFail($request->pengawas_id);
            if (!$pengawas->user || !$pengawas->user->hasRole('pengawas')) {
                throw new \Exception('Selected guru does not have pengawas role');
            }

            $assigned = 0;
            $conflicts = [];

            foreach ($request->session_ids as $sessionId) {
                $sesi = SesiRuangan::with(['ruangan'])->findOrFail($sessionId);
                $date = $jadwal->tanggal->format('Y-m-d');
                $currentRuangan = $sesi->ruangan;

                if (!$currentRuangan) {
                    $conflicts[] = $sesi->nama_sesi;
                    continue;
                }

                $currentRuanganId = $currentRuangan->id;

                // Check if pengawas is already assigned to another room at the same time
                $conflictAssignments = JadwalUjianSesiRuangan::where('pengawas_id', $request->pengawas_id)
                    ->whereHas('jadwalUjian', function ($q) use ($date) {
                        $q->whereDate('tanggal', $date);
                    })
                    ->whereHas('sesiRuangan', function ($q) use ($sesi) {
                        $q->where(function ($query) use ($sesi) {
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
                    if ($assignmentRuanganId && $assignmentRuanganId != $currentRuanganId) {
                        $conflict = true;
                        break;
                    }
                }

                if (!$conflict) {
                    // Assign the pengawas since there's no conflict with a different room
                    JadwalUjianSesiRuangan::updateOrCreate(
                        [
                            'jadwal_ujian_id' => $jadwal->id,
                            'sesi_ruangan_id' => $sesi->id
                        ],
                        ['pengawas_id' => $request->pengawas_id]
                    );

                    $assigned++;
                } else {
                    $conflicts[] = $sesi->nama_sesi;
                }
            }

            DB::commit();

            $message = "{$assigned} sesi berhasil ditugaskan";

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned' => $assigned,
                'conflicts' => count($conflicts)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk assign pengawas', [
                'jadwal_ujian_id' => $request->jadwal_ujian_id,
                'session_ids' => $request->session_ids,
                'pengawas_id' => $request->pengawas_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign pengawas to a session (internal method)
     */
    private function assignPengawasToSession(JadwalUjian $jadwal, SesiRuangan $sesi, $pengawasId)
    {
        try {
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
                ->whereHas('jadwalUjian', function ($q) use ($date) {
                    $q->whereDate('tanggal', $date);
                })
                ->whereHas('sesiRuangan', function ($q) use ($sesi) {
                    // Check for time overlap
                    $q->where(function ($query) use ($sesi) {
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

            // Assign pengawas using the pivot table
            JadwalUjianSesiRuangan::updateOrCreate(
                [
                    'jadwal_ujian_id' => $jadwal->id,
                    'sesi_ruangan_id' => $sesi->id
                ],
                ['pengawas_id' => $pengawasId]
            );

            Log::info('Pengawas assigned to session', [
                'jadwal_id' => $jadwal->id,
                'session_id' => $sesi->id,
                'pengawas_id' => $pengawasId,
                'assigned_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Pengawas berhasil ditugaskan',
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
     */
    private function removePengawasFromSession(JadwalUjian $jadwal, SesiRuangan $sesi)
    {
        try {
            // Find the pivot record
            $pivot = JadwalUjianSesiRuangan::where('jadwal_ujian_id', $jadwal->id)
                ->where('sesi_ruangan_id', $sesi->id)
                ->first();

            if ($pivot) {
                $pengawasName = $pivot->pengawas ? $pivot->pengawas->nama : 'Unknown';

                // Update the pivot record
                $pivot->update(['pengawas_id' => null]);

                Log::info('Pengawas assignment removed', [
                    'jadwal_id' => $jadwal->id,
                    'session_id' => $sesi->id,
                    'removed_by' => auth()->id()
                ]);
            }

            return [
                'success' => true,
                'message' => 'Penugasan pengawas berhasil dibatalkan'
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
     * Get pengawas calendar view
     */
    public function calendar()
    {
        $pengawas = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->orderBy('nama')->get();

        return view('features.koordinator.pengawas_assignment.calendar', compact('pengawas'));
    }

    /**
     * Get all schedules for specific pengawas regardless of date
     */
    public function getAllSchedules($pengawasId)
    {
        $pengawas = Guru::findOrFail($pengawasId);

        // Get all session assignments for this pengawas on any date
        $assignments = JadwalUjianSesiRuangan::where('pengawas_id', $pengawasId)
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

                // Debug info
                Log::debug('Processing all_schedules entry', [
                    'jadwal_id' => $jadwal->id,
                    'has_mapel' => isset($jadwal->mapel),
                    'mapel_nama' => $jadwal->mapel->nama ?? null,
                    'judul' => $jadwal->judul ?? null
                ]);

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

    /**
     * Get calendar events for a specific pengawas
     */
    public function getCalendarEvents(Request $request)
    {
        $request->validate([
            'pengawas_id' => 'required|exists:guru,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $assignments = JadwalUjianSesiRuangan::where('pengawas_id', $request->pengawas_id)
            ->whereHas('jadwalUjian', function ($q) use ($request) {
                $q->whereBetween('tanggal', [$request->start_date, $request->end_date]);
            })
            ->with(['jadwalUjian.mapel', 'sesiRuangan.ruangan'])
            ->get();

        $events = $assignments->map(function ($assignment) {
            $sesi = $assignment->sesiRuangan;
            $jadwal = $assignment->jadwalUjian;
            $examDate = $jadwal->tanggal->format('Y-m-d');

            // Get proper room name
            $ruangan = $sesi->ruangan ? $sesi->ruangan->nama_ruangan : 'Ruangan Tidak Diketahui';

            // Debug info
            Log::debug('Processing calendar event', [
                'jadwal_id' => $jadwal->id,
                'has_mapel' => isset($jadwal->mapel),
                'mapel_nama' => $jadwal->mapel->nama ?? null,
                'judul' => $jadwal->judul ?? null
            ]);

            // Get proper subject name
            $mapel = null;
            if ($jadwal->mapel && isset($jadwal->mapel->nama)) {
                $mapel = $jadwal->mapel->nama;
            } elseif (isset($jadwal->judul)) {
                $mapel = $jadwal->judul;
            } else {
                $mapel = 'Mata Pelajaran Tidak Diketahui';
            }

            $event = [
                'id' => $assignment->id,
                'title' => $sesi->nama_sesi . ' - ' . $ruangan,
                'start' => $examDate . 'T' . $sesi->waktu_mulai,
                'end' => $examDate . 'T' . $sesi->waktu_selesai,
                'extendedProps' => [
                    'mapel' => $mapel,
                    'ruangan' => $ruangan,
                ],
                'backgroundColor' => $this->getStatusColor($sesi->status),
                'borderColor' => $this->getStatusColor($sesi->status),
            ];

            // Add for compatibility with direct properties
            $event['description'] = $mapel;
            $event['mapel'] = $mapel;
            $event['ruangan'] = $ruangan;

            return $event;
        });

        return response()->json($events);
    }

    private function getStatusColor($status)
    {
        return match ($status) {
            'belum_mulai' => '#3B82F6', // blue
            'berlangsung' => '#10B981', // green
            'selesai' => '#6B7280', // gray
            'dibatalkan' => '#EF4444', // red
            default => '#9CA3AF' // default gray
        };
    }
}
