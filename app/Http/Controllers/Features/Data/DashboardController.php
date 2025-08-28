<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;

class DashboardController extends Controller
{
    public function index()
    {
        return view('features.data.dashboard', [ // Fixed path - remove .index
            'jumlahGuru' => Guru::count(),
            'jumlahSiswa' => Siswa::count(),
            'jumlahKelas' => Kelas::count(),
        ]);
    }
}
