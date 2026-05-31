<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        $tahunAjarans = TahunAjaran::withCount(['paketUjian', 'jadwalUjian'])
            ->orderByDesc('is_active')
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->get();

        return view('admin.tahun-ajaran.index', compact('tahunAjarans'));
    }

    public function create()
    {
        return view('admin.tahun-ajaran.create', [
            'tahunAjaran' => new TahunAjaran(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:tahun_ajaran,kode',
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $isActive = $request->boolean('is_active') || $validated['status'] === 'aktif';

            if ($isActive) {
                TahunAjaran::query()->update(['is_active' => false]);
                $validated['status'] = 'aktif';
            }

            $validated['is_active'] = $isActive;
            TahunAjaran::create($validated);
        });

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil dibuat.');
    }

    public function edit(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->isReadOnly()) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Tahun ajaran arsip bersifat read-only.');
        }

        return view('admin.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->isReadOnly()) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Tahun ajaran arsip bersifat read-only.');
        }

        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:tahun_ajaran,kode,' . $tahunAjaran->id,
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request, $tahunAjaran) {
            $isActive = $request->boolean('is_active') || $validated['status'] === 'aktif';

            if ($isActive) {
                TahunAjaran::whereKeyNot($tahunAjaran->id)->update(['is_active' => false]);
                $validated['status'] = 'aktif';
            }

            $validated['is_active'] = $isActive;
            $tahunAjaran->update($validated);
        });

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function activate(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->isReadOnly()) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Tahun ajaran arsip tidak dapat diaktifkan sebelum statusnya diubah.');
        }

        DB::transaction(function () use ($tahunAjaran) {
            TahunAjaran::query()->update(['is_active' => false]);
            $tahunAjaran->update([
                'status' => 'aktif',
                'is_active' => true,
            ]);
        });

        return redirect()->route('admin.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran aktif berhasil diganti.');
    }
}
