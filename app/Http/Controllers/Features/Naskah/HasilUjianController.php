<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use App\Models\SesiUjian;
use App\Models\Siswa;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HasilUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiUjian', 'siswa.kelas']);

        // Filter by jadwal ujian
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        // Filter by kelas
        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $kelasId = $request->kelas_id;
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        // Filter by sesi
        if ($request->has('sesi_id') && $request->sesi_id != '') {
            $query->where('sesi_ujian_id', $request->sesi_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by lulus/tidak lulus
        if ($request->has('lulus') && $request->lulus != '') {
            $query->where('lulus', $request->lulus == 'yes');
        }

        // Search by siswa name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Clone query for stats before pagination
        $statsQuery = clone $query;

        // Get data for statistics
        $totalHasil = $statsQuery->count();
        $completedHasil = $statsQuery->where('status', 'selesai')->count();

        // Reset where clauses that were added by the count() calls
        $statsQuery = clone $query;

        // Get pass/fail statistics
        $passedCount = $statsQuery->where('status', 'selesai')
            ->where('lulus', true)
            ->count();

        // Calculate average score from completed tests only
        $statsQuery = clone $query;
        $averageScoreResult = $statsQuery->where('status', 'selesai')
            ->avg('nilai_akhir');
        $averageScore = $averageScoreResult ? number_format($averageScoreResult, 2) : '0.00';

        // Calculate pass rate
        $passRate = $completedHasil > 0
            ? number_format(($passedCount / $completedHasil) * 100, 1)
            : '0.0';

        // Get latest hasil ujian
        $statsQuery = clone $query;
        $latestHasil = $statsQuery->latest()->first();

        // Execute the main query with pagination
        $hasilUjians = $query->latest()->paginate(15);

        // Get unique values for filters
        $jadwalUjians = JadwalUjian::orderBy('tanggal_ujian', 'desc')->get();
        $sesiUjians = SesiUjian::orderBy('nama_sesi')->get();
        $kelasList = \App\Models\Kelas::orderBy('name', 'asc')->get();

        return view('features.naskah.hasil.index', compact(
            'hasilUjians',
            'jadwalUjians',
            'sesiUjians',
            'kelasList',
            'totalHasil',
            'completedHasil',
            'averageScore',
            'passRate',
            'passedCount',
            'latestHasil'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(HasilUjian $hasil)
    {
        $hasil->load(['jadwalUjian.mapel', 'jadwalUjian.bankSoal', 'sesiUjian', 'siswa.kelas']);

        return view('features.naskah.hasil.show', compact('hasil'));
    }

    /**
     * Show results by jadwal ujian.
     */
    public function byJadwal(JadwalUjian $jadwal)
    {
        $jadwal->load(['mapel', 'bankSoal', 'creator', 'sesiUjians']);

        $hasilUjians = HasilUjian::with(['sesiUjian', 'siswa.kelas'])
            ->where('jadwal_ujian_id', $jadwal->id)
            ->get();

        // Calculate statistics
        $totalPeserta = $hasilUjians->count();
        $selesai = $hasilUjians->where('status', 'selesai')->count();
        $belumMulai = $hasilUjians->where('status', 'belum_mulai')->count();
        $sedangUjian = $hasilUjians->where('status', 'sedang_ujian')->count();

        $lulus = $hasilUjians->where('lulus', true)->count();
        $tidakLulus = $hasilUjians->where('status', 'selesai')->where('lulus', false)->count();

        $rataRataNilai = $hasilUjians->where('status', 'selesai')->avg('nilai_akhir');
        $nilaiTertinggi = $hasilUjians->where('status', 'selesai')->max('nilai_akhir');
        $nilaiTerendah = $hasilUjians->where('status', 'selesai')->min('nilai_akhir');

        // Group by grade
        $grades = $hasilUjians->where('status', 'selesai')
            ->groupBy('grade')
            ->map(function ($items, $grade) {
                return [
                    'grade' => $grade,
                    'count' => $items->count(),
                ];
            })
            ->sortBy('grade')
            ->values();

        return view('features.naskah.hasil.by_jadwal', compact(
            'jadwal',
            'hasilUjians',
            'totalPeserta',
            'selesai',
            'belumMulai',
            'sedangUjian',
            'lulus',
            'tidakLulus',
            'rataRataNilai',
            'nilaiTertinggi',
            'nilaiTerendah',
            'grades'
        ));
    }

    /**
     * Show results by sesi ujian.
     */
    public function bySesi(JadwalUjian $jadwal, SesiUjian $sesi)
    {
        $jadwal->load(['mapel', 'bankSoal']);
        $sesi->load('jadwalUjian');

        $hasilUjians = HasilUjian::with(['jadwalUjian', 'siswa.kelas'])
            ->where('sesi_ujian_id', $sesi->id)
            ->get();

        // Calculate statistics
        $totalPeserta = $hasilUjians->count();
        $selesai = $hasilUjians->where('status', 'selesai')->count();
        $belumMulai = $hasilUjians->where('status', 'belum_mulai')->count();
        $sedangUjian = $hasilUjians->where('status', 'sedang_ujian')->count();

        $lulus = $hasilUjians->where('lulus', true)->count();
        $tidakLulus = $hasilUjians->where('status', 'selesai')->where('lulus', false)->count();

        $rataRataNilai = $hasilUjians->where('status', 'selesai')->avg('nilai_akhir');
        $nilaiTertinggi = $hasilUjians->where('status', 'selesai')->max('nilai_akhir');
        $nilaiTerendah = $hasilUjians->where('status', 'selesai')->min('nilai_akhir');

        return view('features.naskah.hasil.by_sesi', compact(
            'jadwal',
            'sesi',
            'hasilUjians',
            'totalPeserta',
            'selesai',
            'belumMulai',
            'sedangUjian',
            'lulus',
            'tidakLulus',
            'rataRataNilai',
            'nilaiTertinggi',
            'nilaiTerendah'
        ));
    }

    /**
     * Export results to Excel.
     */
    public function export(Request $request)
    {
        // Implement Excel export using Laravel Excel package
        return redirect()->back()->with('info', 'Fitur ekspor akan segera tersedia');
    }

    /**
     * Delete a hasil ujian.
     */
    public function destroy(HasilUjian $hasil)
    {
        $jadwalId = $hasil->jadwal_ujian_id;
        $hasil->delete();

        return redirect()->route('naskah.hasil.by-jadwal', $jadwalId)
            ->with('success', 'Data hasil ujian berhasil dihapus');
    }

    /**
     * Show detailed analysis of results.
     */
    public function analisis(Request $request)
    {
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiUjian', 'siswa.kelas']);

        // Apply filters similar to index method
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $kelasId = $request->kelas_id;
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        // Only consider completed tests for analysis
        $query->where('status', 'selesai');

        $hasilUjians = $query->get();

        // Basic statistics
        $totalHasil = $hasilUjians->count();
        $avgNilai = $totalHasil > 0 ? $hasilUjians->avg('nilai_akhir') : 0;
        $maxNilai = $totalHasil > 0 ? $hasilUjians->max('nilai_akhir') : 0;
        $minNilai = $totalHasil > 0 ? $hasilUjians->min('nilai_akhir') : 0;

        // Group results by score ranges
        $scoreRanges = [
            '91-100' => 0,
            '81-90' => 0,
            '71-80' => 0,
            '61-70' => 0,
            '51-60' => 0,
            '0-50' => 0
        ];

        foreach ($hasilUjians as $hasil) {
            $nilai = $hasil->nilai_akhir;

            if ($nilai >= 91) {
                $scoreRanges['91-100']++;
            } elseif ($nilai >= 81) {
                $scoreRanges['81-90']++;
            } elseif ($nilai >= 71) {
                $scoreRanges['71-80']++;
            } elseif ($nilai >= 61) {
                $scoreRanges['61-70']++;
            } elseif ($nilai >= 51) {
                $scoreRanges['51-60']++;
            } else {
                $scoreRanges['0-50']++;
            }
        }

        // Group results by kelas for comparison
        $kelasPerfomance = [];
        if ($totalHasil > 0) {
            $kelasPerfomance = $hasilUjians
                ->groupBy(function ($item) {
                    return $item->siswa->kelas->name ?? 'Tanpa Kelas';
                })
                ->map(function ($items, $kelas) {
                    return [
                        'kelas' => $kelas,
                        'jumlah' => $items->count(),
                        'rata_rata' => $items->avg('nilai_akhir'),
                        'lulus' => $items->where('lulus', true)->count(),
                        'tidak_lulus' => $items->where('lulus', false)->count(),
                    ];
                })
                ->sortByDesc('rata_rata')
                ->values();
        }

        // Get filters for the view
        $jadwalUjians = JadwalUjian::orderBy('tanggal_ujian', 'desc')->get();
        $kelasList = \App\Models\Kelas::orderBy('name', 'asc')->get();

        return view('features.naskah.hasil.analisis', compact(
            'hasilUjians',
            'totalHasil',
            'avgNilai',
            'maxNilai',
            'minNilai',
            'scoreRanges',
            'kelasPerfomance',
            'jadwalUjians',
            'kelasList'
        ));
    }
}
