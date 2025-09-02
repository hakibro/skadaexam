<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas = Kelas::all();
        $tingkatList = Kelas::pluck('tingkat')->unique();
        $jurusanList = Kelas::pluck('jurusan')->unique();

        return view('features.data.kelas.index', compact('kelas', 'tingkatList', 'jurusanList'));
    }
}
