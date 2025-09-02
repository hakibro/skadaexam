<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankSoal;
use App\Models\Soal;
use App\Models\JadwalUjian;
use App\Models\HasilUjian;
use App\Models\User;
use App\Models\Mapel;
use App\Models\SesiUjian;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get statistics
            $bankSoalCount = BankSoal::count();
            $soalCount = Soal::count();
            $jadwalUjianCount = JadwalUjian::count();
            $hasilUjianCount = HasilUjian::count();
            $mapelCount = Mapel::count();
            $sesiCount = SesiUjian::count();
            $kelasCount = Kelas::count();

            // Get pass rate statistics
            $passedResults = HasilUjian::where('is_final', true)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // Jika menggunakan skor
                        $q->whereRaw('(skor / jumlah_soal) >= 0.7');
                    });
                })->count();
            $totalCompletedResults = HasilUjian::where('is_final', true)->count();
            $passRate = $totalCompletedResults > 0 ? round(($passedResults / $totalCompletedResults) * 100) : 0;

            // Get recently created bank soals
            $recentBankSoals = BankSoal::with('creator')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Get recently created or updated soals
            $recentSoals = Soal::with('bankSoal')
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();

            // Get recently completed exams
            $recentResults = HasilUjian::with(['siswa', 'jadwalUjian.bankSoal'])
                ->where('is_final', true)
                ->orderBy('waktu_selesai', 'desc')
                ->limit(5)
                ->get();

            // Get statistics by tingkat/kelas
            $soalsByTingkat = BankSoal::select('tingkat', DB::raw('COUNT(*) as count'))
                ->groupBy('tingkat')
                ->get();

            // Get statistics by soal type
            $soalsByType = Soal::select('tipe_soal', DB::raw('COUNT(*) as count'))
                ->groupBy('tipe_soal')
                ->get();

            // Get statistics by subject
            $soalsByMapel = BankSoal::join('mapel', 'bank_soal.mapel_id', '=', 'mapel.id')
                ->select('mapel.nama_mapel', DB::raw('COUNT(bank_soal.id) as count'))
                ->groupBy('mapel.id', 'mapel.nama_mapel')
                ->get();

            // Get upcoming exams - Fix, using tanggal_mulai
            $upcomingExams = JadwalUjian::with(['bankSoal', 'mapel'])
                ->where('tanggal_mulai', '>=', Carbon::today())
                ->where('status', 'active')
                ->orderBy('tanggal_mulai', 'asc')
                ->limit(3)
                ->get();

            // Get recent activities
            $recentActivities = $this->getRecentActivities();

            return view('features.naskah.dashboard', compact(
                'bankSoalCount',
                'soalCount',
                'jadwalUjianCount',
                'hasilUjianCount',
                'mapelCount',
                'sesiCount',
                'kelasCount',
                'passRate',
                'recentBankSoals',
                'recentSoals',
                'recentResults',
                'soalsByTingkat',
                'soalsByType',
                'soalsByMapel',
                'upcomingExams',
                'recentActivities'
            ));
        } catch (\Exception $e) {
            // Log error
            Log::error('Error in Naskah Dashboard: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Return a basic view with error
            return view('features.naskah.dashboard', [
                'error' => 'Terjadi kesalahan saat memuat data dashboard. Silakan coba lagi nanti.'
            ]);
        }
    }

    /**
     * Get recent activities based on created and updated records
     */
    private function getRecentActivities()
    {
        $activities = collect();

        // Add bank soal activities
        $bankSoalActivities = BankSoal::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'bank_soal',
                    'action' => 'created',
                    'title' => $item->judul,
                    'actor' => $item->creator->name ?? 'System',
                    'time' => $item->created_at,
                ];
            });
        $activities = $activities->concat($bankSoalActivities);

        // Add jadwal ujian activities
        $jadwalActivities = JadwalUjian::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'jadwal_ujian',
                    'action' => 'created',
                    'title' => $item->judul,
                    'actor' => $item->creator->name ?? 'System',
                    'time' => $item->created_at,
                ];
            });
        $activities = $activities->concat($jadwalActivities);

        // Sort by time (newest first) and take only 10
        return $activities->sortByDesc('time')->take(10);
    }
}
