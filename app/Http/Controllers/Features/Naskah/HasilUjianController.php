<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class HasilUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas']);

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
        // Filter by tingkat
        if ($request->filled('tingkat')) {
            $tingkat = $request->tingkat;
            $query->whereHas('siswa.kelas', function ($q) use ($tingkat) {
                $q->where('tingkat', $tingkat);
            });
        }

        // Filter by jurusan
        if ($request->filled('jurusan')) {
            $jurusan = $request->jurusan;
            $query->whereHas('siswa.kelas', function ($q) use ($jurusan) {
                $q->where('jurusan', $jurusan);
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
            ->avg('nilai');
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
        $jadwalUjians = JadwalUjian::orderBy('tanggal', 'desc')->get();
        $sesiRuangans = SesiRuangan::orderBy('nama_sesi')->get();
        $kelasList = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();

        return view('features.naskah.hasil.index', compact(
            'hasilUjians',
            'jadwalUjians',
            'sesiRuangans',
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
        // Load relasi yang dibutuhkan
        $hasil->load([
            'jadwalUjian.mapel',
            'jadwalUjian.bankSoal',
            'sesiRuangan',
            'siswa.kelas'
        ]);

        // Variabel tambahan untuk view
        $mapel = $hasil->jadwalUjian->mapel;

        // Analisis kategori jawaban (misal ada method di model HasilUjian)
        $kategoriAnalisis = $hasil->getKategoriAnalisis() ?? [];

        // Ambil hasil ujian lain dari siswa yang sama
        $otherResults = HasilUjian::where('siswa_id', $hasil->siswa_id)
            ->where('id', '!=', $hasil->id)
            ->get();

        return view('features.naskah.hasil.show', compact(
            'hasil',
            'mapel',
            'kategoriAnalisis',
            'otherResults'
        ));
    }
    /**
     * Show results by jadwal ujian.
     */
    public function byJadwal(JadwalUjian $jadwal)
    {
        $jadwal->load(['mapel', 'bankSoal', 'creator', 'sesiRuangan']);

        $hasilUjians = HasilUjian::with(['sesiRuangan', 'siswa.kelas'])
            ->where('jadwal_ujian_id', $jadwal->id)
            ->get();

        // Calculate statistics
        $totalPeserta = $hasilUjians->count();
        $selesai = $hasilUjians->where('status', 'selesai')->count();
        $belumMulai = $hasilUjians->where('status', 'belum_mulai')->count();
        $sedangUjian = $hasilUjians->where('status', 'sedang_ujian')->count();

        $lulus = $hasilUjians->where('lulus', true)->count();
        $tidakLulus = $hasilUjians->where('status', 'selesai')->where('lulus', false)->count();

        $rataRataNilai = $hasilUjians->where('status', 'selesai')->avg('nilai');
        $nilaiTertinggi = $hasilUjians->where('status', 'selesai')->max('nilai');
        $nilaiTerendah = $hasilUjians->where('status', 'selesai')->min('nilai');

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
    public function bySesi(JadwalUjian $jadwal, SesiRuangan $sesi)
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

        $rataRataNilai = $hasilUjians->where('status', 'selesai')->avg('nilai');
        $nilaiTertinggi = $hasilUjians->where('status', 'selesai')->max('nilai');
        $nilaiTerendah = $hasilUjians->where('status', 'selesai')->min('nilai');

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
     * Export results to Excel, CSV, or PDF.
     */
    public function export(Request $request)
    {
        $query = HasilUjian::query();

        // Apply filters just like in the index method
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        if ($request->has('kelas_id') && $request->kelas_id != '') {
            $kelasId = $request->kelas_id;
            $query->whereHas('siswa', function ($q) use ($kelasId) {
                $q->where('kelas_id', $kelasId);
            });
        }

        if ($request->has('sesi_id') && $request->sesi_id != '') {
            $query->where('sesi_ruangan_id', $request->sesi_id);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('lulus') && $request->lulus != '') {
            $query->where('lulus', $request->lulus == 'yes');
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Get format (default to xlsx)
        $format = strtolower($request->input('format', 'xlsx'));

        // Generate filename with current datetime
        $dateStr = now()->format('Ymd_His');
        $filename = "hasil_ujian_{$dateStr}";

        // Export based on requested format
        switch ($format) {
            case 'csv':
                return Excel::download(
                    new \App\Exports\HasilUjianExport($query),
                    $filename . '.csv',
                    \Maatwebsite\Excel\Excel::CSV
                );

            case 'pdf':
                // Fallback to XLSX if PDF export fails
                try {
                    return Excel::download(
                        new \App\Exports\HasilUjianPdfExport($query),
                        $filename . '.pdf',
                        \Maatwebsite\Excel\Excel::DOMPDF
                    );
                } catch (\Exception $e) {
                    // Log the error
                    \Illuminate\Support\Facades\Log::error('PDF export failed: ' . $e->getMessage());

                    // Fallback to simple Excel export
                    return Excel::download(
                        new \App\Exports\HasilUjianSimpleExport($query),
                        $filename . '.xlsx'
                    );
                }

            default: // xlsx
                return Excel::download(
                    new \App\Exports\HasilUjianExport($query),
                    $filename . '.xlsx',
                    \Maatwebsite\Excel\Excel::XLSX
                );
        }
    }

    /**
     * Export a single result to PDF.
     */
    public function exportSingle(Request $request, HasilUjian $hasil)
    {
        // Load necessary relationships
        $hasil->load(['jadwalUjian.mapel', 'sesiRuangan.ruangan', 'siswa.kelas']);

        // Get format (default to pdf)
        $format = strtolower($request->input('format', 'pdf'));

        // Generate filename
        $dateStr = now()->format('Ymd_His');
        $idyayasan = $hasil->siswa->idyayasan ?? 'unknown';
        $filename = "hasil_ujian_{$idyayasan}_{$dateStr}";

        try {
            return Excel::download(
                new \App\Exports\SingleHasilUjianExport($hasil),
                $filename . '.pdf',
                \Maatwebsite\Excel\Excel::DOMPDF
            );
        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('PDF export failed: ' . $e->getMessage());

            // Create a simple Excel representation of this result
            return response()->view('exports.hasil-ujian-single-text', [
                'hasil' => $hasil
            ])->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}.txt\"");
        }
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
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas']);

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
        $avgNilai = $totalHasil > 0 ? $hasilUjians->avg('nilai') : 0;
        $maxNilai = $totalHasil > 0 ? $hasilUjians->max('nilai') : 0;
        $minNilai = $totalHasil > 0 ? $hasilUjians->min('nilai') : 0;

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
            $nilai = $hasil->nilai;

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
                        'rata_rata' => $items->avg('nilai'),
                        'lulus' => $items->where('lulus', true)->count(),
                        'tidak_lulus' => $items->where('lulus', false)->count(),
                    ];
                })
                ->sortByDesc('rata_rata')
                ->values();
        }

        // Get filters for the view
        $jadwalUjians = JadwalUjian::orderBy('tanggal', 'desc')->get();
        $kelasList = \App\Models\Kelas::orderBy('nama_kelas', 'asc')->get();

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
