<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalUjian::with([
            'mapel:id,nama_mapel,jurusan',
            'bankSoal:id,judul',
            'tahunAjaran:id,nama,kode,is_active',
            'paketUjian:id,nama,status',
            // 'sesiRuangans.ruangan:id,kode_ruangan,nama_ruangan,kapasitas',
        ]);

        $activeYearId = app(TahunAjaranService::class)->activeId();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYearId);

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }

        if ($request->filled('paket_ujian_id')) {
            $query->where('paket_ujian_id', $request->paket_ujian_id);
        }

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
            'tahun_ajaran' => $jadwal->tahunAjaran ? [
                'id' => $jadwal->tahunAjaran->id,
                'kode' => $jadwal->tahunAjaran->kode,
                'nama' => $jadwal->tahunAjaran->nama,
                'is_active' => $jadwal->tahunAjaran->is_active,
            ] : null,
            'paket_ujian' => $jadwal->paketUjian ? [
                'id' => $jadwal->paketUjian->id,
                'nama' => $jadwal->paketUjian->nama,
                'status' => $jadwal->paketUjian->status,
            ] : null,
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
