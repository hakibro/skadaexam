<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activeYear = app(TahunAjaranService::class)->active();
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();
        $tahunAjaranId = request('tahun_ajaran_id', $activeYear?->id);

        $kelas = Kelas::forTahunAjaran($tahunAjaranId)->get();
        $tingkatList = Kelas::forTahunAjaran($tahunAjaranId)->pluck('tingkat')->unique();
        $jurusanList = Kelas::forTahunAjaran($tahunAjaranId)->pluck('jurusan')->unique();

        return view('features.data.kelas.index', compact('kelas', 'tingkatList', 'jurusanList', 'tahunAjarans', 'tahunAjaranId'));
    }
}
