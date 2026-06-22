<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\SoalUjian;
use App\Models\PaketUjian;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use App\Support\SoalAnswerEvaluator;
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
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjians = $this->paketUjianOptions($tahunAjaranId);
        $paketUjianId = $this->selectedPaketUjianId($request, $paketUjians);
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas']);

        if ($tahunAjaranId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        if ($paketUjianId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('paket_ujian_id', $paketUjianId));
        }

        // Filter by jadwal ujian
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        // Filter by kelas
        $this->applySiswaTahunFilters($query, $request, $tahunAjaranId);

        // Filter by sesi
        if ($request->has('sesi_id') && $request->sesi_id != '') {
            $query->where('sesi_ujian_id', $request->sesi_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $this->applyStatusFilter($query, $request->status);
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
        $completedHasil = $statsQuery->whereIn('status', $this->completedStatuses())->count();

        // Reset where clauses that were added by the count() calls
        $statsQuery = clone $query;

        // Get pass/fail statistics
        $passedCount = $statsQuery->whereIn('status', $this->completedStatuses())
            ->where('lulus', true)
            ->count();

        // Calculate average score from completed tests only
        $statsQuery = clone $query;
        $averageScoreResult = $statsQuery->whereIn('status', $this->completedStatuses())
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
        $jadwalUjians = JadwalUjian::forTahunAjaran($tahunAjaranId)
            ->when($paketUjianId, fn($q) => $q->where('paket_ujian_id', $paketUjianId))
            ->orderBy('tanggal', 'desc')
            ->get();
        $sesiRuangans = SesiRuangan::forTahunAjaran($tahunAjaranId)->orderBy('nama_sesi')->get();
        $kelasList = \App\Models\Kelas::forTahunAjaran($tahunAjaranId)->orderBy('nama_kelas', 'asc')->get();
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

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
            'latestHasil',
            'tahunAjarans',
            'tahunAjaranId',
            'paketUjians',
            'paketUjianId'
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
            'sesiRuangan.ruangan',
            'siswa.kelas',
            'siswa.tahunAjaranRecords.kelas',
            'jawabanSiswas.soalUjian',
            'pelanggaranUjian',
            'enrollment'
        ]);
        $this->hydrateResultStudentClass($hasil);

        // Variabel tambahan untuk view
        $mapel = $hasil->jadwalUjian->mapel;

        // Analisis kategori jawaban (misal ada method di model HasilUjian)
        $kategoriAnalisis = $hasil->getKategoriAnalisis() ?? [];

        // Ambil hasil ujian lain dari siswa yang sama
        $otherResults = HasilUjian::where('siswa_id', $hasil->siswa_id)
            ->where('id', '!=', $hasil->id)
            ->with(['jadwalUjian.mapel'])
            ->latest()
            ->limit(8)
            ->get();
        $answerRows = $this->buildAnswerRows($hasil);
        $jawabanStats = $this->summarizeAnswerRows($answerRows);
        $peerStats = $this->buildPeerStats($hasil);
        $timeline = $this->buildResultTimeline($hasil);
        $weakCategories = collect($kategoriAnalisis)
            ->sortBy('persentase')
            ->take(3)
            ->all();

        return view('features.naskah.hasil.show', compact(
            'hasil',
            'mapel',
            'kategoriAnalisis',
            'otherResults',
            'answerRows',
            'jawabanStats',
            'peerStats',
            'timeline',
            'weakCategories'
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
        $selesai = $hasilUjians->whereIn('status', $this->completedStatuses())->count();
        $belumMulai = $hasilUjians->where('status', 'belum_mulai')->count();
        $sedangUjian = $hasilUjians->where('status', 'sedang_ujian')->count();

        $lulus = $hasilUjians->where('lulus', true)->count();
        $tidakLulus = $hasilUjians->whereIn('status', $this->completedStatuses())->where('lulus', false)->count();

        $rataRataNilai = $hasilUjians->whereIn('status', $this->completedStatuses())->avg('nilai');
        $nilaiTertinggi = $hasilUjians->whereIn('status', $this->completedStatuses())->max('nilai');
        $nilaiTerendah = $hasilUjians->whereIn('status', $this->completedStatuses())->min('nilai');

        // Group by grade
        $grades = $hasilUjians->whereIn('status', $this->completedStatuses())
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
        $selesai = $hasilUjians->whereIn('status', $this->completedStatuses())->count();
        $belumMulai = $hasilUjians->where('status', 'belum_mulai')->count();
        $sedangUjian = $hasilUjians->where('status', 'sedang_ujian')->count();

        $lulus = $hasilUjians->where('lulus', true)->count();
        $tidakLulus = $hasilUjians->whereIn('status', $this->completedStatuses())->where('lulus', false)->count();

        $rataRataNilai = $hasilUjians->whereIn('status', $this->completedStatuses())->avg('nilai');
        $nilaiTertinggi = $hasilUjians->whereIn('status', $this->completedStatuses())->max('nilai');
        $nilaiTerendah = $hasilUjians->whereIn('status', $this->completedStatuses())->min('nilai');

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
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjians = $this->paketUjianOptions($tahunAjaranId);
        $paketUjianId = $this->selectedPaketUjianId($request, $paketUjians);
        $query = HasilUjian::query();

        if ($tahunAjaranId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        if ($paketUjianId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('paket_ujian_id', $paketUjianId));
        }

        // Apply filters just like in the index method
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        // Filter by kelas
        $this->applySiswaTahunFilters($query, $request, $tahunAjaranId);

        // Filter by sesi
        if ($request->has('sesi_id') && $request->sesi_id != '') {
            $query->where('sesi_ujian_id', $request->sesi_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $this->applyStatusFilter($query, $request->status);
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
    // public function export(Request $request)
    // {
    //     $query = HasilUjian::query();

    //     // Apply filters just like in the index method
    //     if ($request->has('jadwal_id') && $request->jadwal_id != '') {
    //         $query->where('jadwal_ujian_id', $request->jadwal_id);
    //     }

    //     if ($request->has('kelas_id') && $request->kelas_id != '') {
    //         $kelasId = $request->kelas_id;
    //         $query->whereHas('siswa', function ($q) use ($kelasId) {
    //             $q->where('kelas_id', $kelasId);
    //         });
    //     }

    //     if ($request->has('sesi_id') && $request->sesi_id != '') {
    //         $query->where('sesi_ruangan_id', $request->sesi_id);
    //     }

    //     if ($request->has('status') && $request->status != '') {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->has('lulus') && $request->lulus != '') {
    //         $query->where('lulus', $request->lulus == 'yes');
    //     }

    //     if ($request->has('search') && $request->search != '') {
    //         $search = $request->search;
    //         $query->whereHas('siswa', function ($q) use ($search) {
    //             $q->where('nama', 'like', "%{$search}%")
    //                 ->orWhere('nis', 'like', "%{$search}%");
    //         });
    //     }

    //     // Get format (default to xlsx)
    //     $format = strtolower($request->input('format', 'xlsx'));

    //     // Generate filename with current datetime
    //     $dateStr = now()->format('Ymd_His');
    //     $filename = "hasil_ujian_{$dateStr}";

    //     // Export based on requested format
    //     switch ($format) {
    //         case 'csv':
    //             return Excel::download(
    //                 new \App\Exports\HasilUjianExport($query),
    //                 $filename . '.csv',
    //                 \Maatwebsite\Excel\Excel::CSV
    //             );

    //         case 'pdf':
    //             // Fallback to XLSX if PDF export fails
    //             try {
    //                 return Excel::download(
    //                     new \App\Exports\HasilUjianPdfExport($query),
    //                     $filename . '.pdf',
    //                     \Maatwebsite\Excel\Excel::DOMPDF
    //                 );
    //             } catch (\Exception $e) {
    //                 // Log the error
    //                 \Illuminate\Support\Facades\Log::error('PDF export failed: ' . $e->getMessage());

    //                 // Fallback to simple Excel export
    //                 return Excel::download(
    //                     new \App\Exports\HasilUjianSimpleExport($query),
    //                     $filename . '.xlsx'
    //                 );
    //             }

    //         default: // xlsx
    //             return Excel::download(
    //                 new \App\Exports\HasilUjianExport($query),
    //                 $filename . '.xlsx',
    //                 \Maatwebsite\Excel\Excel::XLSX
    //             );
    //     }
    // }

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
     * Delete a hasil ujian and reset enrollment.
     */
    public function destroy(HasilUjian $hasil)
    {
        DB::beginTransaction();

        try {
            // Load necessary relationships
            $hasil->load(['enrollment', 'pelanggaranUjian', 'jawabanSiswas']);

            $siswaName = $hasil->siswa->nama ?? 'Unknown';
            $jadwalTitle = $hasil->jadwalUjian->judul ?? 'Unknown';

            // Delete related pelanggaran_ujian records
            $hasil->pelanggaranUjian()->delete();

            // Delete related jawaban_siswa records
            $hasil->jawabanSiswas()->delete();

            // Reset enrollment status and clear exam timing fields
            if ($hasil->enrollment) {
                $hasil->enrollment->update([
                    'status_enrollment' => 'enrolled',
                    'waktu_mulai_ujian' => null,
                    'waktu_selesai_ujian' => null,
                    'catatan' => null,
                ]);
            }

            // Delete hasil ujian record
            $hasil->delete();

            DB::commit();

            // Log for audit trail
            \Log::info('Hasil ujian deleted', [
                'siswa_name' => $siswaName,
                'jadwal_title' => $jadwalTitle,
                'deleted_by' => auth()->user()->name ?? 'System',
                'deleted_at' => now(),
            ]);

            return redirect()->route('naskah.hasil.index')
                ->with('success', 'Hasil ujian berhasil dihapus dan enrollment direset ke status "enrolled".');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error deleting hasil ujian: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus hasil ujian: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed analysis of results.
     */
    public function analisis(Request $request)
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjians = $this->paketUjianOptions($tahunAjaranId);
        $paketUjianId = $this->selectedPaketUjianId($request, $paketUjians);
        $query = HasilUjian::with(['jadwalUjian.mapel', 'sesiRuangan', 'siswa.kelas', 'jawabanSiswas.soalUjian']);

        if ($tahunAjaranId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId));
        }

        if ($paketUjianId) {
            $query->whereHas('jadwalUjian', fn($q) => $q->where('paket_ujian_id', $paketUjianId));
        }

        // Apply filters similar to index method
        if ($request->has('jadwal_id') && $request->jadwal_id != '') {
            $query->where('jadwal_ujian_id', $request->jadwal_id);
        }

        $this->applySiswaTahunFilters($query, $request, $tahunAjaranId);

        // Only consider completed tests for analysis
        $query->whereIn('status', $this->completedStatuses());

        $hasilUjians = $query->get();

        // Basic statistics
        $totalHasil = $hasilUjians->count();
        $avgNilai = $totalHasil > 0 ? $hasilUjians->avg('nilai') : 0;
        $maxNilai = $totalHasil > 0 ? $hasilUjians->max('nilai') : 0;
        $minNilai = $totalHasil > 0 ? $hasilUjians->min('nilai') : 0;
        $medianNilai = $totalHasil > 0 ? $hasilUjians->sortBy('nilai')->values()->get((int) floor(($totalHasil - 1) / 2))->nilai : 0;
        $passCount = $hasilUjians->where('lulus', true)->count();
        $passRate = $totalHasil > 0 ? round(($passCount / $totalHasil) * 100, 1) : 0;
        $avgDurasi = round($hasilUjians->avg('durasi_menit') ?? 0, 1);

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
                    return $item->siswa->kelas->nama_kelas ?? 'Tanpa Kelas';
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

        $questionAnalysis = $this->buildQuestionAnalysis($hasilUjians);
        $categoryAnalysis = $this->buildCategoryAnalysis($questionAnalysis);
        $topStudents = $hasilUjians->sortByDesc('nilai')->take(10)->values();
        $bottomStudents = $hasilUjians->sortBy('nilai')->take(10)->values();
        $jadwalComparison = $hasilUjians
            ->groupBy(fn($hasil) => $hasil->jadwalUjian->judul ?? 'Tanpa Jadwal')
            ->map(fn($items, $jadwal) => [
                'jadwal' => $jadwal,
                'jumlah' => $items->count(),
                'rata_rata' => round($items->avg('nilai'), 2),
                'lulus' => $items->where('lulus', true)->count(),
            ])
            ->sortByDesc('rata_rata')
            ->values();
        $tingkatComparison = $hasilUjians
            ->groupBy(fn($hasil) => $hasil->siswa->kelas->tingkat ?? '-')
            ->map(fn($items, $tingkat) => [
                'tingkat' => $tingkat,
                'jumlah' => $items->count(),
                'rata_rata' => round($items->avg('nilai'), 2),
            ])
            ->values();
        $jurusanComparison = $hasilUjians
            ->groupBy(fn($hasil) => $hasil->siswa->kelas->jurusan ?? '-')
            ->map(fn($items, $jurusan) => [
                'jurusan' => $jurusan,
                'jumlah' => $items->count(),
                'rata_rata' => round($items->avg('nilai'), 2),
            ])
            ->sortByDesc('rata_rata')
            ->values();

        // Get filters for the view
        $jadwalUjians = JadwalUjian::forTahunAjaran($tahunAjaranId)
            ->when($paketUjianId, fn($q) => $q->where('paket_ujian_id', $paketUjianId))
            ->orderBy('tanggal', 'desc')
            ->get();
        $kelasQuery = \App\Models\Kelas::forTahunAjaran($tahunAjaranId);
        $kelasList = (clone $kelasQuery)->orderBy('nama_kelas', 'asc')->get();
        $tingkatList = (clone $kelasQuery)->select('tingkat')->distinct()->whereNotNull('tingkat')->orderBy('tingkat')->pluck('tingkat');
        $jurusanList = (clone $kelasQuery)->select('jurusan')->distinct()->whereNotNull('jurusan')->orderBy('jurusan')->pluck('jurusan');
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        return view('features.naskah.hasil.analisis', compact(
            'hasilUjians',
            'totalHasil',
            'avgNilai',
            'maxNilai',
            'minNilai',
            'medianNilai',
            'passCount',
            'passRate',
            'avgDurasi',
            'scoreRanges',
            'kelasPerfomance',
            'questionAnalysis',
            'categoryAnalysis',
            'topStudents',
            'bottomStudents',
            'jadwalComparison',
            'tingkatComparison',
            'jurusanComparison',
            'jadwalUjians',
            'kelasList',
            'tingkatList',
            'jurusanList',
            'tahunAjarans',
            'tahunAjaranId',
            'paketUjians',
            'paketUjianId'
        ));
    }

    public function jawaban(HasilUjian $hasil)
    {
        $hasil->load(['jadwalUjian.mapel', 'siswa.kelas', 'jawabanSiswas.soalUjian']);
        $hasilUjian = $hasil;
        $mapel = $hasil->jadwalUjian->mapel;
        $jawaban = $this->buildAnswerRows($hasil);

        return view('features.naskah.hasil.jawaban', compact('hasilUjian', 'mapel', 'jawaban'));
    }

    public function print(Request $request, HasilUjian $hasil)
    {
        $hasil->load(['jadwalUjian.mapel', 'jadwalUjian.bankSoal', 'sesiRuangan.ruangan', 'siswa.kelas', 'jawabanSiswas.soalUjian']);
        $answerRows = $request->boolean('with_answers') ? $this->buildAnswerRows($hasil) : [];

        return view('features.naskah.hasil.print', compact('hasil', 'answerRows'));
    }

    private function buildAnswerRows(HasilUjian $hasil): array
    {
        $rows = [];
        $jawabanRecords = $hasil->relationLoaded('jawabanSiswas')
            ? $hasil->jawabanSiswas
            : $hasil->jawabanSiswas()->with('soalUjian')->get();

        if ($jawabanRecords->isNotEmpty()) {
            foreach ($jawabanRecords as $index => $jawaban) {
                $soal = $jawaban->soalUjian;
                if (!$soal) {
                    continue;
                }

                $jawabanRaw = (string) ($jawaban->jawaban ?? '');
                $kunciRaw = (string) ($soal->kunci_jawaban ?? '');
                $evaluation = SoalAnswerEvaluator::evaluate($soal, $jawabanRaw, $kunciRaw);
                $isCorrect = $evaluation['is_correct'];
                $status = $evaluation['status'];

                $rows[] = [
                    'soal_id' => $soal->id,
                    'nomor' => $soal->nomor_soal ?? $soal->urutan ?? $index + 1,
                    'pertanyaan' => html_entity_decode($soal->pertanyaan ?? $soal->soal ?? '-'),
                    'text' => strip_tags($soal->pertanyaan ?? $soal->soal ?? '-'),
                    'pilihan' => $this->getQuestionOptions($soal),
                    'jawaban' => $this->formatAnswerValue($soal, $jawabanRaw),
                    'kunci' => $this->formatAnswerValue($soal, $kunciRaw, true),
                    'jawaban_raw' => $jawabanRaw,
                    'kunci_raw' => $kunciRaw,
                    'jawaban_options' => $this->answerLetters($jawabanRaw),
                    'kunci_options' => $this->answerLetters($kunciRaw),
                    'score_fraction' => $evaluation['score_fraction'] ?? 0,
                    'evaluation_details' => $evaluation['details'] ?? [],
                    'evaluation_summary' => $this->formatEvaluationDetails($evaluation['details'] ?? []),
                    'status' => $status,
                    'is_correct' => $isCorrect,
                    'kategori' => $soal->kategori ?? $soal->tingkat_kesulitan ?? 'Umum',
                    'category' => $soal->kategori ?? $soal->tingkat_kesulitan ?? 'Umum',
                    'pembahasan' => html_entity_decode($soal->pembahasan_teks ?? ''),
                    'waktu_jawab' => optional($jawaban->waktu_jawab)->format('H:i:s'),
                ];
            }

            return $rows;
        }

        foreach (($hasil->jawaban ?? []) as $index => $item) {
            $soalId = $item['soal_id'] ?? $item['id'] ?? null;
            $soal = $soalId ? SoalUjian::find($soalId) : null;
            $jawabanRaw = (string) ($item['jawaban'] ?? '');
            $kunciRaw = (string) ($item['kunci'] ?? $soal?->kunci_jawaban ?? '');
            $evaluation = $soal ? SoalAnswerEvaluator::evaluate($soal, $jawabanRaw, $kunciRaw) : [
                'status' => $jawabanRaw === '' ? 'kosong' : ((bool) ($item['is_correct'] ?? false) ? 'benar' : 'salah'),
                'is_correct' => (bool) ($item['is_correct'] ?? false),
                'score_fraction' => (bool) ($item['is_correct'] ?? false) ? 1 : 0,
                'details' => [],
            ];
            $isCorrect = $evaluation['is_correct'];

            $rows[] = [
                'soal_id' => $soalId,
                'nomor' => $soal?->nomor_soal ?? $soal?->urutan ?? $index + 1,
                'pertanyaan' => html_entity_decode($soal?->pertanyaan ?? $soal?->soal ?? 'Soal #' . ($index + 1)),
                'text' => strip_tags($soal?->pertanyaan ?? $soal?->soal ?? 'Soal #' . ($index + 1)),
                'pilihan' => $soal ? $this->getQuestionOptions($soal) : [],
                'jawaban' => $soal ? $this->formatAnswerValue($soal, $jawabanRaw) : strtoupper($jawabanRaw),
                'kunci' => $soal ? $this->formatAnswerValue($soal, $kunciRaw, true) : strtoupper($kunciRaw),
                'jawaban_raw' => $jawabanRaw,
                'kunci_raw' => $kunciRaw,
                'jawaban_options' => $this->answerLetters($jawabanRaw),
                'kunci_options' => $this->answerLetters($kunciRaw),
                'score_fraction' => $evaluation['score_fraction'] ?? 0,
                'evaluation_details' => $evaluation['details'] ?? [],
                'evaluation_summary' => $this->formatEvaluationDetails($evaluation['details'] ?? []),
                'status' => $evaluation['status'],
                'is_correct' => (bool) $isCorrect,
                'kategori' => $item['kategori'] ?? $soal?->kategori ?? 'Umum',
                'category' => $item['kategori'] ?? $soal?->kategori ?? 'Umum',
                'pembahasan' => html_entity_decode($soal?->pembahasan_teks ?? ''),
                'waktu_jawab' => $item['waktu_jawab'] ?? null,
            ];
        }

        return $rows;
    }

    private function getQuestionOptions($soal): array
    {
        return collect(['A', 'B', 'C', 'D', 'E'])
            ->mapWithKeys(function ($key) use ($soal) {
                $lower = strtolower($key);
                $value = $soal->{"pilihan_{$lower}_teks"} ?? $soal->{"opsi_{$lower}"} ?? null;
                return trim((string) $value) !== '' ? [$key => html_entity_decode($value)] : [];
            })
            ->all();
    }

    private function formatAnswerValue($soal, ?string $value, bool $isKey = false): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $tipe = $soal->tipe_soal ?? 'pilihan_ganda';

        if ($tipe === 'menjodohkan') {
            $decoded = json_decode($value, true);
            $pairs = $isKey ? data_get($decoded, 'data.pairs', []) : $decoded;

            if ($isKey) {
                return collect($pairs)
                    ->map(fn($pair) => ($pair['left'] ?? '') . ' = ' . ($pair['right'] ?? ''))
                    ->filter(fn($line) => trim(str_replace('=', '', $line)) !== '')
                    ->implode('; ');
            }

            return collect($pairs ?? [])
                ->map(fn($right, $left) => $left . ' = ' . $right)
                ->implode('; ');
        }

        if ($tipe === 'mengurutkan') {
            $decoded = json_decode($value, true);
            $items = $isKey ? data_get($decoded, 'data.items', []) : $decoded;

            return collect($items ?? [])
                ->filter()
                ->values()
                ->map(fn($item, $index) => ($index + 1) . '. ' . $item)
                ->implode('; ');
        }

        if ($tipe === 'teks_rumpang') {
            $decoded = json_decode($value, true);
            $items = $isKey ? data_get($decoded, 'data.answers', []) : $decoded;

            if (is_array($items)) {
                return collect($items)
                    ->filter()
                    ->values()
                    ->map(fn($item, $index) => 'Rumpang ' . ($index + 1) . ': ' . (is_array($item) ? implode('|', $item) : $item))
                    ->implode('; ');
            }

            return $value;
        }

        if ($tipe === 'drag_drop') {
            $decoded = json_decode($value, true);

            if ($isKey) {
                $items = data_get($decoded, 'data.items', []);
                $zones = data_get($decoded, 'data.zones', []);

                return collect($items)
                    ->map(fn($item, $index) => $item . ' -> ' . ($zones[$index] ?? '-'))
                    ->implode('; ');
            }

            return collect($decoded ?? [])
                ->map(fn($zone, $item) => $item . ' -> ' . $zone)
                ->implode('; ');
        }

        return in_array($tipe, ['isian_singkat', 'teks_rumpang'], true) ? $value : strtoupper($value);
    }

    private function answerLetters(?string $value): array
    {
        return collect(explode(',', strtoupper((string) $value)))
            ->map(fn($letter) => trim($letter))
            ->filter(fn($letter) => preg_match('/^[A-E]$/', $letter))
            ->values()
            ->all();
    }

    private function formatEvaluationDetails(array $details): array
    {
        if (isset($details['correct_selected']) || isset($details['wrong_selected']) || isset($details['missing'])) {
            return array_filter([
                'Dipilih benar: ' . implode(', ', $details['correct_selected'] ?? []),
                'Dipilih salah: ' . implode(', ', $details['wrong_selected'] ?? []),
                'Belum dipilih: ' . implode(', ', $details['missing'] ?? []),
            ], fn($line) => !str_ends_with($line, ': '));
        }

        if (isset($details['pairs'])) {
            return collect($details['pairs'])
                ->map(fn($pair) => ($pair['left'] ?? '-') . ' -> ' . ($pair['actualRight'] ?? '-') . (($pair['isCorrect'] ?? false) ? ' (benar)' : ' (salah, seharusnya ' . ($pair['right'] ?? '-') . ')'))
                ->all();
        }

        if (isset($details['items'])) {
            return collect($details['items'])
                ->map(function ($item) {
                    if (array_key_exists('actualValue', $item)) {
                        return 'Posisi ' . (($item['index'] ?? 0) + 1) . ': ' . ($item['actualValue'] ?? '-') . (($item['isCorrect'] ?? false) ? ' (benar)' : ' (salah, seharusnya ' . ($item['item'] ?? '-') . ')');
                    }

                    if (array_key_exists('actualZone', $item)) {
                        return ($item['item'] ?? '-') . ' -> ' . ($item['actualZone'] ?? '-') . (($item['isCorrect'] ?? false) ? ' (benar)' : ' (salah, seharusnya ' . ($item['zone'] ?? '-') . ')');
                    }

                    if (array_key_exists('expected', $item)) {
                        return 'Rumpang ' . (($item['index'] ?? 0) + 1) . ': ' . ($item['actual'] ?? '-') . (($item['is_correct'] ?? false) ? ' (benar)' : ' (salah)');
                    }

                    return null;
                })
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    private function summarizeAnswerRows(array $rows): array
    {
        $total = count($rows);
        $benar = collect($rows)->where('status', 'benar')->count();
        $parsial = collect($rows)->where('status', 'parsial')->count();
        $salah = collect($rows)->where('status', 'salah')->count();
        $kosong = collect($rows)->where('status', 'kosong')->count();

        return compact('total', 'benar', 'parsial', 'salah', 'kosong');
    }

    private function buildPeerStats(HasilUjian $hasil): array
    {
        $sameJadwal = HasilUjian::where('jadwal_ujian_id', $hasil->jadwal_ujian_id)
            ->whereIn('status', $this->completedStatuses())
            ->get();
        $kelas = $this->kelasForResult($hasil);
        $sameKelas = HasilUjian::where('jadwal_ujian_id', $hasil->jadwal_ujian_id)
            ->whereIn('status', $this->completedStatuses())
            ->when($kelas, function ($query) use ($hasil, $kelas) {
                $query->whereHas('siswa.tahunAjaranRecords', function ($q) use ($hasil, $kelas) {
                    $q->where('tahun_ajaran_id', $hasil->jadwalUjian?->tahun_ajaran_id)
                        ->where('kelas_id', $kelas->id);
                });
            }, fn($query) => $query->whereRaw('1 = 0'))
            ->get();

        $rank = $sameJadwal->sortByDesc('nilai')->values()->search(fn($item) => $item->id === $hasil->id);

        return [
            'avg_jadwal' => round($sameJadwal->avg('nilai') ?? 0, 2),
            'avg_kelas' => round($sameKelas->avg('nilai') ?? 0, 2),
            'rank' => $rank === false ? null : $rank + 1,
            'total' => $sameJadwal->count(),
        ];
    }

    private function kelasForResult(HasilUjian $hasil)
    {
        $tahunAjaranId = $hasil->jadwalUjian?->tahun_ajaran_id;

        if (!$hasil->siswa || !$tahunAjaranId) {
            return $hasil->siswa?->kelas;
        }

        return $hasil->siswa->kelasForTahunAjaran($tahunAjaranId) ?: $hasil->siswa->kelas;
    }

    private function hydrateResultStudentClass(HasilUjian $hasil): void
    {
        if ($hasil->siswa) {
            $hasil->siswa->setRelation('kelas', $this->kelasForResult($hasil));
        }
    }

    private function buildResultTimeline(HasilUjian $hasil): array
    {
        return [
            ['label' => 'Mulai ujian', 'time' => optional($hasil->waktu_mulai ?? $hasil->created_at)->format('d/m/Y H:i')],
            ['label' => 'Selesai ujian', 'time' => optional($hasil->waktu_selesai)->format('d/m/Y H:i') ?? '-'],
            ['label' => 'Durasi', 'time' => $hasil->durasi_menit ? $hasil->durasi_menit . ' menit' : $hasil->getDurationFormatted()],
        ];
    }

    private function buildQuestionAnalysis($hasilUjians)
    {
        $questionStats = [];
        foreach ($hasilUjians as $hasil) {
            foreach ($this->buildAnswerRows($hasil) as $row) {
                $key = $row['soal_id'] ?? $row['nomor'];
                if (!isset($questionStats[$key])) {
                    $questionStats[$key] = [
                        'nomor' => $row['nomor'],
                        'text' => $row['text'],
                        'category' => $row['category'],
                        'correct' => 0,
                        'partial' => 0,
                        'incorrect' => 0,
                        'blank' => 0,
                        'score_sum' => 0,
                        'total' => 0,
                    ];
                }

                $questionStats[$key]['total']++;
                $questionStats[$key]['score_sum'] += (float) ($row['score_fraction'] ?? ($row['is_correct'] ? 1 : 0));
                if ($row['status'] === 'benar') {
                    $questionStats[$key]['correct']++;
                } elseif ($row['status'] === 'parsial') {
                    $questionStats[$key]['partial']++;
                } elseif ($row['status'] === 'salah') {
                    $questionStats[$key]['incorrect']++;
                } else {
                    $questionStats[$key]['blank']++;
                }
            }
        }

        return collect($questionStats)->map(function ($item) {
            $item['accuracy'] = $item['total'] > 0 ? round(($item['score_sum'] / $item['total']) * 100, 1) : 0;
            $item['difficulty_label'] = $item['accuracy'] >= 75 ? 'Mudah' : ($item['accuracy'] >= 45 ? 'Sedang' : 'Sulit');
            return $item;
        })->sortBy('nomor')->values();
    }

    private function buildCategoryAnalysis($questionAnalysis)
    {
        return collect($questionAnalysis)
            ->groupBy('category')
            ->map(fn($items, $category) => [
                'category' => $category,
                'accuracy' => round($items->avg('accuracy'), 1),
                'questions' => $items->count(),
            ])
            ->sortBy('accuracy')
            ->values();
    }

    private function applySiswaTahunFilters($query, Request $request, $tahunAjaranId): void
    {
        if (!$request->filled('kelas_id') && !$request->filled('tingkat') && !$request->filled('jurusan')) {
            return;
        }

        $query->whereHas('siswa.tahunAjaranRecords', function ($q) use ($request, $tahunAjaranId) {
            if ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            }

            if ($request->filled('kelas_id')) {
                $q->where('kelas_id', $request->kelas_id);
            }

            if ($request->filled('tingkat')) {
                $q->whereHas('kelas', fn($kelas) => $kelas->where('tingkat', $request->tingkat));
            }

            if ($request->filled('jurusan')) {
                $q->whereHas('kelas', fn($kelas) => $kelas->where('jurusan', $request->jurusan));
            }
        });
    }

    private function paketUjianOptions($tahunAjaranId)
    {
        return PaketUjian::when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByRaw("CASE WHEN status = 'aktif' THEN 0 ELSE 1 END")
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->get();
    }

    private function selectedPaketUjianId(Request $request, $paketUjians): ?int
    {
        if ($request->has('paket_ujian_id')) {
            $paketUjianId = $request->input('paket_ujian_id');

            if ($paketUjianId === '') {
                return null;
            }

            return $paketUjians->contains('id', (int) $paketUjianId)
                ? (int) $paketUjianId
                : ($paketUjians->firstWhere('status', 'aktif')?->id ?? $paketUjians->first()?->id);
        }

        return $paketUjians->firstWhere('status', 'aktif')?->id
            ?? $paketUjians->first()?->id;
    }

    private function completedStatuses(): array
    {
        return ['selesai', 'auto-selesai'];
    }

    private function applyStatusFilter($query, string $status): void
    {
        if ($status === 'selesai') {
            $query->whereIn('status', $this->completedStatuses());
            return;
        }

        $query->where('status', $status);
    }
}
