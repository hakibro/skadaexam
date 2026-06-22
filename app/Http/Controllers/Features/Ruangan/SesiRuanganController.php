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
use App\Models\JadwalUjianSesiRuangan;
use App\Services\TahunAjaranService;
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

        $pengawasList = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->whereIn('name', ['pengawas', 'koordinator']);
            });
        })->orderBy('nama')->get();

        return view('features.ruangan.sesi.index', compact('ruangan', 'sesiList', 'pengawasList'));
    }

    /**
     * Show the form for creating a new session for a room
     */
    public function create(Ruangan $ruangan)
    {
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->route('ruangan.show', $ruangan->id)
                ->with('error', 'Ruangan pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->route('ruangan.show', $ruangan->id)
                ->with('error', 'Ruangan pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'nama_sesi' => 'required|string|max:191',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
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

            // Jika ada konflik, beri warning tapi lanjutkan
            if ($conflict) {
                session()->flash('warning', 'Terdapat konflik jadwal dengan sesi lain di ruangan yang sama, namun sesi tetap dibuat.');
            }

            DB::beginTransaction();

            // Create the session data
            $sesiData = [
                'tahun_ajaran_id' => $ruangan->tahun_ajaran_id,
                'paket_ujian_id' => $ruangan->paket_ujian_id,
                'ruangan_id' => $ruangan->id,
                'nama_sesi' => $request->nama_sesi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => 'belum_mulai',
                'sumber' => 'sumber',
                'kode_sesi' => $request->kode_sesi,
            ];

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
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->route('ruangan.sesi.show', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->route('ruangan.sesi.show', ['ruangan' => $ruangan->id, 'sesi' => $sesi->id])
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
                session()->flash('warning', 'Terdapat konflik jadwal dengan sesi lain di ruangan yang sama, namun perubahan tetap disimpan.');
            }

            DB::beginTransaction();

            $sesi->update([
                'nama_sesi' => $request->nama_sesi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => $request->status,
                'kode_sesi' => $request->kode_sesi,
                'paket_ujian_id' => $ruangan->paket_ujian_id,
            ]);

            DB::commit();

            if ($request->boolean('inline_update')) {
                return redirect()->back()
                    ->with('success', 'Waktu sesi sumber berhasil diperbarui');
            }

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

    public function updatePengawas(Request $request, Ruangan $ruangan, SesiRuangan $sesi)
    {
        if ($sesi->ruangan_id !== $ruangan->id) {
            return redirect()->back()
                ->with('error', 'Sesi tidak valid untuk ruangan ini.');
        }

        if ($ruangan->tahunAjaran?->isReadOnly() || $sesi->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $validated = $request->validate([
            'pengawas_id' => 'nullable|exists:guru,id',
        ]);

        $pengawasId = $validated['pengawas_id'] ?: null;

        if ($pengawasId) {
            $pengawas = Guru::with('user.roles')->findOrFail($pengawasId);
            if (!$pengawas->user || !$pengawas->user->canSupervise()) {
                return redirect()->back()
                    ->with('error', 'Guru yang dipilih tidak memiliki role pengawas atau koordinator.');
            }
        }

        $pivotQuery = JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesi->id);
        $affected = $pivotQuery->count();

        if ($affected === 0) {
            return redirect()->back()
                ->with('error', 'Sesi belum memiliki jadwal ujian, sehingga pengawas belum dapat ditugaskan.');
        }

        $pivotQuery->update(['pengawas_id' => $pengawasId]);

        $message = $pengawasId
            ? 'Pengawas berhasil diterapkan ke semua jadwal pada sesi ini.'
            : 'Pengawas berhasil dikosongkan dari semua jadwal pada sesi ini.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Remove the specified session
     */
    public function destroy(Ruangan $ruangan, SesiRuangan $sesi)
    {
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
        $assignedSiswa = $sesi->sesiRuanganSiswa()->with('siswa.kelas', 'siswa.tahunAjaranRecords.kelas')->get();

        // Get available students (not assigned to this session)
        $availableSiswa = Siswa::whereDoesntHave('sesiRuanganSiswa', function ($query) use ($sesi) {
            $query->where('sesi_ruangan_id', $sesi->id);
        })
            ->whereHas('tahunAjaranRecords', fn($query) => $query->where('tahun_ajaran_id', $ruangan->tahun_ajaran_id))
            ->with('kelas', 'tahunAjaranRecords.kelas')
            // ->where('status_pembayaran', 'Lunas') // Only students who have paid
            ->orderBy('nama')
            ->get();

        $kelasList = Kelas::forTahunAjaran($ruangan->tahun_ajaran_id)->orderBy('nama_kelas')->get();

        return view('features.ruangan.sesi.siswa.index', compact('ruangan', 'sesi', 'assignedSiswa', 'availableSiswa', 'kelasList'));
    }

    /**
     * Store student assignment to session
     */
    public function siswaStore(Request $request, Ruangan $ruangan, SesiRuangan $sesi)
    {
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
                    // Create sesi ruangan siswa record
                    $sesiRuanganSiswa = $sesi->sesiRuanganSiswa()->create([
                        'siswa_id' => $siswaId,
                        'status_kehadiran' => 'tidak_hadir', // Default status
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
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
     * Move a student from current session to another session
     */
    public function pindahSiswa(Request $request, Ruangan $ruangan, SesiRuangan $sesi, Siswa $siswa)
    {
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

        $request->validate([
            'target_sesi_id' => 'required|exists:sesi_ruangan,id',
        ]);

        $targetSesiId = $request->input('target_sesi_id');

        // Check if target is same as current
        if ($targetSesiId == $sesi->id) {
            return redirect()->back()
                ->with('error', 'Sesi tujuan tidak boleh sama dengan sesi asal.');
        }

        try {
            // Load target session with relationships
            $targetSesi = SesiRuangan::with(['ruangan', 'tahunAjaran', 'sesiRuanganSiswa'])
                ->findOrFail($targetSesiId);

            // Check if target session is archived
            if ($targetSesi->tahunAjaran?->isReadOnly()) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat memindahkan siswa ke sesi pada tahun ajaran arsip.');
            }

            // Check room capacity
            $currentStudentCount = $targetSesi->sesiRuanganSiswa()->count();
            $roomCapacity = $targetSesi->ruangan ? $targetSesi->ruangan->kapasitas : 0;

            // Check if student is already in target session
            $alreadyInTarget = $targetSesi->sesiRuanganSiswa()
                ->where('siswa_id', $siswa->id)
                ->exists();

            if ($alreadyInTarget) {
                return redirect()->back()
                    ->with('error', 'Siswa sudah terdaftar di sesi tujuan.');
            }

            if ($roomCapacity > 0 && $currentStudentCount >= $roomCapacity) {
                return redirect()->back()
                    ->with('error', 'Ruangan tujuan sudah penuh (kapasitas: ' . $roomCapacity . ').');
            }

            DB::beginTransaction();

            // Update sesi_ruangan_siswa record
            $sesiSiswa = $sesi->sesiRuanganSiswa()->where('siswa_id', $siswa->id)->first();

            if ($sesiSiswa) {
                // Update to target session
                $sesiSiswa->update([
                    'sesi_ruangan_id' => $targetSesiId,
                ]);
            } else {
                // Create new record if doesn't exist (shouldn't happen in normal flow)
                SesiRuanganSiswa::create([
                    'sesi_ruangan_id' => $targetSesiId,
                    'siswa_id' => $siswa->id,
                    'status_kehadiran' => 'tidak_hadir',
                ]);
            }

            // Update all enrollment_ujian records
            $enrollments = EnrollmentUjian::where('sesi_ruangan_id', $sesi->id)
                ->where('siswa_id', $siswa->id)
                ->get();

            $updatedEnrollments = 0;
            foreach ($enrollments as $enrollment) {
                $enrollment->update([
                    'sesi_ruangan_id' => $targetSesiId,
                    'catatan' => ($enrollment->catatan ? $enrollment->catatan . ' | ' : '') .
                        'Dipindahkan dari ' . $sesi->nama_sesi . ' ke ' . $targetSesi->nama_sesi . ' pada ' . now()->format('d/m/Y H:i'),
                ]);
                $updatedEnrollments++;
            }

            DB::commit();

            $enrollMsg = $updatedEnrollments > 0 ?
                ' dengan ' . $updatedEnrollments . ' enrollment ujian' : '';

            return redirect()->back()
                ->with('success', 'Siswa berhasil dipindahkan dari "' . $sesi->nama_sesi . '" ke "' . $targetSesi->nama_sesi . '"' . $enrollMsg);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moving student between sessions: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memindahkan siswa: ' . $e->getMessage());
        }
    }

    /**
     * Remove all students from a session
     */
    public function siswaDestroyAll(Ruangan $ruangan, SesiRuangan $sesi)
    {
        if ($ruangan->tahunAjaran?->isReadOnly()) {
            return redirect()->back()
                ->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
        }

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
        $availableJadwals = \App\Models\JadwalUjian::forTahunAjaran($sesi->tahun_ajaran_id)
            ->whereDoesntHave('tahunAjaran', fn($q) => $q->where('status', 'arsip'))
            ->whereDoesntHave('sesiRuangans', function ($query) use ($sesi) {
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
            if ($sesi->tahunAjaran?->isReadOnly()) {
                return redirect()->back()->with('error', 'Sesi pada tahun ajaran arsip hanya dapat dilihat.');
            }

            foreach ($request->jadwal_ids as $jadwalId) {
                $jadwal = \App\Models\JadwalUjian::findOrFail($jadwalId);
                if ($jadwal->tahun_ajaran_id !== $sesi->tahun_ajaran_id || $jadwal->tahunAjaran?->isReadOnly()) {
                    continue;
                }

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
            if ($sesi->tahunAjaran?->isReadOnly() || $jadwal->tahun_ajaran_id !== $sesi->tahun_ajaran_id) {
                return redirect()->back()->with('error', 'Jadwal tidak dapat dilepas dari sesi tahun ajaran ini.');
            }

            $sesi->jadwalUjians()->detach($jadwalId);

            return redirect()->route('ruangan.sesi.jadwal.index', [$ruangan->id, $sesi->id])
                ->with('success', 'Jadwal ujian berhasil dilepas dari sesi');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal melepas jadwal ujian: ' . $e->getMessage());
        }
    }


    // Cari siswa di ruangan mana saja
    public function cariSiswa(Request $request)
    {
        $tahunAjaranId = app(TahunAjaranService::class)->activeId();
        if (!$tahunAjaranId) {
            return redirect()->route('tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu untuk mengatur siswa.');
        }

        $search = $request->input('q');
        $siswas = collect();
        if ($search) {
            $siswas = Siswa::with([
                'tahunAJarAnRecords' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId)->with('kelas'),
                'sesiRuanganSiswa.sesiRuangan.ruangan',
                'sesiRuanganSiswa.sesiRuangan.jadwalUjians.mapel'
            ])
                ->whereHas('tahunAJarAnRecords', fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
                ->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('idyayasan', 'like', "%{$search}%");
                })
                ->paginate(10);

            $siswas->getCollection()->each(function ($siswa) use ($tahunAjaranId) {
                $siswa->setRelation('kelas', $siswa->kelasForTahunAjaran($tahunAjaranId));
            });
        }

        $sesiOptions = SesiRuangan::with(['ruangan', 'jadwalUjians.mapel'])
            ->forTahunAjaran($tahunAjaranId)
            ->where('sumber', '<>', 'sumber')
            ->orderBy('ruangan_id')
            ->orderBy('waktu_mulai')
            ->get()
            ->groupBy(fn($sesi) => $sesi->ruangan->nama_ruangan ?? 'Ruangan tidak tersedia');

        return view('features.ruangan.cari-siswa', compact('siswas', 'search', 'sesiOptions'));
    }

    public function assignSiswaKeSesi(Request $request)
    {
        if (!app(TahunAjaranService::class)->activeId()) {
            return redirect()->route('tahun-ajaran.index')
                ->with('error', 'Aktifkan tahun ajaran terlebih dahulu untuk mengatur siswa.');
        }

        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'sesi_ids' => 'required|array|min:1',
            'sesi_ids.*' => 'exists:sesi_ruangan,id',
            'jadwal_ids' => 'nullable|array',
            'jadwal_ids.*' => 'exists:jadwal_ujian,id',
        ]);

        DB::beginTransaction();

        try {
            $assigned = 0;
            $enrolled = 0;
            $siswas = Siswa::with('tahunAJarAnRecords.kelas')->whereIn('id', $validated['siswa_ids'])->get();
            $sesis = SesiRuangan::with(['ruangan', 'jadwalUjians.mapel', 'tahunAjaran'])
                ->whereIn('id', $validated['sesi_ids'])
                ->get();
            $manualJadwals = collect();

            if (!empty($validated['jadwal_ids'])) {
                $manualJadwals = JadwalUjian::with(['mapel', 'tahunAjaran'])
                    ->whereIn('id', $validated['jadwal_ids'])
                    ->get();
            }

            foreach ($sesis as $sesi) {
                if ($sesi->tahunAjaran?->isReadOnly()) {
                    continue;
                }

                foreach ($siswas as $siswa) {
                    $sesiSiswa = $sesi->sesiRuanganSiswa()->firstOrCreate(
                        ['siswa_id' => $siswa->id],
                        ['status_kehadiran' => 'tidak_hadir']
                    );

                    if ($sesiSiswa->wasRecentlyCreated) {
                        $assigned++;
                    }

                    $jadwalCandidates = $manualJadwals->where('tahun_ajaran_id', $sesi->tahun_ajaran_id);

                    foreach ($jadwalCandidates as $jadwal) {
                        if (!$this->isSiswaEligibleForJadwal($siswa, $jadwal)) {
                            continue;
                        }

                        $enrollment = EnrollmentUjian::withTrashed()
                            ->where('jadwal_ujian_id', $jadwal->id)
                            ->where('siswa_id', $siswa->id)
                            ->first();

                        if (!$enrollment) {
                            EnrollmentUjian::create([
                                'siswa_id' => $siswa->id,
                                'jadwal_ujian_id' => $jadwal->id,
                                'sesi_ruangan_id' => $sesi->id,
                                'status_enrollment' => 'enrolled',
                                'catatan' => 'Ditambahkan manual dari Atur Siswa',
                            ]);
                            $enrolled++;
                            continue;
                        }

                        if ($enrollment->trashed()) {
                            $enrollment->restore();
                        }

                        $enrollment->update([
                            'sesi_ruangan_id' => $sesi->id,
                            'status_enrollment' => $enrollment->status_enrollment ?: 'enrolled',
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('ruangan.cari-siswa', ['q' => $request->input('q')])
                ->with('success', "{$assigned} siswa ditambahkan ke sesi, {$enrolled} enrollment dibuat dari jadwal yang dipilih.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal mengatur siswa: ' . $e->getMessage());
        }
    }

    private function isSiswaEligibleForJadwal(Siswa $siswa, JadwalUjian $jadwal): bool
    {
        $kelas = $siswa->kelasForTahunAjaran($jadwal->tahun_ajaran_id);

        if (!$kelas) {
            return false;
        }

        $kelasTargets = collect($jadwal->kelas_target ?? [])->map(fn($id) => (string) $id)->all();
        if (!empty($kelasTargets) && !in_array((string) $kelas->id, $kelasTargets, true)) {
            return false;
        }

        if (!$jadwal->mapel) {
            return true;
        }

        $mapelTingkat = $this->normalizeEligibilityValue($jadwal->mapel->tingkat);
        $siswaTingkat = $this->normalizeEligibilityValue($kelas->tingkat);
        if ($mapelTingkat && $siswaTingkat !== $mapelTingkat) {
            return false;
        }

        $mapelJurusan = $this->normalizeEligibilityValue($jadwal->mapel->jurusan);
        $siswaJurusan = $this->normalizeEligibilityValue($kelas->jurusan);
        if ($mapelJurusan && $mapelJurusan !== 'UMUM' && $siswaJurusan !== $mapelJurusan) {
            return false;
        }

        return true;
    }

    private function normalizeEligibilityValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return match ($normalized) {
            '10', 'KELAS 10', 'KELAS X' => 'X',
            '11', 'KELAS 11', 'KELAS XI' => 'XI',
            '12', 'KELAS 12', 'KELAS XII' => 'XII',
            default => $normalized,
        };
    }

    /**
     * Duplicate a session.
     */

    public function duplicate(Ruangan $ruangan, SesiRuangan $sesi)
    {
        try {
            DB::beginTransaction();

            // 1. Duplikasi sesi
            $newSesi = $sesi->replicate();
            $newSesi->nama_sesi = $sesi->nama_sesi . ' (copy)';
            $newSesi->status = 'belum_mulai';
            $newSesi->sumber = $sesi->kode_sesi;
            $newSesi->kode_sesi = null; // akan digenerate otomatis oleh boot creating
            $newSesi->token_ujian = null;
            $newSesi->token_expired_at = null;
            $newSesi->save();

            // 2. Duplikasi data siswa (sesi_ruangan_siswa)
            $copiedCount = 0;
            foreach ($sesi->sesiRuanganSiswa as $siswaEntry) {
                $newSesi->sesiRuanganSiswa()->create([
                    'siswa_id' => $siswaEntry->siswa_id,
                    'status_kehadiran' => 'tidak_hadir', // reset kehadiran
                    'keterangan' => null,
                ]);
                $copiedCount++;
            }

            DB::commit();

            return redirect()->route('ruangan.sesi.edit', [$ruangan->id, $newSesi->id])
                ->with('success', "Sesi berhasil diduplikasi beserta {$copiedCount} data siswa. Silakan periksa dan sesuaikan.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating sesi: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menduplikasi sesi: ' . $e->getMessage());
        }
    }

}
