<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\TahunAjaranService;

class DashboardController extends Controller
{
    public function index()
    {
        $activeYearId = app(TahunAjaranService::class)->activeId();

        return view('features.data.dashboard', [ // Fixed path - remove .index
            'jumlahGuru' => Guru::count(),
            'jumlahSiswa' => $activeYearId
                ? Siswa::whereHas('tahunAjaranRecords', fn($q) => $q->where('tahun_ajaran_id', $activeYearId))->count()
                : Siswa::count(),
            'jumlahKelas' => Kelas::forTahunAjaran($activeYearId)->count(),
        ]);
    }
}
