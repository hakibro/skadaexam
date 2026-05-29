<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalUjian::with([
            'mapel:id,nama_mapel,jurusan',
            'bankSoal:id,judul',
            // 'sesiRuangans.ruangan:id,kode_ruangan,nama_ruangan,kapasitas',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        $jadwals = $query->orderBy('tanggal')
            ->orderBy('judul')
            ->get()
            ->groupBy(fn($jadwal) => optional($jadwal->tanggal)->format('Y-m-d') ?? 'tanpa_tanggal')
            ->map(fn($items, $tanggal) => [
                'tanggal' => $tanggal,
                'total_jadwal' => $items->count(),
                'jadwal' => $items->map(fn($jadwal) => $this->formatJadwal($jadwal))->values(),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $jadwals,
        ]);
    }

    private function formatJadwal(JadwalUjian $jadwal): array
    {
        return [
            'id' => $jadwal->id,
            'kode_ujian' => $jadwal->kode_ujian,
            'judul' => $jadwal->judul,
            'tanggal' => optional($jadwal->tanggal)->format('Y-m-d'),
            'durasi_menit' => $jadwal->durasi_menit,
            'jumlah_soal' => $jadwal->jumlah_soal,
            'status' => $jadwal->status,
            'mapel' => $jadwal->mapel ? [
                'id' => $jadwal->mapel->id,
                'nama_mapel' => $jadwal->mapel->nama_mapel,
                'jurusan' => $jadwal->mapel->jurusan,
            ] : null,
            'bank_soal' => $jadwal->bankSoal ? [
                'id' => $jadwal->bankSoal->id,
                'judul' => $jadwal->bankSoal->judul,
            ] : null,
        ];
    }
}
