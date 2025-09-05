<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SesiRuanganController extends Controller
{
    /**
     * Display a listing of sessions for a specific room
     */
    public function index(Ruangan $ruangan)
    {
        $sesiList = SesiRuangan::where('ruangan_id', $ruangan->id)
            ->with(['pengawas', 'sesiRuanganSiswa', 'jadwalUjians'])
            ->withCount(['sesiRuanganSiswa'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        return view('features.ruangan.sesi.index', compact('ruangan', 'sesiList'));
    }

    /**
     * Show the form for creating a new session for a room
     */
    public function create(Ruangan $ruangan)
    {
        $pengawasList = Guru::whereHas('roles', function ($query) {
            $query->whereIn('name', ['pengawas', 'koordinator']);
        })->orderBy('nama')->get();

        $templates = \App\Models\SesiTemplate::where('is_active', true)->get();

        return view('features.ruangan.sesi.create', compact('ruangan', 'pengawasList', 'templates'));
    }

    /**
     * Store a newly created session
     */
    public function store(Request $request, Ruangan $ruangan)
    {
        // Format time fields if they're coming from a template
        if ($request->filled('template_id') && $request->template_id) {
            // Format waktu_mulai and waktu_selesai to ensure H:i format
            if ($request->has('waktu_mulai')) {
                $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
            }
            if ($request->has('waktu_selesai')) {
                $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
            }
        }

        $request->validate([
            'nama_sesi' => 'required|string|max:191',
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'pengawas_id' => 'nullable|exists:guru,id',
            'keterangan' => 'nullable|string',
            'template_id' => 'nullable|exists:sesi_templates,id',
        ]);

        try {
            // Check for time conflicts
            $conflict = SesiRuangan::where('ruangan_id', $ruangan->id)
                ->where('tanggal', $request->tanggal)
                ->whereNotIn('status', ['selesai', 'dibatalkan'])
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('waktu_mulai', '>=', $request->waktu_mulai)
                            ->where('waktu_mulai', '<', $request->waktu_selesai);
                    })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('waktu_selesai', '>', $request->waktu_mulai)
                                ->where('waktu_selesai', '<=', $request->waktu_selesai);
                        })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('waktu_mulai', '<=', $request->waktu_mulai)
                                ->where('waktu_selesai', '>=', $request->waktu_selesai);
                        });
                })
                ->exists();

            if ($conflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terdapat konflik jadwal dengan sesi lain di ruangan yang sama');
            }

            DB::beginTransaction();

            // Create the session data
            $sesiData = [
                'ruangan_id' => $ruangan->id,
                'nama_sesi' => $request->nama_sesi,
                'tanggal' => $request->tanggal,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'pengawas_id' => $request->pengawas_id,
                'status' => 'belum_mulai',
                'keterangan' => $request->keterangan,
            ];

            // If using template, store the reference
            if ($request->template_id) {
                $sesiData['template_id'] = $request->template_id;
            }

            $sesi = SesiRuangan::create($sesiData);

            DB::commit();

            return redirect()->route('ruangan.sesi.show', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('success', 'Sesi berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sesi: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan sesi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified session
     */
    public function show(Ruangan $ruangan, SesiRuangan $sesi)
    {
        $sesi->load(['pengawas', 'sesiRuanganSiswa.siswa', 'jadwalUjians']);

        return view('features.ruangan.sesi.show', compact('ruangan', 'sesi'));
    }

    /**
     * Show the form for editing the specified session
     */
    public function edit(Ruangan $ruangan, SesiRuangan $sesi)
    {
        $pengawasList = Guru::whereHas('roles', function ($query) {
            $query->whereIn('name', ['pengawas', 'koordinator']);
        })->orderBy('nama')->get();

        return view('features.ruangan.sesi.edit', compact('ruangan', 'sesi', 'pengawasList'));
    }

    /**
     * Update the specified session
     */
    public function update(Request $request, Ruangan $ruangan, SesiRuangan $sesi)
    {
        // Format time fields to ensure they're in the correct format
        if ($request->has('waktu_mulai')) {
            $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
        }
        if ($request->has('waktu_selesai')) {
            $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
        }

        $request->validate([
            'nama_sesi' => 'required|string|max:191',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'pengawas_id' => 'nullable|exists:guru,id',
            'status' => 'required|in:belum_mulai,berlangsung,selesai,dibatalkan',
            'keterangan' => 'nullable|string',
        ]);

        try {
            // Check for time conflicts (excluding current session)
            $conflict = SesiRuangan::where('ruangan_id', $ruangan->id)
                ->where('tanggal', $request->tanggal)
                ->where('id', '!=', $sesi->id)
                ->whereNotIn('status', ['selesai', 'dibatalkan'])
                ->where(function ($query) use ($request) {
                    $query->where(function ($q) use ($request) {
                        $q->where('waktu_mulai', '>=', $request->waktu_mulai)
                            ->where('waktu_mulai', '<', $request->waktu_selesai);
                    })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('waktu_selesai', '>', $request->waktu_mulai)
                                ->where('waktu_selesai', '<=', $request->waktu_selesai);
                        })
                        ->orWhere(function ($q) use ($request) {
                            $q->where('waktu_mulai', '<=', $request->waktu_mulai)
                                ->where('waktu_selesai', '>=', $request->waktu_selesai);
                        });
                })
                ->exists();

            if ($conflict) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Terdapat konflik jadwal dengan sesi lain di ruangan yang sama');
            }

            DB::beginTransaction();

            $sesi->update([
                'nama_sesi' => $request->nama_sesi,
                'tanggal' => $request->tanggal,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'pengawas_id' => $request->pengawas_id,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return redirect()->route('ruangan.sesi.show', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('success', 'Sesi berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sesi: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui sesi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified session
     */
    public function destroy(Ruangan $ruangan, SesiRuangan $sesi)
    {
        try {
            // Check if the session has students
            if ($sesi->sesiRuanganSiswa()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Sesi tidak dapat dihapus karena masih memiliki siswa terdaftar');
            }

            DB::beginTransaction();
            $sesi->delete();
            DB::commit();

            return redirect()->route('ruangan.sesi.index', $ruangan->id)
                ->with('success', 'Sesi berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting sesi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus sesi: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the session including all student enrollments
     */
    public function forceDelete(Ruangan $ruangan, SesiRuangan $sesi)
    {
        try {
            DB::beginTransaction();

            // Get count of student enrollments before deletion
            $studentCount = $sesi->sesiRuanganSiswa()->count();

            // Delete all student enrollments
            $sesi->sesiRuanganSiswa()->delete();

            // Delete the session
            $sesi->delete();

            DB::commit();

            return redirect()->route('ruangan.sesi.index', $ruangan->id)
                ->with('success', "Sesi berhasil dihapus paksa beserta $studentCount data siswa");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting sesi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus paksa sesi: ' . $e->getMessage());
        }
    }

    /**
     * Show student management page for a session
     */
    public function siswaIndex(Ruangan $ruangan, SesiRuangan $sesi)
    {
        $assignedSiswa = $sesi->sesiRuanganSiswa()->with('siswa.kelas')->get();

        // Get available students (not assigned to this session)
        $availableSiswa = Siswa::whereDoesntHave('sesiRuanganSiswa', function ($query) use ($sesi) {
            $query->where('sesi_ruangan_id', $sesi->id);
        })
            ->with('kelas')
            ->where('status_pembayaran', 'Lunas') // Only students who have paid
            ->orderBy('nama')
            ->get();

        $kelasList = Kelas::orderBy('nama_kelas')->get();

        return view('features.ruangan.sesi.siswa.index', compact('ruangan', 'sesi', 'assignedSiswa', 'availableSiswa', 'kelasList'));
    }

    /**
     * Store student assignment to session
     */
    public function siswaStore(Request $request, Ruangan $ruangan, SesiRuangan $sesi)
    {
        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        try {
            DB::beginTransaction();

            $currentCount = $sesi->sesiRuanganSiswa()->count();
            $toBeAdded = count($request->siswa_ids);

            // Check capacity
            if (($currentCount + $toBeAdded) > $ruangan->kapasitas) {
                return redirect()->back()
                    ->with('error', 'Jumlah siswa melebihi kapasitas ruangan (' . $ruangan->kapasitas . ' siswa)');
            }

            $added = 0;
            foreach ($request->siswa_ids as $siswaId) {
                // Check if student is already assigned
                $exists = $sesi->sesiRuanganSiswa()->where('siswa_id', $siswaId)->exists();
                if (!$exists) {
                    $sesi->sesiRuanganSiswa()->create([
                        'siswa_id' => $siswaId,
                        'status' => 'tidak_hadir', // Default status
                    ]);
                    $added++;
                }
            }

            DB::commit();

            return redirect()->route('ruangan.sesi.siswa.index', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('success', $added . ' siswa berhasil ditambahkan ke sesi');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding students to sesi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Remove a student from a session
     */
    public function siswaDestroy(Ruangan $ruangan, SesiRuangan $sesi, Siswa $siswa)
    {
        try {
            DB::beginTransaction();

            $sesiSiswa = $sesi->sesiRuanganSiswa()->where('siswa_id', $siswa->id)->first();
            if ($sesiSiswa) {
                $sesiSiswa->delete();
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Siswa berhasil dihapus dari sesi');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing student from sesi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus siswa dari sesi: ' . $e->getMessage());
        }
    }

    /**
     * Remove all students from a session
     */
    public function siswaDestroyAll(Ruangan $ruangan, SesiRuangan $sesi)
    {
        try {
            DB::beginTransaction();

            $count = $sesi->sesiRuanganSiswa()->count();
            $sesi->sesiRuanganSiswa()->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', $count . ' siswa berhasil dihapus dari sesi');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing all students from sesi: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus semua siswa: ' . $e->getMessage());
        }
    }

    /**
     * Generate token for session
     */
    public function generateToken(Ruangan $ruangan, SesiRuangan $sesi)
    {
        try {
            $token = $sesi->generateToken();

            return response()->json([
                'success' => true,
                'token' => $token,
                'expired_at' => $sesi->token_expired_at->format('d M Y H:i'),
                'message' => 'Token berhasil di-generate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show jadwal ujian management for a session
     */
    public function jadwalIndex(Ruangan $ruangan, SesiRuangan $sesi)
    {
        $sesi->load('jadwalUjians');
        $availableJadwals = \App\Models\JadwalUjian::whereDoesntHave('sesiRuangans', function ($query) use ($sesi) {
            $query->where('sesi_ruangan_id', $sesi->id);
        })->with('mapel')->get();

        return view('features.ruangan.sesi.jadwal.index', compact('ruangan', 'sesi', 'availableJadwals'));
    }

    /**
     * Attach jadwal ujian to session
     */
    public function jadwalStore(Request $request, Ruangan $ruangan, SesiRuangan $sesi)
    {
        $request->validate([
            'jadwal_ids' => 'required|array',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
        ]);

        try {
            foreach ($request->jadwal_ids as $jadwalId) {
                $jadwal = \App\Models\JadwalUjian::findOrFail($jadwalId);
                if (!$sesi->jadwalUjians()->where('jadwal_ujian_id', $jadwalId)->exists()) {
                    $sesi->jadwalUjians()->attach($jadwalId);
                }
            }

            return redirect()->route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id])
                ->with('success', 'Jadwal ujian berhasil ditambahkan ke sesi');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menambahkan jadwal ujian: ' . $e->getMessage());
        }
    }

    /**
     * Detach jadwal ujian from session
     */
    public function jadwalDestroy(Request $request, Ruangan $ruangan, SesiRuangan $sesi, $jadwalId)
    {
        try {
            $jadwal = \App\Models\JadwalUjian::findOrFail($jadwalId);
            $sesi->jadwalUjians()->detach($jadwalId);

            return redirect()->route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id])
                ->with('success', 'Jadwal ujian berhasil dilepas dari sesi');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal melepas jadwal ujian: ' . $e->getMessage());
        }
    }
}
