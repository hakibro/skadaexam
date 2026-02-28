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
    public function index()
    {
        // Ambil semua ruangan dengan relasi yang diperlukan
        $ruangan = Ruangan::with([
            'sesiRuangan' => function ($query) {
                $query->orderBy('waktu_mulai');
            },
            'sesiRuangan.siswa' => function ($query) {
                // Ambil kolom siswa yang diperlukan
                $query->select('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.kelas_id');
            },
            'sesiRuangan.jadwalUjians' => function ($query) {
                // Ambil jadwal ujian beserta mapel
                $query->with('mapel:id,nama_mapel');
            }
        ])->get();

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
                            'nama' => $s->nama,
                            'kelas_id' => $s->kelas_id,
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
            'data' => $result
        ]);
    }
}