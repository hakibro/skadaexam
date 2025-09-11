<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\BeritaAcaraUjian;
use App\Models\SesiRuangan;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LaporanController extends Controller
{
    /**
     * Display berita acara management
     */
    public function index(Request $request)
    {
        $query = BeritaAcaraUjian::with(['sesiRuangan.ruangan', 'pengawas']);

        // Filter by finalization status
        if ($request->filled('status')) {
            if ($request->status === 'finalized') {
                $query->where('is_final', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_final', false);
            }
        }

        // Filter by pelaksanaan status
        if ($request->filled('pelaksanaan_status')) {
            $query->where('status_pelaksanaan', $request->pelaksanaan_status);
        }

        // Filter by date range (using created_at since tanggal doesn't exist)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by pengawas
        if ($request->filled('pengawas_id')) {
            $query->where('pengawas_id', $request->pengawas_id);
        }

        // Search by ruangan or session name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('sesiRuangan', function ($q) use ($search) {
                $q->whereHas('ruangan', function ($rq) use ($search) {
                    $rq->where('nama_ruangan', 'like', "%{$search}%")
                        ->orWhere('kode_ruangan', 'like', "%{$search}%");
                });
            });
        }

        $beritaAcaras = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get available pengawas for filter
        $pengawasList = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', 'pengawas');
            });
        })->orderBy('nama')->get();

        // Get statistics
        $stats = [
            // Map to expected view keys
            'pending' => BeritaAcaraUjian::where('is_final', false)->count(),
            'verified' => BeritaAcaraUjian::where('is_final', true)->count(),
            'rejected' => 0, // No rejected status in current model
            'total' => BeritaAcaraUjian::count(),

            // Keep existing stats for backward compatibility
            'draft' => BeritaAcaraUjian::where('is_final', false)->count(),
            'finalized' => BeritaAcaraUjian::where('is_final', true)->count(),
            'today' => BeritaAcaraUjian::whereDate('created_at', Carbon::today())->count(),
        ];

        return view('features.koordinator.laporan.index', compact(
            'beritaAcaras',
            'pengawasList',
            'stats'
        ));
    }

    /**
     * Display specific berita acara
     */
    public function show(BeritaAcaraUjian $laporan)
    {
        $laporan->load([
            'sesiRuangan.ruangan',
            'sesiRuangan.jadwalUjian.mapel',
            'sesiRuangan.jadwalUjian.kelas',
            'sesiRuangan.sesiRuanganSiswa.siswa',
            'pengawas'
        ]);

        // Check if sesiRuangan exists
        if (!$laporan->sesiRuangan) {
            abort(404, 'Data sesi ruangan tidak ditemukan untuk berita acara ini.');
        }

        // Pass the berita acara as 'beritaAcara' to match the view
        return view('features.koordinator.laporan.show', ['beritaAcara' => $laporan]);
    }

    /**
     * Download berita acara as PDF
     */
    public function download(BeritaAcaraUjian $laporan)
    {
        $laporan->load(['sesiRuangan.ruangan', 'pengawas']);

        // For now, return a simple response
        // In production, you would generate a proper PDF
        return response()->json([
            'message' => 'PDF download akan diimplementasikan dengan package PDF generator',
            'laporan' => [
                'id' => $laporan->id,
                'sesi' => $laporan->sesiRuangan->nama_sesi,
                'ruangan' => $laporan->sesiRuangan->ruangan->nama,
                'pengawas' => $laporan->pengawas->nama,
                'tanggal' => $laporan->created_at->format('d/m/Y'),
            ]
        ]);
    }

    /**
     * Show edit form for berita acara
     */
    public function edit(BeritaAcaraUjian $laporan)
    {
        $laporan->load([
            'sesiRuangan.ruangan',
            'sesiRuangan.jadwalUjian.mapel',
            'sesiRuangan.jadwalUjian.kelas',
            'sesiRuangan.sesiRuanganSiswa.siswa',
            'pengawas'
        ]);

        return view('features.koordinator.laporan.edit', ['beritaAcara' => $laporan]);
    }

    /**
     * Update berita acara
     */
    public function update(Request $request, BeritaAcaraUjian $laporan)
    {
        $request->validate([
            'catatan_pembukaan' => 'nullable|string',
            'catatan_pelaksanaan' => 'nullable|string',
            'catatan_penutupan' => 'nullable|string',
            'status_pelaksanaan' => 'required|string',
        ]);

        try {
            $laporan->update($request->only([
                'catatan_pembukaan',
                'catatan_pelaksanaan',
                'catatan_penutupan',
                'status_pelaksanaan'
            ]));

            return redirect()
                ->route('koordinator.laporan.show', $laporan)
                ->with('success', 'Berita acara berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating berita acara: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui berita acara');
        }
    }

    /**
     * Finalize/Review berita acara
     */
    public function verify(Request $request)
    {
        $request->validate([
            'berita_acara_id' => 'required|exists:berita_acara_ujian,id',
            'action' => 'required|in:finalize,reject,verify',
            'catatan' => 'nullable|string|max:1000'
        ]);

        try {
            $laporan = BeritaAcaraUjian::findOrFail($request->berita_acara_id);

            if ($request->action === 'finalize' || $request->action === 'verify') {
                $laporan->finalize();
                $message = 'Berita acara berhasil diverifikasi.';
            } else {
                // For rejection, we could add a note but keep it as draft
                $laporan->update([
                    'catatan_pembukaan' => $request->catatan ?
                        ($laporan->catatan_pembukaan ? $laporan->catatan_pembukaan . "\n\nCatatan Koordinator: " . $request->catatan : 'Catatan Koordinator: ' . $request->catatan) :
                        $laporan->catatan_pembukaan
                ]);
                $message = 'Catatan telah ditambahkan ke berita acara.';
            }

            Log::info('Berita acara reviewed', [
                'berita_acara_id' => $laporan->id,
                'action' => $request->action,
                'koordinator_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Error reviewing berita acara: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses berita acara.'
            ], 500);
        }
    }

    /**
     * Bulk finalize berita acara
     */
    public function bulkVerify(Request $request)
    {
        $request->validate([
            'berita_acara_ids' => 'required|array',
            'berita_acara_ids.*' => 'exists:berita_acara_ujian,id',
            'action' => 'required|in:finalize',
        ]);

        try {
            DB::beginTransaction();

            $beritaAcaras = BeritaAcaraUjian::whereIn('id', $request->berita_acara_ids)
                ->where('is_final', false)
                ->get();

            $processedCount = 0;
            foreach ($beritaAcaras as $laporan) {
                if ($request->action === 'finalize') {
                    $laporan->finalize();
                    $processedCount++;
                }
            }

            DB::commit();

            Log::info('Bulk berita acara finalization', [
                'count' => $processedCount,
                'koordinator_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "{$processedCount} berita acara berhasil difinalisasi"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error bulk finalizing berita acara', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate comprehensive report
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'format' => 'required|in:pdf,excel',
            'include_details' => 'boolean'
        ]);

        try {
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);

            // Get berita acara data
            $beritaAcaras = BeritaAcaraUjian::with(['sesiRuangan.ruangan', 'pengawas'])
                ->whereBetween('tanggal', [$dateFrom, $dateTo])
                ->orderBy('tanggal')
                ->get();

            // Get summary statistics
            $summary = [
                'total_sessions' => $beritaAcaras->count(),
                'verified' => $beritaAcaras->where('status_verifikasi', 'verified')->count(),
                'pending' => $beritaAcaras->where('status_verifikasi', 'pending')->count(),
                'rejected' => $beritaAcaras->where('status_verifikasi', 'rejected')->count(),
                'total_students' => $beritaAcaras->sum('jumlah_peserta_hadir') + $beritaAcaras->sum('jumlah_peserta_tidak_hadir'),
                'attendance_rate' => $beritaAcaras->avg(function ($item) {
                    $total = $item->jumlah_peserta_hadir + $item->jumlah_peserta_tidak_hadir;
                    return $total > 0 ? ($item->jumlah_peserta_hadir / $total) * 100 : 0;
                }),
                'technical_issues' => $beritaAcaras->where('gangguan_teknis', true)->count(),
            ];

            $reportData = [
                'title' => 'Laporan Koordinator Ujian',
                'period' => $dateFrom->format('d/m/Y') . ' - ' . $dateTo->format('d/m/Y'),
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'generated_by' => auth()->user()->name,
                'summary' => $summary,
                'berita_acaras' => $beritaAcaras,
                'include_details' => $request->include_details ?? false
            ];

            if ($request->get('format') === 'pdf') {
                return $this->generatePdfReport($reportData);
            } else {
                return $this->generateExcelReport($reportData);
            }
        } catch (\Exception $e) {
            Log::error('Error generating report', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePdfReport($data)
    {
        // Implementation for PDF generation
        // You would use a package like dompdf or similar

        // For now, return a JSON response indicating feature is not implemented
        return response()->json([
            'message' => 'PDF export will be implemented with a PDF package',
            'data' => $data
        ]);
    }

    public function generateExcelReport($data)
    {
        // Implementation for Excel generation
        // You would use a package like Laravel Excel

        return response()->json([
            'message' => 'Excel export will be implemented with Laravel Excel package',
            'data' => $data
        ]);
    }

    /**
     * Get berita acara statistics
     */
    public function getStatistics(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->subMonth());
        $dateTo = $request->get('date_to', Carbon::now());

        $statistics = BeritaAcaraUjian::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN is_final = 1 THEN 1 ELSE 0 END) as finalized,
                SUM(CASE WHEN is_final = 0 THEN 1 ELSE 0 END) as draft,
                AVG(jumlah_peserta_hadir) as avg_attendance,
                SUM(jumlah_peserta_hadir) as total_present,
                SUM(jumlah_peserta_tidak_hadir) as total_absent
            ')
            ->first();

        // Get daily statistics for chart
        $dailyStats = BeritaAcaraUjian::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('
                DATE(created_at) as date,
                COUNT(*) as count,
                SUM(CASE WHEN is_final = 1 THEN 1 ELSE 0 END) as finalized_count
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'summary' => $statistics,
            'daily_statistics' => $dailyStats,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }
}
