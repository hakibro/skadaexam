<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SesiRuanganSiswa;
use Illuminate\Http\Request;

class KehadiranController extends Controller
{
    public function index(Request $request)
    {
        $studentFilter = function ($query) use ($request) {
            if ($request->filled('search') || $request->filled('q')) {
                $search = $request->filled('search') ? $request->get('search') : $request->get('q');
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('idyayasan', 'like', "%{$search}%");
                });
            }

            if ($request->filled('nama')) {
                $query->where('nama', 'like', '%' . $request->nama . '%');
            }

            if ($request->filled('idyayasan')) {
                $query->where('idyayasan', 'like', '%' . $request->idyayasan . '%');
            }

            if ($request->filled('tingkat')) {
                $query->whereHas('kelas', fn($q) => $q->where('tingkat', $request->tingkat));
            }

            if ($request->filled('kelas_id')) {
                $query->where('kelas_id', $request->kelas_id);
            }

            if ($request->filled('kelas')) {
                $query->whereHas('kelas', fn($q) => $q->where('nama_kelas', 'like', '%' . $request->kelas . '%'));
            }
        };

        $query = SesiRuanganSiswa::query()
            ->with([
                'siswa.kelas:id,nama_kelas,tingkat,jurusan',
                'sesiRuangan.ruangan:id,kode_ruangan,nama_ruangan,kapasitas',
                'sesiRuangan.jadwalUjians.mapel:id,nama_mapel',
            ])
            ->whereHas('sesiRuangan', function ($q) use ($request) {
                $q->where('sumber', '!=', 'sumber')->orWhereNull('sumber');

                if ($request->filled('nama_sesi')) {
                    $q->where('nama_sesi', 'like', '%' . $request->nama_sesi . '%');
                }

                if ($request->filled('tanggal')) {
                    $q->whereHas('jadwalUjians', fn($jadwal) => $jadwal->whereDate('tanggal', $request->tanggal));
                }
            })
            ->whereHas('siswa', $studentFilter);

        if ($request->filled('status_kehadiran')) {
            $query->where('status_kehadiran', $request->status_kehadiran);
        }

        $rows = $query->get()
            ->sortBy(function ($row) {
                $jadwal = $row->sesiRuangan?->jadwalUjians?->first();
                return optional($jadwal?->tanggal)->format('Y-m-d') . ' '
                    . ($row->sesiRuangan?->ruangan?->nama_ruangan ?? '')
                    . ' '
                    . ($row->sesiRuangan?->waktu_mulai ?? '')
                    . ' '
                    . ($row->siswa?->nama ?? '');
            });

        $data = $rows
            ->groupBy(function ($row) {
                $jadwal = $row->sesiRuangan?->jadwalUjians?->first();
                return optional($jadwal?->tanggal)->format('Y-m-d') ?? 'tanpa_tanggal';
            })
            ->map(fn($tanggalRows, $tanggal) => [
                'tanggal' => $tanggal,
                'total_siswa' => $tanggalRows->count(),
                'ruangan' => $tanggalRows
                    ->groupBy(fn($row) => $row->sesiRuangan?->ruangan?->id ?? 'tanpa_ruangan')
                    ->map(function ($ruanganRows) {
                        $ruangan = $ruanganRows->first()->sesiRuangan?->ruangan;

                        return [
                            'id' => $ruangan?->id,
                            'kode_ruangan' => $ruangan?->kode_ruangan,
                            'nama_ruangan' => $ruangan?->nama_ruangan ?? 'Tanpa Ruangan',
                            'kapasitas' => $ruangan?->kapasitas,
                            'total_siswa' => $ruanganRows->count(),
                            'sesi' => $ruanganRows
                                ->groupBy(fn($row) => $row->sesi_ruangan_id)
                                ->map(function ($sesiRows) {
                                    $sesi = $sesiRows->first()->sesiRuangan;

                                    return [
                                        'id' => $sesi?->id,
                                        'kode_sesi' => $sesi?->kode_sesi,
                                        'sumber' => $sesi?->sumber,
                                        'nama_sesi' => $sesi?->nama_sesi,
                                        'waktu_mulai' => $sesi?->waktu_mulai,
                                        'waktu_selesai' => $sesi?->waktu_selesai,
                                        'status' => $sesi?->status,
                                        'jadwal_ujian' => $sesi?->jadwalUjians?->map(fn($jadwal) => [
                                            'id' => $jadwal->id,
                                            'judul' => $jadwal->judul,
                                            'kode_ujian' => $jadwal->kode_ujian,
                                            'mapel' => $jadwal->mapel?->nama_mapel,
                                            'tanggal' => optional($jadwal->tanggal)->format('Y-m-d'),
                                        ])->values() ?? [],
                                        'total_siswa' => $sesiRows->count(),
                                        'siswa' => $sesiRows->map(fn($row) => [
                                            'id' => $row->siswa?->id,
                                            'nis' => $row->siswa?->nis,
                                            'idyayasan' => $row->siswa?->idyayasan,
                                            'nama' => $row->siswa?->nama,
                                            'kelas' => $row->siswa?->kelas ? [
                                                'id' => $row->siswa->kelas->id,
                                                'nama_kelas' => $row->siswa->kelas->nama_kelas,
                                                'tingkat' => $row->siswa->kelas->tingkat,
                                                'jurusan' => $row->siswa->kelas->jurusan,
                                            ] : null,
                                            'status_kehadiran' => $row->status_kehadiran,
                                            'keterangan' => $row->keterangan,
                                        ])->values(),
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'filters' => $request->only([
                'search',
                'q',
                'nama',
                'idyayasan',
                'tingkat',
                'kelas',
                'kelas_id',
                'tanggal',
                'nama_sesi',
                'status_kehadiran',
            ]),
            'data' => $data,
        ]);
    }
}
