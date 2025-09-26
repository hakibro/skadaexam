<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\SesiRuanganSiswa;
use App\Models\Ruangan;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Display monitoring dashboard
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedStatus = $request->get('status', 'all');
        $selectedRuangan = $request->get('ruangan_id', 'all');

        // Build query for sessions
        $query = SesiRuangan::with(['ruangan', 'pengawas', 'sesiRuanganSiswa.siswa', 'jadwalUjians'])
            ->whereHas('jadwalUjians', function ($q) use ($selectedDate) {
                $q->whereDate('tanggal', $selectedDate);
            });

        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedRuangan !== 'all') {
            $query->where('ruangan_id', $selectedRuangan);
        }

        $sessions = $query->orderBy('waktu_mulai')->get();

        // Get monitoring statistics
        $stats = $this->getMonitoringStats($selectedDate);

        // Get available ruangan for filter
        $availableRuangan = Ruangan::where('status', 'aktif')
            ->orderBy('nama_ruangan')
            ->get();

        // Get real-time data for ongoing sessions
        $activeSessions = SesiRuangan::where('status', 'berlangsung')
            ->with(['ruangan', 'pengawas', 'sesiRuanganSiswa', 'jadwalUjians'])
            ->get()
            ->map(function ($session) {
                // Add computed properties for the view
                $session->status_border_class = match ($session->status) {
                    'berlangsung' => 'border-green-500',
                    'belum_mulai' => 'border-yellow-500',
                    'selesai' => 'border-gray-500',
                    'dibatalkan' => 'border-red-500',
                    default => 'border-gray-300'
                };

                $session->status_badge_class = match ($session->status) {
                    'berlangsung' => 'bg-green-100 text-green-800',
                    'belum_mulai' => 'bg-yellow-100 text-yellow-800',
                    'selesai' => 'bg-gray-100 text-gray-800',
                    'dibatalkan' => 'bg-red-100 text-red-800',
                    default => 'bg-gray-100 text-gray-800'
                };

                $session->status_label = match ($session->status) {
                    'berlangsung' => 'Berlangsung',
                    'belum_mulai' => 'Belum Mulai',
                    'selesai' => 'Selesai',
                    'dibatalkan' => 'Dibatalkan',
                    default => 'Unknown'
                };

                // Calculate progress if ongoing
                if ($session->status === 'berlangsung') {
                    $tanggal = $session->jadwalUjians->first()?->tanggal;

                    if ($tanggal) {
                        $tanggalCarbon = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

                        $startTime = $tanggalCarbon->copy()->setTimeFromTimeString($session->waktu_mulai);
                        $endTime   = $tanggalCarbon->copy()->setTimeFromTimeString($session->waktu_selesai);
                        $now = Carbon::now();

                        if ($now->between($startTime, $endTime)) {
                            $totalDuration = $endTime->diffInMinutes($startTime);
                            $elapsedDuration = $now->diffInMinutes($startTime);
                            $session->progress_percentage = min(100, round(($elapsedDuration / $totalDuration) * 100));
                            $session->remaining_time = $endTime->diffForHumans($now, true) . ' tersisa';
                        } elseif ($now->gt($endTime)) {
                            $session->progress_percentage = 100;
                            $session->remaining_time = 'Selesai';
                        } else {
                            $session->progress_percentage = 0;
                            $session->remaining_time = 'Belum mulai';
                        }
                    } else {
                        // Fallback kalau tidak ada tanggal dari jadwal
                        $session->progress_percentage = 0;
                        $session->remaining_time = '';
                    }
                } else {
                    $session->progress_percentage = 0;
                    $session->remaining_time = '';
                }

                return $session;
            });

        // Get rooms for filter dropdown
        $rooms = Ruangan::where('status', 'aktif')->orderBy('nama_ruangan')->get();

        return view('features.koordinator.monitoring.index', compact(
            'sessions',
            'stats',
            'availableRuangan',
            'activeSessions',
            'rooms',
            'selectedDate',
            'selectedStatus',
            'selectedRuangan'
        ));
    }


    /**
     * Get real-time session data via AJAX
     */
    public function getSessionData(SesiRuangan $sesi)
    {
        $sesi->load(['ruangan', 'sesiRuanganSiswa.siswa', 'beritaAcaraUjian', 'jadwalUjians']);

        $studentStats = [
            'total' => $sesi->sesiRuanganSiswa->count(),
            'hadir' => $sesi->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count(),
            'tidak_hadir' => $sesi->sesiRuanganSiswa->where('status_kehadiran', 'tidak_hadir')->count(),
            'logout' => 0, // Logout functionality removed from current design
        ];

        $attendanceRate = $studentStats['total'] > 0
            ? round(($studentStats['hadir'] / $studentStats['total']) * 100, 1)
            : 0;

        return response()->json([
            'session' => [
                'id' => $sesi->id,
                'nama_sesi' => $sesi->nama_sesi,
                'kode_sesi' => $sesi->kode_sesi,
                'status' => $sesi->status,
                'tanggal' => $sesi->tanggal->format('d/m/Y'),
                'waktu_mulai' => $sesi->waktu_mulai,
                'waktu_selesai' => $sesi->waktu_selesai,
                'token_ujian' => $sesi->token_ujian,
                'token_expired_at' => $sesi->token_expired_at?->format('Y-m-d H:i:s'),
            ],
            'ruangan' => [
                'nama' => $sesi->ruangan->nama_ruangan,
                'kode' => $sesi->ruangan->kode_ruangan,
                'kapasitas' => $sesi->ruangan->kapasitas,
                'lokasi' => $sesi->ruangan->lokasi,
            ],
            'pengawas' => $sesi->pengawas ? [
                'nama' => $sesi->pengawas->nama,
                'nip' => $sesi->pengawas->nip,
                'email' => $sesi->pengawas->email,
            ] : null,
            'students' => $studentStats,
            'attendance_rate' => $attendanceRate,
            'has_berita_acara' => $sesi->beritaAcaraUjian ? true : false,
            'berita_acara_status' => $sesi->beritaAcaraUjian?->is_final ? 'finalized' : 'draft',
        ]);
    }

    /**
     * Get live updates for ongoing sessions
     */
    public function getLiveUpdates()
    {
        $ongoingSessions = SesiRuangan::where('status', 'berlangsung')
            ->with(['ruangan', 'pengawas', 'sesiRuanganSiswa'])
            ->get()
            ->map(function ($sesi) {
                $studentStats = [
                    'total' => $sesi->sesiRuanganSiswa->count(),
                    'hadir' => $sesi->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count(),
                    'tidak_hadir' => $sesi->sesiRuanganSiswa->where('status_kehadiran', 'tidak_hadir')->count(),
                    // 'logout' => $sesi->sesiRuanganSiswa->where('status_kehadiran', 'logout')->count(),
                ];

                return [
                    'id' => $sesi->id,
                    'nama_sesi' => $sesi->nama_sesi,
                    'ruangan' => $sesi->ruangan->nama_ruangan,
                    'pengawas' => $sesi->pengawas?->nama ?? 'Belum ditugaskan',
                    'students' => $studentStats,
                    'attendance_rate' => $studentStats['total'] > 0
                        ? round(($studentStats['hadir'] / $studentStats['total']) * 100, 1)
                        : 0,
                    'duration_minutes' => Carbon::now()->diffInMinutes(Carbon::parse($sesi->waktu_mulai)),
                ];
            });

        return response()->json([
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'ongoing_sessions' => $ongoingSessions,
            'summary' => [
                'total_ongoing' => $ongoingSessions->count(),
                'total_students' => $ongoingSessions->sum('students.total'),
                'total_present' => $ongoingSessions->sum('students.hadir'),
            ]
        ]);
    }

    /**
     * Update session status
     */
    public function updateSessionStatus(Request $request, SesiRuangan $sesi)
    {
        $request->validate([
            'status' => 'required|in:belum_mulai,berlangsung,selesai,dibatalkan'
        ]);

        try {
            $oldStatus = $sesi->status;
            $sesi->update(['status' => $request->status]);

            // Log status change
            Log::info('Session status updated', [
                'session_id' => $sesi->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status sesi berhasil diperbarui',
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating session status', [
                'session_id' => $sesi->id,
                'status' => $request->status,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate session token
     */
    public function generateSessionToken(SesiRuangan $sesi)
    {
        try {
            $token = $sesi->generateToken();

            return response()->json([
                'success' => true,
                'message' => 'Token berhasil dibuat',
                'token' => $token,
                'expired_at' => $sesi->token_expired_at->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating session token', [
                'session_id' => $sesi->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getMonitoringStats($date)
    {
        $sessionsToday = SesiRuangan::whereHas('jadwalUjians', function ($q) use ($date) {
            $q->whereDate('tanggal', $date);
        });

        $activeSessions = (clone $sessionsToday)->where('status', 'berlangsung')->count();


        $activeStudents = SesiRuanganSiswa::where('status_kehadiran', 'hadir')
            ->whereHas('sesiRuangan', function ($query) use ($date) {
                $query->where('status', 'berlangsung')
                    ->whereHas('jadwalUjians', function ($q) use ($date) {
                        $q->whereDate('tanggal', $date);
                    });
            })->count();

        $issues = 0; // Logout functionality removed from current design

        $onlineProctors = Guru::whereHas('sesiRuanganDiawasi', function ($query) use ($date) {
            $query->where('status', 'berlangsung')
                ->whereHas('jadwalUjians', function ($q) use ($date) {
                    $q->whereDate('tanggal', $date);
                });
        })->count();

        return [
            'active_sessions' => $activeSessions,
            'active_students' => $activeStudents,
            'issues' => $issues,
            'online_proctors' => $onlineProctors,

            // Keep the existing stats as well
            'total_sessions' => $sessionsToday->count(),
            'belum_mulai' => $sessionsToday->clone()->where('status', 'belum_mulai')->count(),
            'berlangsung' => $activeSessions,
            'selesai' => $sessionsToday->clone()->where('status', 'selesai')->count(),
            'dibatalkan' => $sessionsToday->clone()->where('status', 'dibatalkan')->count(),
            'total_students' => SesiRuanganSiswa::whereHas('sesiRuangan.jadwalUjians', function ($query) use ($date) {
                $query->whereDate('tanggal', $date);
            })->count(),
            'students_present' => SesiRuanganSiswa::where('status_kehadiran', 'hadir')
                ->whereHas('sesiRuangan.jadwalUjians', function ($q) use ($date) {
                    $q->whereDate('tanggal', $date);
                })->count(),
            'unassigned_sessions' => $sessionsToday->clone()
                ->whereDoesntHave('jadwalUjians', function ($query) {
                    $query->whereNotNull('jadwal_ujian_sesi_ruangan.pengawas_id');
                })->count(),
            'active_pengawas' => $onlineProctors,
        ];
    }

    /**
     * Show detailed view of a session
     */
    public function show(SesiRuangan $sesi)
    {
        $sesi->load(['ruangan', 'sesiRuanganSiswa.siswa', 'beritaAcaraUjian', 'jadwalUjians']);

        return view('features.koordinator.monitoring.show', compact('sesi'));
    }

    /**
     * Send message to supervisor
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:sesi_ruangan,id',
            'type' => 'required|in:info,warning,urgent',
            'message' => 'required|string|max:500'
        ]);

        try {
            // Here you would implement the messaging logic
            // For now, just return success

            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim ke pengawas'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Allow student to re-enter
     */
    public function allowReentry(Request $request)
    {
        $request->validate([
            'student_session_id' => 'required|exists:sesi_ruangan_siswa,id'
        ]);

        try {
            $studentSession = SesiRuanganSiswa::findOrFail($request->student_session_id);
            $studentSession->update(['status_kehadiran' => 'hadir']);

            return response()->json([
                'success' => true,
                'message' => 'Siswa berhasil diizinkan masuk kembali'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengizinkan siswa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student session details
     */
    public function studentDetail($studentSession)
    {
        $studentSession = SesiRuanganSiswa::with(['siswa', 'sesiRuangan'])
            ->findOrFail($studentSession);

        return response()->json([
            'student' => $studentSession->siswa,
            'session' => $studentSession->sesiRuangan,
            'status' => $studentSession->status,
            'login_time' => $studentSession->login_time,
            'logout_time' => $studentSession->logout_time
        ]);
    }

    /**
     * Export monitoring report
     */
    public function export(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $status = $request->get('status', 'all');
        $room = $request->get('room', 'all');

        // Get sessions data
        $sessions = SesiRuangan::with(['ruangan', 'pengawas', 'sesiRuanganSiswa', 'jadwalUjians'])
            ->whereHas('jadwalUjians', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            })
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($room !== 'all', function ($query) use ($room) {
                return $query->where('ruangan_id', $room);
            })
            ->orderBy('waktu_mulai')
            ->get();

        // For now, return a simple CSV
        $filename = 'monitoring-' . $date . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function () use ($sessions) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Nama Sesi',
                'Ruangan',
                'Pengawas',
                'Status',
                'Waktu',
                'Total Siswa',
                'Hadir',
                'Tidak Hadir'
            ]);

            // CSV data
            foreach ($sessions as $session) {
                fputcsv($file, [
                    $session->nama_sesi,
                    $session->ruangan->nama_ruangan ?? 'N/A',
                    $session->pengawas->nama ?? 'Belum ditugaskan',
                    $session->status,
                    $session->waktu_mulai . ' - ' . $session->waktu_selesai,
                    $session->sesiRuanganSiswa->count(),
                    $session->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count(),
                    $session->sesiRuanganSiswa->where('status_kehadiran', 'tidak_hadir')->count()
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
