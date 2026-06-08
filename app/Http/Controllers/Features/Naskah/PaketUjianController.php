<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\PaketUjian;
use App\Models\TahunAjaran;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;

class PaketUjianController extends Controller
{
    public function index(Request $request, TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->active();
        $tahunAjaranId = $request->get('tahun_ajaran_id', $activeYear?->id);
        $tahunAjarans = TahunAjaran::orderByDesc('is_active')->orderByDesc('tanggal_mulai')->get();

        $paketUjians = PaketUjian::with(['tahunAjaran'])
            ->withCount('jadwalUjian')
            ->when($tahunAjaranId, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama')
            ->paginate(30)
            ->appends($request->query());

        return view('features.naskah.paket.index', compact('paketUjians', 'tahunAjarans', 'tahunAjaranId', 'activeYear'));
    }

    public function create(TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->active();
        if (!$activeYear) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Belum ada tahun ajaran aktif. Aktifkan tahun ajaran terlebih dahulu.');
        }

        return view('features.naskah.paket.create', [
            'paketUjian' => new PaketUjian([
                'tahun_ajaran_id' => $activeYear->id,
                'tanggal_mulai' => $activeYear->tanggal_mulai,
                'tanggal_selesai' => $activeYear->tanggal_selesai,
                'status' => 'draft',
            ]),
            'activeYear' => $activeYear,
        ]);
    }

    public function store(Request $request, TahunAjaranService $tahunAjaranService)
    {
        $activeYear = $tahunAjaranService->ensureActive();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
        ]);

        $validated['tahun_ajaran_id'] = $activeYear->id;
        $paketUjian = PaketUjian::create($validated);

        return redirect()->route('naskah.paket-ujian.show', $paketUjian)
            ->with('success', 'Paket ujian berhasil dibuat.');
    }

    public function show(PaketUjian $paketUjian)
    {
        $paketUjian->load(['tahunAjaran', 'jadwalUjian.mapel']);

        return view('features.naskah.paket.show', compact('paketUjian'));
    }

    public function edit(PaketUjian $paketUjian)
    {
        if ($paketUjian->isReadOnly()) {
            return redirect()->route('naskah.paket-ujian.show', $paketUjian)
                ->with('error', 'Paket ujian arsip bersifat read-only.');
        }

        return view('features.naskah.paket.edit', compact('paketUjian'));
    }

    public function update(Request $request, PaketUjian $paketUjian)
    {
        if ($paketUjian->isReadOnly()) {
            return redirect()->route('naskah.paket-ujian.show', $paketUjian)
                ->with('error', 'Paket ujian arsip bersifat read-only.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:draft,aktif,arsip',
            'keterangan' => 'nullable|string',
        ]);

        $paketUjian->update($validated);

        return redirect()->route('naskah.paket-ujian.show', $paketUjian)
            ->with('success', 'Paket ujian berhasil diperbarui.');
    }

    public function updateStatus(Request $request, PaketUjian $paketUjian)
    {
        if ($paketUjian->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Paket ujian pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,aktif,arsip',
        ]);

        $paketUjian->update($validated);

        return redirect()->back()
            ->with('success', 'Status paket ujian berhasil diperbarui.');
    }

    public function destroy(PaketUjian $paketUjian)
    {
        if ($paketUjian->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Paket ujian pada tahun ajaran arsip hanya dapat dilihat.');
        }

        if ($paketUjian->jadwalUjian()->exists()) {
            return redirect()->back()
                ->with('error', 'Paket ujian tidak dapat dihapus karena masih memiliki jadwal ujian.');
        }

        $paketUjian->delete();

        return redirect()->route('naskah.paket-ujian.index')
            ->with('success', 'Paket ujian berhasil dihapus.');
    }
}
