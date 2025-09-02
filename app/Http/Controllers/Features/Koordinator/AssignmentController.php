<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\SesiRuangan;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    /**
     * Display assignment management page
     */
    public function index(Request $request)
    {
        $query = SesiRuangan::with(['ruangan', 'pengawas', 'sesiRuanganSiswa'])
            ->where('tanggal', '>=', Carbon::today()->subDays(7)); // Show sessions from last week

        // Filter by tanggal
        if ($request->filled('tanggal')) {
            $query->where('tanggal', $request->tanggal);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by assignment status
        if ($request->filled('assignment_status')) {
            if ($request->assignment_status === 'assigned') {
                $query->whereNotNull('pengawas_id');
            } elseif ($request->assignment_status === 'unassigned') {
                $query->whereNull('pengawas_id');
            }
        }

        // Filter by ruangan
        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        // Filter by pengawas
        if ($request->filled('pengawas_id')) {
            $query->where('pengawas_id', $request->pengawas_id);
        }

        // Handle pagination
        $perPage = $request->get('per_page', 15);
        if (!in_array($perPage, [15, 25, 50])) {
            $perPage = 15;
        }

        $sesiRuangans = $query->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->paginate($perPage);

        // Get available pengawas
        $availablePengawas = Guru::whereHas('roles', function ($query) {
            $query->where('name', 'pengawas');
        })->orderBy('nama')->get();

        // Get available ruangan
        $availableRuangan = Ruangan::where('status', 'aktif')
            ->orderBy('nama_ruangan')
            ->get();

        // Get statistics
        $stats = [
            'total_sesi' => SesiRuangan::where('tanggal', '>=', Carbon::today())->count(),
            'assigned' => SesiRuangan::whereNotNull('pengawas_id')
                ->where('tanggal', '>=', Carbon::today())->count(),
            'unassigned' => SesiRuangan::whereNull('pengawas_id')
                ->where('tanggal', '>=', Carbon::today())->count(),
            'berlangsung' => SesiRuangan::where('status', 'berlangsung')->count(),
            'total_pengawas' => $availablePengawas->count(),
        ];

        return view('features.koordinator.assignment.index', compact(
            'sesiRuangans',
            'availablePengawas',
            'availableRuangan',
            'stats'
        ));
    }

    /**
     * Assign pengawas to a session (route method)
     */
    public function assign(Request $request)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id',
            'pengawas_id' => 'required|exists:guru,id'
        ]);

        $sesi = SesiRuangan::findOrFail($request->sesi_ruangan_id);

        $result = $this->assignPengawasToSession($sesi, $request->pengawas_id);

        return response()->json($result);
    }

    /**
     * Remove pengawas assignment (route method)
     */
    public function unassign(Request $request)
    {
        $request->validate([
            'sesi_ruangan_id' => 'required|exists:sesi_ruangan,id'
        ]);

        $sesi = SesiRuangan::findOrFail($request->sesi_ruangan_id);

        $result = $this->removePengawasFromSession($sesi);

        return response()->json($result);
    }

    /**
     * Get schedule for specific pengawas and date
     */
    public function getSchedule($pengawasId, $tanggal)
    {
        $pengawas = Guru::findOrFail($pengawasId);
        $sessions = SesiRuangan::where('pengawas_id', $pengawasId)
            ->whereDate('tanggal', $tanggal)
            ->with(['ruangan'])
            ->orderBy('waktu_mulai')
            ->get();

        return view('features.koordinator.assignment.schedule', compact('pengawas', 'sessions', 'tanggal'));
    }

    /**
     * Assign pengawas to a session (internal method)
     */
    private function assignPengawasToSession(SesiRuangan $sesi, $pengawasId)
    {
        try {
            // Verify that the selected guru has pengawas role
            $pengawas = Guru::findOrFail($pengawasId);
            if (!$pengawas->hasRole('pengawas')) {
                return [
                    'success' => false,
                    'message' => 'Selected guru does not have pengawas role'
                ];
            }

            // Check for conflicts (same pengawas at same time)
            $conflict = SesiRuangan::where('pengawas_id', $pengawasId)
                ->where('tanggal', $sesi->tanggal)
                ->where('id', '!=', $sesi->id)
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
                })->exists();

            if ($conflict) {
                return [
                    'success' => false,
                    'message' => 'Pengawas already assigned to another session at the same time'
                ];
            }

            // Assign pengawas
            $sesi->update(['pengawas_id' => $pengawasId]);

            Log::info('Pengawas assigned to session', [
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
    private function removePengawasFromSession(SesiRuangan $sesi)
    {
        try {
            $pengawasName = $sesi->pengawas ? $sesi->pengawas->nama : 'Unknown';

            $sesi->update(['pengawas_id' => null]);

            Log::info('Pengawas assignment removed', [
                'session_id' => $sesi->id,
                'removed_by' => auth()->id()
            ]);

            return [
                'success' => true,
                'message' => 'Penugasan pengawas berhasil dibatalkan'
            ];
        } catch (\Exception $e) {
            Log::error('Error removing pengawas assignment', [
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
     * LEGACY METHOD - Assign pengawas to a session
     */
    public function assignPengawas(Request $request, SesiRuangan $sesi)
    {
        try {
            $pengawasName = $sesi->pengawas ? $sesi->pengawas->nama : 'Unknown';

            $sesi->update(['pengawas_id' => null]);

            Log::info('Pengawas assignment removed', [
                'session_id' => $sesi->id,
                'removed_by' => auth()->id()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Penugasan pengawas berhasil dibatalkan'
                ]);
            }

            return redirect()->back()->with('success', 'Penugasan pengawas berhasil dibatalkan');
        } catch (\Exception $e) {
            Log::error('Error removing pengawas assignment', [
                'session_id' => $sesi->id,
                'error' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Gagal membatalkan penugasan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk assign pengawas to multiple sessions
     */
    public function bulkAssign(Request $request)
    {
        $request->validate([
            'session_ids' => 'required|array',
            'session_ids.*' => 'exists:sesi_ruangan,id',
            'pengawas_id' => 'required|exists:guru,id'
        ]);

        try {
            DB::beginTransaction();

            // Verify that the selected guru has pengawas role
            $pengawas = Guru::findOrFail($request->pengawas_id);
            if (!$pengawas->hasRole('pengawas')) {
                throw new \Exception('Selected guru does not have pengawas role');
            }

            $assigned = 0;
            $conflicts = [];

            foreach ($request->session_ids as $sessionId) {
                $sesi = SesiRuangan::findOrFail($sessionId);

                // Check for conflicts
                $conflict = SesiRuangan::where('pengawas_id', $request->pengawas_id)
                    ->where('tanggal', $sesi->tanggal)
                    ->where('id', '!=', $sesi->id)
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
                    })->exists();

                if (!$conflict) {
                    $sesi->update(['pengawas_id' => $request->pengawas_id]);
                    $assigned++;
                } else {
                    $conflicts[] = $sesi->nama_sesi . ' (' . $sesi->tanggal->format('d/m/Y') . ')';
                }
            }

            DB::commit();

            $message = "{$assigned} sesi berhasil ditugaskan";
            if (count($conflicts) > 0) {
                $message .= ". Konflik waktu pada: " . implode(', ', $conflicts);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'assigned' => $assigned,
                'conflicts' => count($conflicts)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error in bulk assign', [
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
     * Get pengawas availability for a specific date and time
     */
    public function getPengawasAvailability(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'exclude_session_id' => 'nullable|exists:sesi_ruangan,id'
        ]);

        $date = $request->date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        $excludeSessionId = $request->exclude_session_id;

        // Get all pengawas
        $allPengawas = Guru::whereHas('roles', function ($query) {
            $query->where('name', 'pengawas');
        })->get();

        $availablePengawas = [];
        $unavailablePengawas = [];

        foreach ($allPengawas as $pengawas) {
            // Check if pengawas is already assigned at this time
            $conflict = SesiRuangan::where('pengawas_id', $pengawas->id)
                ->where('tanggal', $date)
                ->when($excludeSessionId, function ($query, $excludeSessionId) {
                    $query->where('id', '!=', $excludeSessionId);
                })
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('waktu_mulai', '<=', $startTime)
                            ->where('waktu_selesai', '>', $startTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('waktu_mulai', '<', $endTime)
                            ->where('waktu_selesai', '>=', $endTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('waktu_mulai', '>=', $startTime)
                            ->where('waktu_selesai', '<=', $endTime);
                    });
                })->first();

            if (!$conflict) {
                $availablePengawas[] = [
                    'id' => $pengawas->id,
                    'nama' => $pengawas->nama,
                    'nip' => $pengawas->nip,
                    'email' => $pengawas->email
                ];
            } else {
                $unavailablePengawas[] = [
                    'id' => $pengawas->id,
                    'nama' => $pengawas->nama,
                    'conflict_session' => $conflict->nama_sesi,
                    'conflict_ruangan' => $conflict->ruangan->nama_ruangan ?? 'Unknown',
                    'conflict_time' => $conflict->waktu_mulai . ' - ' . $conflict->waktu_selesai
                ];
            }
        }

        return response()->json([
            'available' => $availablePengawas,
            'unavailable' => $unavailablePengawas
        ]);
    }

    /**
     * Get pengawas schedule for calendar view
     */
    public function getPengawasSchedule(Request $request)
    {
        $request->validate([
            'pengawas_id' => 'required|exists:guru,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $schedule = SesiRuangan::where('pengawas_id', $request->pengawas_id)
            ->whereBetween('tanggal', [$request->start_date, $request->end_date])
            ->with(['ruangan'])
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get()
            ->map(function ($sesi) {
                return [
                    'id' => $sesi->id,
                    'title' => $sesi->nama_sesi,
                    'start' => $sesi->tanggal->format('Y-m-d') . 'T' . $sesi->waktu_mulai,
                    'end' => $sesi->tanggal->format('Y-m-d') . 'T' . $sesi->waktu_selesai,
                    'ruangan' => $sesi->ruangan->nama_ruangan,
                    'status' => $sesi->status,
                    'backgroundColor' => $this->getStatusColor($sesi->status),
                    'borderColor' => $this->getStatusColor($sesi->status),
                ];
            });

        return response()->json($schedule);
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
