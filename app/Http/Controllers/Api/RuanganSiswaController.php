<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganSiswaController extends Controller
{
    /**
     * Menampilkan semua ruangan beserta sesi, siswa, dan jadwal ujian.
     *
     * @return \Illuminate\Http\JsonResponse
     */
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

        $ruangan = Ruangan::with([
            'sesiRuangan' => function ($query) use ($request, $studentFilter) {
                $query->where('sumber', 'sumber')
                    ->when($this->hasStudentFilter($request), function ($q) use ($studentFilter) {
                        $q->whereHas('siswa', $studentFilter);
                    })
                    ->orderBy('nama_sesi')
                    ->orderBy('waktu_mulai');
            },
            'sesiRuangan.siswa' => function ($query) use ($studentFilter) {
                $query->select('siswa.id', 'siswa.nis', 'siswa.idyayasan', 'siswa.nama', 'siswa.kelas_id');
                $studentFilter($query);
            },
            'sesiRuangan.siswa.kelas:id,nama_kelas,tingkat,jurusan',
            'sesiRuangan.jadwalUjians' => function ($query) {
                $query->with('mapel:id,nama_mapel');
            }
        ])
            ->when($this->hasStudentFilter($request), function ($query) use ($studentFilter) {
                $query->whereHas('sesiRuangan', function ($q) use ($studentFilter) {
                    $q->where('sumber', 'sumber')
                        ->whereHas('siswa', $studentFilter);
                });
            })
            ->get();

        // Format data
        $result = $ruangan->map(function ($ruang) {
            return [
                'id' => $ruang->id,
                'kode_ruangan' => $ruang->kode_ruangan,
                'nama_ruangan' => $ruang->nama_ruangan,
                'lokasi' => $ruang->lokasi,
                'kapasitas' => $ruang->kapasitas,
                'sesi' => $ruang->sesiRuangan->map(function ($sesi) {
                    // Format siswa
                    $siswa = $sesi->siswa->map(function ($s) use ($sesi) {
                        return [
                            'id' => $s->id,
                            'nis' => $s->nis,
                            'idyayasan' => $s->idyayasan,
                            'nama' => $s->nama,
                            'kelas_id' => $s->kelas_id,
                            'kelas' => $s->kelas ? [
                                'id' => $s->kelas->id,
                                'nama_kelas' => $s->kelas->nama_kelas,
                                'tingkat' => $s->kelas->tingkat,
                                'jurusan' => $s->kelas->jurusan,
                            ] : null,
                            'status_kehadiran' => $s->pivot->status_kehadiran ?? null,
                            'keterangan' => $s->pivot->keterangan ?? null,
                        ];
                    });

                    // Format jadwal ujian
                    $jadwal = $sesi->jadwalUjians->map(function ($j) {
                        return [
                            'id' => $j->id,
                            'judul' => $j->judul,
                            'mapel' => $j->mapel ? [
                                'id' => $j->mapel->id,
                                'nama' => $j->mapel->nama_mapel,
                            ] : null,
                            'tanggal' => $j->tanggal ? $j->tanggal->format('Y-m-d') : null,
                            'durasi_menit' => $j->durasi_menit,
                            'status' => $j->status,
                            'pengawas_id' => $j->pivot->pengawas_id ?? null, // dari pivot
                        ];
                    });

                    return [
                        'id' => $sesi->id,
                        'kode_sesi' => $sesi->kode_sesi,
                        'sumber' => $sesi->sumber,
                        'nama_sesi' => $sesi->nama_sesi,
                        'waktu_mulai' => $sesi->waktu_mulai,
                        'waktu_selesai' => $sesi->waktu_selesai,
                        'status' => $sesi->status,
                        'siswa' => $siswa,
                        'total_siswa' => $siswa->count(),
                        'jadwal_ujian' => $jadwal,
                        'total_jadwal' => $jadwal->count(),
                    ];
                }),
                'total_sesi' => $ruang->sesiRuangan->count(),
            ];
        });

        return response()->json([
            'success' => true,
            'filters' => $request->only(['search', 'q', 'nama', 'idyayasan', 'tingkat', 'kelas', 'kelas_id']),
            'data' => $result
        ]);
    }

    private function hasStudentFilter(Request $request): bool
    {
        return $request->filled('search')
            || $request->filled('q')
            || $request->filled('nama')
            || $request->filled('idyayasan')
            || $request->filled('tingkat')
            || $request->filled('kelas')
            || $request->filled('kelas_id');
    }
}
