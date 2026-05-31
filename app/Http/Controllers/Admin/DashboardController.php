<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $jumlahGuru = Guru::count();
        $jumlahSiswa = Siswa::count();
        $activeYearId = app(TahunAjaranService::class)->activeId();
        $jumlahKelas = Kelas::forTahunAjaran($activeYearId)->count();

        return view('admin.dashboard', compact('jumlahGuru', 'jumlahSiswa', 'jumlahKelas'));
    }
}
