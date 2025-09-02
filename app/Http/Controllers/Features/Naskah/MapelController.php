<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MapelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mapel::query();

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by tingkat
        if ($request->has('tingkat') && $request->tingkat != '') {
            $query->where('tingkat', $request->tingkat);
        }

        // Filter by jurusan
        if ($request->has('jurusan') && $request->jurusan != '') {
            $query->where('jurusan', $request->jurusan);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_mapel', 'like', "%{$search}%")
                    ->orWhere('kode_mapel', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $mapels = $query->orderBy('nama_mapel', 'asc')->paginate(10);


        // Get unique values for filters
        $tingkats = Mapel::select('tingkat')->distinct()->pluck('tingkat')->sort();
        $jurusans = Mapel::select('jurusan')->distinct()->whereNotNull('jurusan')->pluck('jurusan')->sort();

        return view('features.naskah.mapel.index', compact('mapels', 'tingkats', 'jurusans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $tingkatList = Kelas::pluck('tingkat')->unique();
        $jurusanList = Kelas::pluck('jurusan')->unique();
        return view('features.naskah.mapel.create', compact('tingkatList', 'jurusanList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mapel,kode_mapel',
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string'
        ]);

        $mapel = Mapel::create([
            'kode_mapel' => $request->kode_mapel,
            'nama_mapel' => $request->nama_mapel,
            'deskripsi' => $request->deskripsi,
            'tingkat' => $request->tingkat,
            'jurusan' => $request->jurusan,
            'status' => 'aktif'
        ]);

        return redirect()->route('naskah.mapel.show', $mapel->id)
            ->with('success', 'Mata pelajaran berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mapel $mapel)
    {
        $mapel->load(['bankSoals']);

        // Get exam statistics
        $totalJadwal = $mapel->jadwalUjians()->count();
        $latestBankSoals = $mapel->bankSoals()->latest()->take(5)->get();

        return view('features.naskah.mapel.show', compact(
            'mapel',
            'totalJadwal',
            'latestBankSoals'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mapel $mapel)
    {
        return view('features.naskah.mapel.edit', compact('mapel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'kode_mapel' => 'required|string|max:20|unique:mapel,kode_mapel,' . $mapel->id,
            'nama_mapel' => 'required|string|max:255',
            'tingkat' => 'required|string|max:20',
            'jurusan' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string'
        ]);

        $mapel->update([
            'kode_mapel' => $request->kode_mapel,
            'nama_mapel' => $request->nama_mapel,
            'deskripsi' => $request->deskripsi,
            'tingkat' => $request->tingkat,
            'jurusan' => $request->jurusan,
        ]);

        return redirect()->route('naskah.mapel.show', $mapel->id)
            ->with('success', 'Mata pelajaran berhasil diperbarui');
    }

    /**
     * Update the status of the specified resource.
     */
    public function updateStatus(Request $request, Mapel $mapel)
    {
        $request->validate([
            'status' => 'required|in:aktif,nonaktif',
        ]);

        $mapel->update([
            'status' => $request->status,
        ]);

        return redirect()->route('naskah.mapel.show', $mapel->id)
            ->with('success', 'Status mata pelajaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mapel $mapel)
    {
        // Check if mapel is used in bank soal
        if ($mapel->bankSoals()->exists()) {
            return redirect()->route('naskah.mapel.index')
                ->with('error', 'Mata pelajaran tidak dapat dihapus karena digunakan dalam bank soal');
        }

        $mapel->delete();

        return redirect()->route('naskah.mapel.index')
            ->with('success', 'Mata pelajaran berhasil dihapus');
    }
}
