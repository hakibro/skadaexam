<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Ruangan;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
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
            ->with(['sesiRuanganSiswa', 'jadwalUjians', 'jadwalUjians.mapel'])
            ->withCount(['sesiRuanganSiswa'])
            ->orderBy('waktu_mulai', 'asc')
            ->get();
        return view('features.ruangan.sesi.index', compact('ruangan', 'sesiList'));
    }

    /**
     * Show the form for creating a new session for a room
     */
    public function create(Ruangan $ruangan)
    {
        $pengawasList = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['pengawas', 'koordinator']);
            });
        })->orderBy('nama')->get();

        return view('features.ruangan.sesi.create', compact('ruangan', 'pengawasList'));
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
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'keterangan' => 'nullable|string',
            'template_id' => 'nullable|exists:sesi_templates,id',
            'kode_sesi' => 'nullable|string|max:20',
        ]);

        try {
            // Check for time conflicts - we now only check for time conflicts within the same room
            // without considering date (since date comes from jadwal ujian now)
            $conflict = SesiRuangan::where('ruangan_id', $ruangan->id)
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
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => 'belum_mulai',
                'keterangan' => $request->keterangan,
                'kode_sesi' => $request->kode_sesi,
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
        $sesi->load(['sesiRuanganSiswa.siswa', 'jadwalUjians', 'jadwalUjians.mapel']);

        return view('features.ruangan.sesi.show', compact('ruangan', 'sesi'));
    }

    /**
     * Show the form for editing the specified session
     */
    public function edit(Ruangan $ruangan, SesiRuangan $sesi)
    {
        $pengawasList = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['pengawas', 'koordinator']);
            });
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
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'status' => 'required|in:belum_mulai,berlangsung,selesai,dibatalkan',
            'keterangan' => 'nullable|string',
            'kode_sesi' => 'nullable|string|max:20',
        ]);

        try {
            // Check for time conflicts (excluding current session)
            // Now we only check time conflicts within the same room
            $conflict = SesiRuangan::where('ruangan_id', $ruangan->id)
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
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => $request->status,
                'keterangan' => $request->keterangan,
                'kode_sesi' => $request->kode_sesi,
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
            // ->where('status_pembayaran', 'Lunas') // Only students who have paid
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
            $matchedJadwalIds = [];

            foreach ($request->siswa_ids as $siswaId) {
                // Check if student is already assigned
                $exists = $sesi->sesiRuanganSiswa()->where('siswa_id', $siswaId)->exists();
                if (!$exists) {
                    // Create sesi ruangan siswa record
                    $sesiRuanganSiswa = $sesi->sesiRuanganSiswa()->create([
                        'siswa_id' => $siswaId,
                        'status_kehadiran' => 'tidak_hadir', // Default status
                    ]);
                    $added++;

                    // Get student's class and jurusan
                    $siswa = Siswa::with('kelas')->find($siswaId);
                    if ($siswa && $siswa->kelas) {
                        $kelasJurusan = $siswa->kelas->jurusan;

                        // Find matching jadwal ujian based on jurusan compatibility
                        $matchingJadwals = \App\Models\JadwalUjian::whereHas('mapel', function ($query) use ($kelasJurusan) {
                            $query->where('jurusan', $kelasJurusan)
                                ->orWhere('jurusan', 'UMUM')
                                ->orWhereNull('jurusan'); // If jurusan is null, it applies to all
                        })
                            ->where('status', 'aktif')
                            ->whereJsonContains('kelas_target', $siswa->kelas_id)
                            ->get();

                        foreach ($matchingJadwals as $jadwal) {
                            $matchedJadwalIds[$jadwal->id] = $jadwal;

                            // Create automatic enrollment for this jadwal
                            $existingEnrollment = \App\Models\EnrollmentUjian::where('jadwal_ujian_id', $jadwal->id)
                                ->where('sesi_ruangan_id', $sesi->id)
                                ->where('siswa_id', $siswaId)
                                ->first();

                            if (!$existingEnrollment) {
                                // Create new enrollment record
                                $enrollment = new \App\Models\EnrollmentUjian([
                                    'sesi_ruangan_id' => $sesi->id,
                                    'jadwal_ujian_id' => $jadwal->id,
                                    'siswa_id' => $siswaId,
                                    'status_enrollment' => 'enrolled',
                                    'catatan' => 'Auto-enrolled when assigned to session'
                                ]);
                                $enrollment->save();

                                Log::info("Auto-enrolled student {$siswaId} in jadwal {$jadwal->id} for sesi {$sesi->id}");
                            }
                        }
                    }
                }
            }

            // Attach matched jadwal ujian to sesi ruangan
            if (!empty($matchedJadwalIds)) {
                foreach ($matchedJadwalIds as $jadwalId => $jadwal) {
                    if (!$sesi->jadwalUjians()->where('jadwal_ujian_id', $jadwalId)->exists()) {
                        $sesi->jadwalUjians()->attach($jadwalId);
                        Log::info("Attached jadwal ujian {$jadwalId} to sesi ruangan {$sesi->id}");
                    }
                }
            } else {
                Log::info("No matching jadwal ujian found for students in sesi ruangan {$sesi->id}");
            }

            // If no jadwal ujian was matched, add a warning message
            if (empty($matchedJadwalIds) && $added > 0) {
                session()->flash('warning', 'Siswa ditambahkan, tetapi tidak ada jadwal ujian yang cocok. Harap tambahkan jadwal ujian secara manual.');
            }

            DB::commit();

            $totalEnrollments = \App\Models\EnrollmentUjian::where('sesi_ruangan_id', $sesi->id)
                ->whereIn('siswa_id', $request->siswa_ids)
                ->count();

            $jadwalMsg = count($matchedJadwalIds) > 0 ?
                ' dan ' . count($matchedJadwalIds) . ' jadwal ujian yang sesuai otomatis ditambahkan' : '';
            $enrollMsg = $totalEnrollments > 0 ?
                ', ' . $totalEnrollments . ' enrollment ujian otomatis dibuat' : '';

            return redirect()->route('ruangan.sesi.siswa.index', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('success', $added . ' siswa berhasil ditambahkan ke sesi' . $jadwalMsg . $enrollMsg);
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

            // Delete student assignment
            $sesiSiswa = $sesi->sesiRuanganSiswa()->where('siswa_id', $siswa->id)->first();
            if ($sesiSiswa) {
                $sesiSiswa->delete();
            }

            // Delete all enrollments associated with this student and session
            $enrollments = \App\Models\EnrollmentUjian::where('sesi_ruangan_id', $sesi->id)
                ->where('siswa_id', $siswa->id)
                ->get();

            $deletedEnrollments = 0;
            foreach ($enrollments as $enrollment) {
                // Only delete enrollments that don't have completed exams
                if ($enrollment->status_enrollment !== 'completed' && !$enrollment->hasilUjian()->exists()) {
                    $enrollment->delete();
                    $deletedEnrollments++;
                }
            }

            DB::commit();

            $enrollMsg = $deletedEnrollments > 0 ?
                ' dan ' . $deletedEnrollments . ' enrollment ujian terkait' : '';

            return redirect()->back()
                ->with('success', 'Siswa berhasil dihapus dari sesi' . $enrollMsg);
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

            // Get count of students before deletion
            $count = $sesi->sesiRuanganSiswa()->count();

            // Get student IDs to delete their enrollments later
            $siswaIds = $sesi->sesiRuanganSiswa()->pluck('siswa_id')->toArray();

            // Delete all student assignments
            $sesi->sesiRuanganSiswa()->delete();

            // Delete associated enrollments that don't have completed exams
            $deletedEnrollments = 0;
            if (!empty($siswaIds)) {
                $enrollments = \App\Models\EnrollmentUjian::where('sesi_ruangan_id', $sesi->id)
                    ->whereIn('siswa_id', $siswaIds)
                    ->where(function ($query) {
                        $query->where('status_enrollment', '!=', 'completed')
                            ->orWhereNull('status_enrollment');
                    })
                    ->whereDoesntHave('hasilUjian')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $enrollment->delete();
                    $deletedEnrollments++;
                }
            }

            DB::commit();

            $enrollMsg = $deletedEnrollments > 0 ?
                ' dan ' . $deletedEnrollments . ' enrollment ujian terkait' : '';

            return redirect()->back()
                ->with('success', $count . ' siswa berhasil dihapus dari sesi' . $enrollMsg);
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
