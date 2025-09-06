<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RuanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Ruangan::query()->withCount('sesiRuangan');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by kapasitas
        if ($request->filled('kapasitas_min')) {
            $query->where('kapasitas', '>=', $request->kapasitas_min);
        }

        if ($request->filled('kapasitas_max')) {
            $query->where('kapasitas', '<=', $request->kapasitas_max);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_ruangan', 'like', "%{$search}%")
                    ->orWhere('kode_ruangan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        $ruangans = $query->paginate(10);

        // Calculate statistics for the view
        $statistics = [
            'total' => Ruangan::count(),
            'aktif' => Ruangan::where('status', 'aktif')->count(),
            'nonaktif' => Ruangan::where('status', 'tidak_aktif')->count(),
            'perbaikan' => Ruangan::where('status', 'perbaikan')->count(),
        ];

        return view('features.ruangan.index', compact('ruangans', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('features.ruangan.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:191',
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan',
            'kapasitas' => 'required|integer|min:1|max:1000',
            'lokasi' => 'nullable|string|max:191',
            'fasilitas' => 'nullable|array',
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $ruangan = Ruangan::create([
                'nama_ruangan' => $request->nama_ruangan,
                'kode_ruangan' => $request->kode_ruangan,
                'kapasitas' => $request->kapasitas,
                'lokasi' => $request->lokasi,
                'fasilitas' => $request->fasilitas ?? [],
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return redirect()->route('ruangan.show', $ruangan->id)
                ->with('success', 'Ruangan berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Error creating ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ruangan $ruangan)
    {
        // Load relationships dengan proper counting
        $ruangan->loadCount([
            'sesiRuangan as sesi_ruangan_count',
            'sesiRuangan as active_sessions_count' => function ($query) {
                $query->where('status', 'berlangsung');
            }
        ]);

        // Get active sessions count
        $activeSessions = $ruangan->sesiRuangan()
            ->where('status', 'berlangsung')
            ->orWhere(function ($query) {
                $query->where('status', 'belum_mulai')
                    ->whereHas('jadwalUjians', function ($q) {
                        $q->whereDate('tanggal', now()->toDateString());
                    })
                    ->where('waktu_mulai', '<=', now()->format('H:i:s'))
                    ->where('waktu_selesai', '>=', now()->format('H:i:s'));
            })
            ->count();

        // Get total participants
        $totalParticipants = DB::table('sesi_ruangan_siswa')
            ->join('sesi_ruangan', 'sesi_ruangan_siswa.sesi_ruangan_id', '=', 'sesi_ruangan.id')
            ->where('sesi_ruangan.ruangan_id', $ruangan->id)
            ->count();

        // Get recent sessions
        $recentSessions = $ruangan->sesiRuangan()
            ->with(['pengawas', 'jadwalUjians'])
            ->withCount('sesiRuanganSiswa')
            ->orderBy('waktu_mulai', 'desc')
            ->take(5)
            ->get();

        // Get last used session
        $lastUsedSession = $ruangan->sesiRuangan()
            ->where('status', 'selesai')
            ->orderBy('updated_at', 'desc')
            ->first();

        // Get upcoming sessions count
        $upcomingSessions = $ruangan->sesiRuangan()
            ->where('status', 'belum_mulai')
            ->where('tanggal', '>=', now()->toDateString())
            ->count();

        return view('features.ruangan.show', compact(
            'ruangan',
            'activeSessions',
            'totalParticipants',
            'recentSessions',
            'lastUsedSession',
            'upcomingSessions'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ruangan $ruangan)
    {
        return view('features.ruangan.edit', compact('ruangan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'nama_ruangan' => 'required|string|max:191',
            'kode_ruangan' => 'required|string|max:20|unique:ruangan,kode_ruangan,' . $ruangan->id,
            'kapasitas' => 'required|integer|min:1|max:1000',
            'lokasi' => 'nullable|string|max:191',
            'fasilitas' => 'nullable|array',
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $ruangan->update([
                'nama_ruangan' => $request->nama_ruangan,
                'kode_ruangan' => $request->kode_ruangan,
                'kapasitas' => $request->kapasitas,
                'lokasi' => $request->lokasi,
                'fasilitas' => $request->fasilitas ?? [],
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            return redirect()->route('ruangan.show', $ruangan)
                ->with('success', 'Ruangan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Update status of the resource.
     */
    public function updateStatus(Request $request, Ruangan $ruangan)
    {
        $request->validate([
            'status' => 'required|in:aktif,perbaikan,tidak_aktif',
        ]);

        try {
            $ruangan->update([
                'status' => $request->status,
            ]);

            return redirect()->back()
                ->with('success', 'Status ruangan berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating ruangan status: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruangan $ruangan)
    {
        try {
            // Check if there are any sesi associated with this room
            if ($ruangan->sesiRuangan()->count() > 0) {
                return redirect()->route('ruangan.index')
                    ->with('error', 'Ruangan tidak dapat dihapus karena masih memiliki sesi ujian');
            }

            $ruangan->delete();

            return redirect()->route('ruangan.index')
                ->with('success', 'Ruangan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the room and all related sessions
     */
    public function forceDelete(Ruangan $ruangan)
    {
        try {
            DB::beginTransaction();

            // Get count of sessions before deletion
            $sesiCount = $ruangan->sesiRuangan()->count();

            // Delete all sessions related to this room first
            $ruangan->sesiRuangan()->each(function ($sesi) {
                // Delete all student enrollments for this session
                $sesi->sesiRuanganSiswa()->delete();
                // Delete the session
                $sesi->delete();
            });

            // Now delete the room
            $ruangan->delete();

            DB::commit();

            return redirect()->route('ruangan.index')
                ->with('success', "Ruangan berhasil dihapus paksa beserta $sesiCount sesi terkait");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus paksa ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Import multiple ruangan
     */
    public function import()
    {
        return view('features.ruangan.import');
    }

    /**
     * Process ruangan import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'ruangan_data' => 'required|array',
            'ruangan_data.*.nama_ruangan' => 'required|string|max:191',
            'ruangan_data.*.kode_ruangan' => 'required|string|max:20|distinct|unique:ruangan,kode_ruangan',
            'ruangan_data.*.kapasitas' => 'required|integer|min:1|max:1000',
            'ruangan_data.*.lokasi' => 'nullable|string|max:191',
            'ruangan_data.*.status' => 'nullable|in:aktif,perbaikan,tidak_aktif',
        ]);

        try {
            DB::beginTransaction();

            $ruanganData = $request->ruangan_data;
            $imported = 0;

            foreach ($ruanganData as $data) {
                Ruangan::create([
                    'nama_ruangan' => $data['nama_ruangan'],
                    'kode_ruangan' => $data['kode_ruangan'],
                    'kapasitas' => $data['kapasitas'],
                    'lokasi' => $data['lokasi'] ?? null,
                    'fasilitas' => $data['fasilitas'] ?? [],
                    'status' => $data['status'] ?? 'aktif',
                    'keterangan' => $data['keterangan'] ?? null,
                ]);
                $imported++;
            }

            DB::commit();

            return redirect()->route('ruangan.index')
                ->with('success', $imported . ' ruangan berhasil diimpor');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengimpor ruangan: ' . $e->getMessage());
        }
    }

    /**
     * Bulk delete rooms
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:ruangan,id'
        ]);

        try {
            DB::beginTransaction();

            $roomIds = $request->room_ids;
            $deletedCount = 0;
            $skippedCount = 0;

            foreach ($roomIds as $roomId) {
                $room = Ruangan::find($roomId);
                if ($room) {
                    if ($room->sesiRuangan()->count() > 0) {
                        $skippedCount++;
                    } else {
                        $room->delete();
                        $deletedCount++;
                    }
                }
            }

            DB::commit();

            $message = $deletedCount . ' ruangan berhasil dihapus';
            if ($skippedCount > 0) {
                $message .= '. ' . $skippedCount . ' ruangan dilewati karena masih memiliki sesi ujian';
            }

            return redirect()->route('ruangan.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk deleting ruangan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus ruangan: ' . $e->getMessage());
        }
    }
}
