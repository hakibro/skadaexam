<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SesiRuanganSiswa;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Exports\KehadiranExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kelas;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\PaketUjian;
use App\Services\TahunAjaranService;


class KehadiranController extends Controller
{
    public function index(Request $request)
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);
        $paketUjians = $this->paketUjianOptions($tahunAjaranId);
        $paketUjianId = $this->selectedPaketUjianId($request, $paketUjians);

        $query = SesiRuanganSiswa::query()
            ->with([
                'siswa.kelas',
                'siswa.tahunAjaranRecords.kelas',
                'sesiRuangan',
                'sesiRuangan.jadwalUjian',
                'sesiRuangan.ruangan',
            ])
            ->when($tahunAjaranId, fn($q) => $q->whereHas('sesiRuangan', fn($sesi) => $sesi->where('tahun_ajaran_id', $tahunAjaranId)))
            ->when($paketUjianId, fn($q) => $q->whereHas('sesiRuangan.jadwalUjians', fn($jadwal) => $jadwal->where('paket_ujian_id', $paketUjianId)));

        // ================= FILTER =================

        // Status Kehadiran
        if ($request->filled('status')) {
            $query->where('status_kehadiran', $request->status);
        }

        // Range Tanggal (jadwal_ujian.tanggal)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereHas(
                'sesiRuangan.jadwalUjian',
                function ($q) use ($request) {
                    $q->whereBetween('tanggal', [
                        Carbon::parse($request->start_date)->startOfDay(),
                        Carbon::parse($request->end_date)->endOfDay(),
                    ]);
                }
            );
        }

        // Ruangan
        if ($request->filled('ruangan_id')) {
            $query->whereHas('sesiRuangan', function ($q) use ($request) {
                $q->where('ruangan_id', $request->ruangan_id);
            });
        }



        // Tingkat
        if ($request->filled('tingkat')) {
            $query->whereHas('siswa.tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereHas('kelas', fn($kelas) => $kelas->where('tingkat', $request->tingkat)));
        }

        // Jurusan (dari tabel kelas, bukan master jurusan)
        if ($request->filled('jurusan')) {
            $query->whereHas('siswa.tahunAjaranRecords', fn($q) => $q
                ->when($tahunAjaranId, fn($pivot) => $pivot->where('tahun_ajaran_id', $tahunAjaranId))
                ->whereHas('kelas', fn($kelas) => $kelas->where('jurusan', $request->jurusan)));
        }


        $data = $query
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $ruangan = Ruangan::forTahunAjaran($tahunAjaranId)
            ->when($paketUjianId, fn($q) => $q->where('paket_ujian_id', $paketUjianId))
            ->orderBy('nama_ruangan')
            ->get();
        $jurusan = Kelas::forTahunAjaran($tahunAjaranId)->select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');
        $tahunAjarans = \App\Models\TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        return view('features.koordinator.kehadiran.index', compact('data', 'ruangan', 'jurusan', 'tahunAjarans', 'tahunAjaranId', 'paketUjians', 'paketUjianId'));
    }

    /**
     * Download sesuai filter (Excel – ringan & cepat)
     */
    public function downloadExcel(Request $request)
    {
        return Excel::download(
            new KehadiranExport($request),
            'laporan-kehadiran-' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
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
}
