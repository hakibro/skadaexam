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


class KehadiranController extends Controller
{
    public function index(Request $request)
    {
        $query = SesiRuanganSiswa::query()
            ->with([
                'siswa.kelas',
                'sesiRuangan',
                'sesiRuangan.jadwalUjian',
                'sesiRuangan.ruangan',
            ]);

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
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('tingkat', $request->tingkat);
            });
        }

        // Jurusan (dari tabel kelas, bukan master jurusan)
        if ($request->filled('jurusan')) {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('jurusan', $request->jurusan);
            });
        }


        $data = $query
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $ruangan = Ruangan::orderBy('nama_ruangan')->get();
        $jurusan = Kelas::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan');

        return view('features.koordinator.kehadiran.index', compact('data', 'ruangan', 'jurusan'));
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
}
