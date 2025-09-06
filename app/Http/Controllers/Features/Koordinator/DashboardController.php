<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = $this->getKoordinatorStats();

        // Get today's sessions
        $todaySessions = SesiRuangan::with(['ruangan', 'pengawas', 'sesiRuanganSiswa'])
            ->where('tanggal', Carbon::today())
            ->orderBy('waktu_mulai')
            ->get();

        // Get sessions without pengawas (need assignment)
        $unassignedSessions = SesiRuangan::whereNull('pengawas_id')
            ->where('tanggal', '>=', Carbon::today())
            ->with(['ruangan'])
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->limit(10)
            ->get();

        // Get pending berita acara (need verification)
        $pendingBeritaAcara = BeritaAcaraUjian::with(['sesiRuangan.ruangan', 'pengawas'])
            ->where('is_final', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get available pengawas (guru with pengawas role)
        $availablePengawas = Guru::whereHas('roles', function ($query) {
            $query->where('name', 'pengawas');
        })->get();

        // Get ongoing sessions that need monitoring
        $ongoingSessions = SesiRuangan::where('status', 'berlangsung')
            ->with(['ruangan', 'pengawas', 'sesiRuanganSiswa'])
            ->get();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        return view('features.koordinator.dashboard', compact(
            'stats',
            'todaySessions',
            'unassignedSessions',
            'pendingBeritaAcara',
            'availablePengawas',
            'ongoingSessions',
            'recentActivities'
        ))->with([
            'pendingAssignments' => $unassignedSessions->map(function ($session) {
                return [
                    'session_name' => $session->nama_sesi ?? 'Sesi Ujian',
                    'room_name' => $session->ruangan->nama_ruangan ?? 'Ruangan',
                    'date' => $session->tanggal ? $session->tanggal->format('d M Y') : 'Hari ini',
                ];
            })->toArray(),
            'activeSessions' => $ongoingSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'name' => $session->nama_sesi ?? 'Sesi Ujian',
                    'room' => $session->ruangan->nama_ruangan ?? 'N/A',
                    'students_present' => $session->sesiRuanganSiswa->where('status', 'hadir')->count(),
                    'progress' => $session->progress ?? 0,
                    'supervisor' => $session->pengawas->nama ?? 'N/A',
                ];
            })->toArray()
        ]);
    }

    private function getKoordinatorStats()
    {
        return [
            'total_pengawas' => Guru::whereHas('roles', function ($query) {
                $query->where('name', 'pengawas');
            })->count(),

            'sessions_today' => SesiRuangan::whereHas('jadwalUjians', function ($q) {
                $q->whereDate('tanggal', Carbon::today());
            })->count(),

            'unassigned_sessions' => SesiRuangan::whereNull('pengawas_id')
                ->whereHas('jadwalUjians', function ($q) {
                    $q->whereDate('tanggal', '>=', Carbon::today());
                })
                ->count(),

            'draft_berita_acara' => BeritaAcaraUjian::where('is_final', false)->count(),

            'ongoing_sessions' => SesiRuangan::where('status', 'berlangsung')->count(),

            'finalized_berita_acara_today' => BeritaAcaraUjian::where('is_final', true)
                ->whereDate('waktu_finalisasi', Carbon::today())
                ->count(),

            'total_ruangan_aktif' => Ruangan::where('status', 'aktif')->count(),

            'sessions_this_week' => SesiRuangan::whereHas('jadwalUjians', function ($q) {
                $q->whereBetween('tanggal', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ]);
            })->count(),
        ];
    }

    private function getRecentActivities()
    {
        $activities = collect();

        // Recent pengawas assignments
        $recentAssignments = SesiRuangan::whereNotNull('pengawas_id')
            ->with(['pengawas', 'ruangan'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($sesi) {
                return [
                    'message' => "Pengawas {$sesi->pengawas->nama} ditugaskan ke {$sesi->ruangan->nama_ruangan}",
                    'time' => $sesi->updated_at->diffForHumans(),
                    'timestamp' => $sesi->updated_at,
                    'icon' => 'fa-user-plus',
                    'icon_bg' => 'bg-blue-100',
                    'icon_color' => 'text-blue-600'
                ];
            });

        $activities = $activities->concat($recentAssignments);

        // Recent berita acara submissions
        $recentBeritaAcara = BeritaAcaraUjian::with(['pengawas', 'sesiRuangan.ruangan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($berita) {
                $status = $berita->is_final ? 'Finalized' : 'Draft';
                return [
                    'message' => "Berita acara {$status} dari {$berita->pengawas->nama}",
                    'time' => $berita->created_at->diffForHumans(),
                    'timestamp' => $berita->created_at,
                    'icon' => 'fa-file-alt',
                    'icon_bg' => $berita->is_final ? 'bg-green-100' : 'bg-yellow-100',
                    'icon_color' => $berita->is_final ? 'text-green-600' : 'text-yellow-600'
                ];
            });

        $activities = $activities->concat($recentBeritaAcara);

        return $activities->sortByDesc('timestamp')->take(10)->values()->toArray();
    }
}
