<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjianSesiRuangan;
use Illuminate\Http\Request;

class PengawasController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalUjianSesiRuangan::query()
            ->with([
                'jadwalUjian.mapel:id,nama_mapel',
                'sesiRuangan.ruangan:id,kode_ruangan,nama_ruangan,kapasitas',
                'pengawas:id,nama,nip,email',
            ])
            ->whereNotNull('pengawas_id');

        if ($request->filled('pengawas_id')) {
            $query->where('pengawas_id', $request->pengawas_id);
        }

        if ($request->filled('tanggal')) {
            $query->whereHas('jadwalUjian', fn($q) => $q->whereDate('tanggal', $request->tanggal));
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereHas('jadwalUjian', fn($q) => $q->whereDate('tanggal', '>=', $request->tanggal_mulai));
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereHas('jadwalUjian', fn($q) => $q->whereDate('tanggal', '<=', $request->tanggal_selesai));
        }

        $data = $query->get()
            ->sortBy(fn($row) => optional($row->jadwalUjian?->tanggal)->format('Y-m-d') . ' ' . ($row->sesiRuangan?->waktu_mulai ?? ''))
            ->groupBy(fn($row) => optional($row->jadwalUjian?->tanggal)->format('Y-m-d') ?? 'tanpa_tanggal')
            ->map(fn($items, $tanggal) => [
                'tanggal' => $tanggal,
                'total_pengawas_sesi' => $items->count(),
                'ruangan' => $items
                    ->groupBy(fn($row) => $row->sesiRuangan?->ruangan?->id ?? 'tanpa_ruangan')
                    ->map(function ($ruanganItems) {
                        $ruangan = $ruanganItems->first()->sesiRuangan?->ruangan;

                        return [
                            'id' => $ruangan?->id,
                            'kode_ruangan' => $ruangan?->kode_ruangan,
                            'nama_ruangan' => $ruangan?->nama_ruangan ?? 'Tanpa Ruangan',
                            'kapasitas' => $ruangan?->kapasitas,
                            'total_pengawas_sesi' => $ruanganItems->count(),
                            'pengawas' => $ruanganItems->map(fn($row) => [
                                'id' => $row->pengawas?->id,
                                'nama' => $row->pengawas?->nama,
                                'nip' => $row->pengawas?->nip,
                                'email' => $row->pengawas?->email,
                                'jadwal_ujian' => $row->jadwalUjian ? [
                                    'id' => $row->jadwalUjian->id,
                                    'judul' => $row->jadwalUjian->judul,
                                    'kode_ujian' => $row->jadwalUjian->kode_ujian,
                                    'mapel' => $row->jadwalUjian->mapel?->nama_mapel,
                                    'tanggal' => optional($row->jadwalUjian->tanggal)->format('Y-m-d'),
                                ] : null,
                                'sesi_ruangan' => $row->sesiRuangan ? [
                                    'id' => $row->sesiRuangan->id,
                                    'kode_sesi' => $row->sesiRuangan->kode_sesi,
                                    'nama_sesi' => $row->sesiRuangan->nama_sesi,
                                    'waktu_mulai' => $row->sesiRuangan->waktu_mulai,
                                    'waktu_selesai' => $row->sesiRuangan->waktu_selesai,
                                    'status' => $row->sesiRuangan->status,
                                ] : null,
                            ])->values(),
                        ];
                    })
                    ->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
