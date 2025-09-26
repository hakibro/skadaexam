<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\SesiRuangan;
use App\Models\BeritaAcaraUjian;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $stats = $this->getKoordinatorStats();

        // Get today's sessions using jadwal_ujian's tanggal
        $todaySessions = SesiRuangan::with(['ruangan', 'sesiRuanganSiswa', 'jadwalUjians', 'jadwalUjians.mapel'])
            ->whereHas('jadwalUjians', function ($query) {
                $query->whereDate('tanggal', Carbon::today());
            })
            ->orderBy('waktu_mulai')
            ->get();

        // Get sessions without pengawas (need assignment)
        $unassignedSessions = DB::table('jadwal_ujian')
            ->join('jadwal_ujian_sesi_ruangan', 'jadwal_ujian.id', '=', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id')
            ->join('sesi_ruangan', 'jadwal_ujian_sesi_ruangan.sesi_ruangan_id', '=', 'sesi_ruangan.id')
            ->join('ruangan', 'sesi_ruangan.ruangan_id', '=', 'ruangan.id')
            ->whereNull('jadwal_ujian_sesi_ruangan.pengawas_id')
            ->where('jadwal_ujian.tanggal', '>=', Carbon::today())
            ->select(
                'sesi_ruangan.id',
                'sesi_ruangan.nama_sesi',
                'ruangan.nama_ruangan',
                'jadwal_ujian.tanggal'
            )
            ->orderBy('jadwal_ujian.tanggal')
            ->orderBy('sesi_ruangan.waktu_mulai')
            ->limit(10)
            ->get();

        // Get pending berita acara (need verification)
        $pendingBeritaAcara = BeritaAcaraUjian::with(['sesiRuangan.ruangan', 'pengawas'])
            ->where('is_final', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get available pengawas (guru with pengawas role)
        $availablePengawas = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->get();

        // Get ongoing sessions that need monitoring
        $ongoingSessions = SesiRuangan::where('status', 'berlangsung')
            ->with(['ruangan', 'sesiRuanganSiswa', 'jadwalUjians', 'jadwalUjians.mapel'])
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
                    'room_name' => $session->nama_ruangan ?? 'Ruangan',
                    'date' => $session->tanggal ? Carbon::parse($session->tanggal)->format('d M Y') : 'Hari ini',
                ];
            })->toArray(),
            'activeSessions' => $ongoingSessions->map(function ($session) {
                return [
                    'id' => $session->id,
                    'name' => $session->nama_sesi ?? 'Sesi Ujian',
                    'room' => $session->ruangan->nama_ruangan ?? 'N/A',
                    'students_present' => $session->sesiRuanganSiswa->where('status_kehadiran', 'hadir')->count(),
                    'progress' => $session->progress ?? 0,
                    'supervisor' => $session->pengawas_names ?? 'Belum ditentukan',
                ];
            })->toArray()
        ]);
    }

    private function getKoordinatorStats()
    {
        return [
            'total_pengawas' => Guru::whereHas('user', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'pengawas');
                });
            })->count(),

            'sessions_today' => SesiRuangan::whereHas('jadwalUjians', function ($q) {
                $q->whereDate('tanggal', Carbon::today());
            })->count(),

            'unassigned_sessions' => DB::table('jadwal_ujian')
                ->join('jadwal_ujian_sesi_ruangan', 'jadwal_ujian.id', '=', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id')
                ->whereNull('jadwal_ujian_sesi_ruangan.pengawas_id')
                ->where('jadwal_ujian.tanggal', '>=', Carbon::today())
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

        // Recent pengawas assignments (from new pivot table)
        $recentAssignments = DB::table('jadwal_ujian_sesi_ruangan')
            ->join('sesi_ruangan', 'jadwal_ujian_sesi_ruangan.sesi_ruangan_id', '=', 'sesi_ruangan.id')
            ->join('ruangan', 'sesi_ruangan.ruangan_id', '=', 'ruangan.id')
            ->join('guru', 'jadwal_ujian_sesi_ruangan.pengawas_id', '=', 'guru.id')
            ->join('jadwal_ujian', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id', '=', 'jadwal_ujian.id')
            ->whereNotNull('jadwal_ujian_sesi_ruangan.pengawas_id')
            ->select(
                'guru.nama as pengawas_nama',
                'ruangan.nama_ruangan',
                'sesi_ruangan.nama_sesi',
                'jadwal_ujian.tanggal',
                'jadwal_ujian_sesi_ruangan.updated_at'
            )
            ->orderBy('jadwal_ujian_sesi_ruangan.updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($assignment) {
                return [
                    'message' => "Pengawas {$assignment->pengawas_nama} ditugaskan ke {$assignment->nama_ruangan} ({$assignment->nama_sesi})",
                    'time' => Carbon::parse($assignment->updated_at)->diffForHumans(),
                    'timestamp' => $assignment->updated_at,
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

    public function showUploadForm()
    {

        return view('features.koordinator.upload-tata-tertib');
    }

    public function uploadTataTertib(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:2048', // hanya PDF, max 2MB
        ]);

        // Simpan dengan nama seragam
        $request->file('file')->move(public_path('storage'), 'tata_tertib.pdf');

        return back()->with('success', 'Tata Tertib berhasil diupload!');
    }
}
