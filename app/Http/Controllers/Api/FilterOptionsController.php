<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Ruangan;
use App\Models\SesiRuangan;

class FilterOptionsController extends Controller
{
    public function index()
    {
        $kelas = Kelas::query()
            ->select('id', 'nama_kelas', 'tingkat', 'jurusan')
            ->orderByRaw("FIELD(tingkat, 'X', 'XI', 'XII')")
            ->orderBy('nama_kelas')
            ->get();

        $ruangan = Ruangan::query()
            ->select('id', 'kode_ruangan', 'nama_ruangan', 'kapasitas', 'status')
            ->orderBy('nama_ruangan')
            ->get();

        $namaSesi = SesiRuangan::query()
            ->select('nama_sesi')
            ->whereNotNull('nama_sesi')
            ->distinct()
            ->orderBy('nama_sesi')
            ->pluck('nama_sesi')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'tingkat' => $kelas->pluck('tingkat')
                    ->filter()
                    ->unique()
                    ->values(),
                'kelas' => $kelas->map(fn($item) => [
                    'id' => $item->id,
                    'nama_kelas' => $item->nama_kelas,
                    'tingkat' => $item->tingkat,
                    'jurusan' => $item->jurusan,
                ])->values(),
                'jurusan' => $kelas->pluck('jurusan')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values(),
                'ruangan' => $ruangan->map(fn($item) => [
                    'id' => $item->id,
                    'kode_ruangan' => $item->kode_ruangan,
                    'nama_ruangan' => $item->nama_ruangan,
                    'kapasitas' => $item->kapasitas,
                    'status' => $item->status,
                ])->values(),
                'nama_sesi_ruangan' => $namaSesi,
                'status_kehadiran' => [
                    ['value' => 'hadir', 'label' => 'Hadir'],
                    ['value' => 'tidak_hadir', 'label' => 'Tidak Hadir'],
                    ['value' => 'sakit', 'label' => 'Sakit'],
                    ['value' => 'izin', 'label' => 'Izin'],
                ],
            ],
        ]);
    }
}
