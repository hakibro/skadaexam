<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();

        // Get total ruangan
        $totalRuangan = Ruangan::forTahunAjaran($activeYearId)->count();

        // Get total active ruangan
        $ruanganAktif = Ruangan::forTahunAjaran($activeYearId)->where('status', 'aktif')->count();

        // Calculate total kapasitas
        $kapasitasTotal = Ruangan::forTahunAjaran($activeYearId)->sum('kapasitas');

        // Get ongoing sessions (sesi ruangan yang sedang berlangsung)
        // We no longer use the tanggal column directly, instead we check the jadwal_ujians relationship
        $ongoingSessions = SesiRuangan::forTahunAjaran($activeYearId)
            ->where(function ($query) {
                $query->where('status', 'berlangsung')
                    ->orWhere(function ($query) {
                $query->where('status', 'belum_mulai')
                    ->where('waktu_mulai', '<=', now()->format('H:i:s'))
                    ->where('waktu_selesai', '>=', now()->format('H:i:s'))
                    ->whereHas('jadwalUjians', function ($q) {
                        $q->whereDate('tanggal', now()->toDateString());
                    });
                    });
            })
            ->with(['ruangan', 'sesiRuanganSiswa', 'jadwalUjians'])
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Count ongoing sessions
        $sesiAktif = $ongoingSessions->count();

        // Get today's sessions - using jadwalUjians relationship to get sessions for today
        $todaySessions = SesiRuangan::whereHas('jadwalUjians', function ($query) {
            $query->whereDate('tanggal', now()->toDateString());
        })
            ->forTahunAjaran($activeYearId)
            ->with(['ruangan'])
            ->withCount('sesiRuanganSiswa')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        // Get 5 most recent ruangan
        $recentRuangan = Ruangan::with('sesiRuangan')
            ->forTahunAjaran($activeYearId)
            ->withCount('sesiRuangan')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get room utilization stats
        $ruanganStats = [
            'aktif' => Ruangan::forTahunAjaran($activeYearId)->where('status', 'aktif')->count(),
            'perbaikan' => Ruangan::forTahunAjaran($activeYearId)->where('status', 'perbaikan')->count(),
            'tidak_aktif' => Ruangan::forTahunAjaran($activeYearId)->where('status', 'tidak_aktif')->count(),
        ];

        // Get session stats for today
        $sessionStats = [
            'belum_mulai' => SesiRuangan::whereHas('jadwalUjians', function ($q) {
                $q->whereDate('tanggal', now()->toDateString());
            })->forTahunAjaran($activeYearId)->where('status', 'belum_mulai')->count(),
            'berlangsung' => SesiRuangan::forTahunAjaran($activeYearId)->where('status', 'berlangsung')->count(),
            'selesai' => SesiRuangan::whereHas('jadwalUjians', function ($q) {
                $q->whereDate('tanggal', now()->toDateString());
            })->forTahunAjaran($activeYearId)->where('status', 'selesai')->count(),
        ];

        // Get capacity utilization
        $totalKapasitasHariIni = SesiRuangan::whereHas('jadwalUjians', function ($q) {
            $q->whereDate('tanggal', now()->toDateString());
        })
            ->forTahunAjaran($activeYearId)
            ->with('ruangan')
            ->get()
            ->sum(function ($sesi) {
                return $sesi->ruangan->kapasitas ?? 0;
            });

        $totalSiswaHariIni = SesiRuangan::whereHas('jadwalUjians', function ($q) {
            $q->whereDate('tanggal', now()->toDateString());
        })
            ->forTahunAjaran($activeYearId)
            ->withCount('sesiRuanganSiswa')
            ->get()
            ->sum('sesi_ruangan_siswa_count');

        return view('features.ruangan.dashboard', compact(
            'totalRuangan',
            'ruanganAktif',
            'kapasitasTotal',
            'sesiAktif',
            'recentRuangan',
            'ongoingSessions',
            'todaySessions',
            'ruanganStats',
            'sessionStats',
            'totalKapasitasHariIni',
            'totalSiswaHariIni'
        ));
    }
}
