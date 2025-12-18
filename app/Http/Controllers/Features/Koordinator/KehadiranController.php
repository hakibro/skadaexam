<?php

namespace App\Http\Controllers\Features\Koordinator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SesiRuanganSiswa;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
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

        // Sesi
        if ($request->filled('sesi_id')) {
            $query->where('sesi_ruangan_id', $request->sesi_id);
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
     * Download sesuai filter (CSV – ringan & cepat)
     */
    public function download(Request $request)
    {
        $query = DB::table('kehadiran')
            ->select([
                'users.nama',
                'kehadiran.kategori',
                'kehadiran.status',
                'kehadiran.tanggal',
                'ruangan.nama as ruangan',
                'sesi.nama as sesi',
                'kelas.nama as kelas',
                'jurusan.nama as jurusan',
            ])
            ->leftJoin('users', 'users.id', '=', 'kehadiran.user_id')
            ->leftJoin('ruangan', 'ruangan.id', '=', 'kehadiran.ruangan_id')
            ->leftJoin('sesi', 'sesi.id', '=', 'kehadiran.sesi_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'kehadiran.kelas_id')
            ->leftJoin('jurusan', 'jurusan.id', '=', 'kelas.jurusan_id');

        // === FILTER SAMA PERSIS DENGAN INDEX ===
        foreach ($request->query() as $key => $value) {
            if ($value === null || $value === '')
                continue;

            match ($key) {
                'kategori' => $query->where('kehadiran.kategori', $value),
                'status' => $query->where('kehadiran.status', $value),
                'ruangan_id' => $query->where('kehadiran.ruangan_id', $value),
                'sesi_id' => $query->where('kehadiran.sesi_id', $value),
                'kelas_id' => $query->where('kehadiran.kelas_id', $value),
                'jurusan_id' => $query->where('kelas.jurusan_id', $value),
                'tingkat' => $query->where('kelas.tingkat', $value),
                default => null,
            };
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('kehadiran.tanggal', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        $data = $query->orderBy('kehadiran.tanggal')->get();

        // ================= EXPORT CSV =================
        $filename = 'kehadiran_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Nama',
                'Kategori',
                'Status',
                'Tanggal',
                'Ruangan',
                'Sesi',
                'Kelas',
                'Jurusan'
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->nama,
                    $row->kategori,
                    $row->status,
                    $row->tanggal,
                    $row->ruangan,
                    $row->sesi,
                    $row->kelas,
                    $row->jurusan,
                ]);
            }

            fclose($file);
        }, $filename);
    }
}
