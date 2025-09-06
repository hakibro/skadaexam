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
    public function destroy(Request $request, Mapel $mapel)
    {
        \Illuminate\Support\Facades\Log::info('Destroy request received', [
            'mapel_id' => $mapel->id,
            'mapel_name' => $mapel->nama_mapel,
            'force' => $request->input('force'),
            'request_data' => $request->all()
        ]);

        // Check if force is either "1", "true", or true (boolean)
        $forceDelete = in_array($request->input('force'), [1, "1", true, "true"], true);

        // Check if mapel is used in bank soal
        if ($mapel->bankSoals()->exists() && !$forceDelete) {
            \Illuminate\Support\Facades\Log::info('Cannot delete mapel due to bank soal relation', [
                'mapel_id' => $mapel->id,
                'bank_soal_count' => $mapel->bankSoals()->count()
            ]);

            return redirect()->route('naskah.mapel.index')
                ->with('error', 'Mata pelajaran tidak dapat dihapus karena digunakan dalam bank soal. Gunakan hapus paksa jika yakin.')
                ->with('mapel_id', $mapel->id);
        }

        if ($forceDelete) {
            // Detach or cascade delete related records if needed
            foreach ($mapel->bankSoals as $bankSoal) {
                // Delete related soals first
                $bankSoal->soals()->delete();
                $bankSoal->delete();
            }

            // Permanently delete the mapel record instead of soft delete
            $mapel->forceDelete();

            \Illuminate\Support\Facades\Log::info('Mapel force deleted', [
                'mapel_id' => $mapel->id,
                'mapel_name' => $mapel->nama_mapel
            ]);
        } else {
            // Regular soft delete
            $mapel->delete();

            \Illuminate\Support\Facades\Log::info('Mapel soft deleted', [
                'mapel_id' => $mapel->id,
                'mapel_name' => $mapel->nama_mapel
            ]);
        }

        return redirect()->route('naskah.mapel.index')
            ->with('success', $forceDelete ? 'Mata pelajaran berhasil dihapus secara permanen' : 'Mata pelajaran berhasil dihapus');
    }

    /**
     * Bulk action handler for mata pelajaran.
     */
    public function bulkAction(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Bulk action request received', [
            'action' => $request->action,
            'mapel_ids' => $request->mapel_ids ?? [],
            'all_request_data' => $request->all()
        ]);

        $request->validate([
            'action' => 'required|in:delete,force_delete,status_aktif,status_nonaktif',
            'mapel_ids' => 'required|array',
            'mapel_ids.*' => 'exists:mapel,id'
        ]);

        $action = $request->action;
        $mapelIds = $request->mapel_ids;
        $count = 0;
        $errors = [];

        switch ($action) {
            case 'delete':
                foreach ($mapelIds as $id) {
                    $mapel = Mapel::find($id);
                    if ($mapel) {
                        if ($mapel->bankSoals()->exists()) {
                            $errors[] = "Mata pelajaran '{$mapel->nama_mapel}' tidak dapat dihapus karena digunakan dalam bank soal";
                        } else {
                            $mapel->delete();
                            $count++;
                        }
                    }
                }
                $message = "Berhasil menghapus {$count} mata pelajaran";
                break;

            case 'force_delete':
                foreach ($mapelIds as $id) {
                    $mapel = Mapel::find($id);
                    if ($mapel) {
                        // Delete related bank soals and their soals
                        foreach ($mapel->bankSoals as $bankSoal) {
                            $bankSoal->soals()->delete();
                            $bankSoal->delete();
                        }
                        // Use forceDelete to permanently remove from database
                        $mapel->forceDelete();
                        $count++;

                        \Illuminate\Support\Facades\Log::info('Mapel force deleted in bulk action', [
                            'mapel_id' => $mapel->id,
                            'mapel_name' => $mapel->nama_mapel
                        ]);
                    }
                }
                $message = "Berhasil menghapus paksa {$count} mata pelajaran beserta bank soal dan soal terkait";
                break;

            case 'status_aktif':
                $count = Mapel::whereIn('id', $mapelIds)->update(['status' => 'aktif']);
                $message = "Berhasil mengubah status {$count} mata pelajaran menjadi aktif";
                break;

            case 'status_nonaktif':
                $count = Mapel::whereIn('id', $mapelIds)->update(['status' => 'nonaktif']);
                $message = "Berhasil mengubah status {$count} mata pelajaran menjadi nonaktif";
                break;
        }

        if (!empty($errors)) {
            return redirect()->route('naskah.mapel.index')
                ->with('warning', $message . '. Beberapa mata pelajaran tidak dapat dihapus: ' . implode(', ', $errors));
        }

        return redirect()->route('naskah.mapel.index')
            ->with('success', $message);
    }

    // The filter method has been removed as we're using standard form submission instead of AJAX
}
