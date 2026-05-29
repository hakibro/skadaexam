<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;

class LiveUjianController extends Controller
{
    public function progress(Request $request)
    {
        $activeSesi = SesiRuangan::query()
            ->with(['ruangan:id,kode_ruangan,nama_ruangan,kapasitas'])
            ->where(function ($query) {
                $query->where('status', 'berlangsung')
                    ->orWhere(function ($q) {
                        $q->whereHas('jadwalUjians', fn($jadwal) => $jadwal->whereDate('tanggal', now()->toDateString()))
                            ->where('waktu_mulai', '<=', now()->format('H:i:s'))
                            ->where('waktu_selesai', '>=', now()->format('H:i:s'));
                    });
            })
            ->when($request->filled('ruangan_id'), fn($q) => $q->where('ruangan_id', $request->ruangan_id))
            ->get();

        $sesiIds = $activeSesi->pluck('id');

        $enrollments = EnrollmentUjian::query()
            ->with([
                'siswa.kelas:id,nama_kelas,tingkat,jurusan',
                'jadwalUjian.mapel:id,nama_mapel',
                'hasilUjian.jawabanSiswas',
            ])
            ->whereIn('sesi_ruangan_id', $sesiIds)
            ->when($request->filled('jadwal_ujian_id'), fn($q) => $q->where('jadwal_ujian_id', $request->jadwal_ujian_id))
            ->get()
            ->groupBy('sesi_ruangan_id');

        $data = $activeSesi
            ->groupBy('ruangan_id')
            ->map(function ($sesis) use ($enrollments) {
                $ruangan = $sesis->first()->ruangan;
                $sessionRows = $sesis->map(function ($sesi) use ($enrollments) {
                    $sesiEnrollments = $enrollments->get($sesi->id, collect());
                    $students = $sesiEnrollments->map(function ($enrollment) {
                        $hasil = $enrollment->hasilUjian;
                        $totalSoal = (int) ($hasil?->jumlah_soal ?: $enrollment->jadwalUjian?->jumlah_soal ?: 0);
                        $dijawab = (int) ($hasil?->jumlah_dijawab ?: $hasil?->jawabanSiswas?->whereNotNull('jawaban')->count() ?: 0);

                        return [
                            'siswa' => [
                                'id' => $enrollment->siswa?->id,
                                'idyayasan' => $enrollment->siswa?->idyayasan,
                                'nama' => $enrollment->siswa?->nama,
                                'kelas' => $enrollment->siswa?->kelas?->nama_kelas,
                            ],
                            'jadwal_ujian' => [
                                'id' => $enrollment->jadwalUjian?->id,
                                'judul' => $enrollment->jadwalUjian?->judul,
                                'mapel' => $enrollment->jadwalUjian?->mapel?->nama_mapel,
                            ],
                            'status_enrollment' => $enrollment->status_enrollment,
                            'status_hasil' => $hasil?->status,
                            'jumlah_soal' => $totalSoal,
                            'sudah_dijawab' => $dijawab,
                            'belum_dijawab' => max(0, $totalSoal - $dijawab),
                        ];
                    })->values();

                    return [
                        'id' => $sesi->id,
                        'kode_sesi' => $sesi->kode_sesi,
                        'nama_sesi' => $sesi->nama_sesi,
                        'waktu_mulai' => $sesi->waktu_mulai,
                        'waktu_selesai' => $sesi->waktu_selesai,
                        'status' => $sesi->status,
                        'total_siswa' => $students->count(),
                        'total_sudah_dijawab' => $students->sum('sudah_dijawab'),
                        'total_belum_dijawab' => $students->sum('belum_dijawab'),
                        'siswa' => $students,
                    ];
                })->values();

                return [
                    'ruangan' => $ruangan ? [
                        'id' => $ruangan->id,
                        'kode_ruangan' => $ruangan->kode_ruangan,
                        'nama_ruangan' => $ruangan->nama_ruangan,
                        'kapasitas' => $ruangan->kapasitas,
                    ] : null,
                    'total_sesi_aktif' => $sessionRows->count(),
                    'total_siswa' => $sessionRows->sum('total_siswa'),
                    'total_sudah_dijawab' => $sessionRows->sum('total_sudah_dijawab'),
                    'total_belum_dijawab' => $sessionRows->sum('total_belum_dijawab'),
                    'sesi' => $sessionRows,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'generated_at' => now()->toIso8601String(),
            'data' => $data,
        ]);
    }
}
