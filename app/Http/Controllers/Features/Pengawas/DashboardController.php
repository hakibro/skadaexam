<?php

namespace App\Http\Controllers\Features\Pengawas;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the pengawas dashboard
     */

    public function index()
    {
        $user = Auth::user();

        if (!$user->canSupervise() && !$user->isAdmin()) {
            return redirect()->route('home')->with('error', 'Anda tidak memiliki akses pengawas');
        }

        $guru = $user->guru;

        if (!$guru && !$user->isAdmin()) {
            return redirect()->route('login')->with('error', 'User tidak memiliki profil guru');
        }

        $today = Carbon::today();
        $isAdmin = $user->isAdmin();

        // Base query
        $baseQuery = SesiRuangan::query()
            ->with(['ruangan', 'sesiRuanganSiswa'])
            ->orderBy('id', 'desc');

        // Hari ini
        $assignments = (clone $baseQuery)
            ->with(['jadwalUjians' => function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', $today)
                    ->with('mapel')
                    ->orderBy('tanggal', 'asc');

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            }, 'ruangan', 'sesiRuanganSiswa'])
            ->whereHas('jadwalUjians', function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', $today);

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            })
            ->get();

        // Upcoming
        $upcomingAssignments = (clone $baseQuery)
            ->with(['jadwalUjians' => function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', '>', $today)
                    ->with('mapel')
                    ->orderBy('tanggal', 'asc');

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            }, 'ruangan', 'sesiRuanganSiswa'])
            ->whereHas('jadwalUjians', function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', '>', $today);

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            })
            ->get()
            ->sortBy(function ($sesiRuangan) {
                $jadwalUjian = $sesiRuangan->jadwalUjians->first();
                if (!$jadwalUjian) return '9999-12-31 23:59:59';
                return $jadwalUjian->tanggal->format('Y-m-d') . ' ' . $sesiRuangan->waktu_mulai;
            });

        // Past
        $pastAssignments = (clone $baseQuery)
            ->with(['jadwalUjians' => function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', '<', $today)
                    ->with('mapel')
                    ->orderBy('tanggal', 'desc');

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            }, 'ruangan', 'sesiRuanganSiswa'])
            ->whereHas('jadwalUjians', function ($q) use ($today, $guru, $isAdmin) {
                $q->whereDate('tanggal', '<', $today);

                // Apply supervisor filter for non-admin users
                if (!$isAdmin && $guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                }
            })
            ->get()
            ->sortByDesc(function ($sesiRuangan) {
                $jadwalUjian = $sesiRuangan->jadwalUjians->first();
                if (!$jadwalUjian) return '0000-00-00 00:00:00';
                return $jadwalUjian->tanggal->format('Y-m-d') . ' ' . $sesiRuangan->waktu_mulai;
            })
            ->take(10);

        return view('features.pengawas.dashboard', compact(
            'guru',
            'assignments',
            'upcomingAssignments',
            'pastAssignments'
        ));
    }

    /**
     * Display details of specific assignment
     */
    public function showAssignment($id)
    {
        $sesiRuangan = SesiRuangan::with([
            'ruangan',
            'sesiRuanganSiswa',
            'sesiRuanganSiswa.siswa',
            'sesiRuanganSiswa.siswa.kelas'
        ])->findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();
        $guru = null;

        // Skip the check for admin users
        if ($user->isAdmin()) {
            // Admin can view all assignments - load all jadwal ujians
            $sesiRuangan->load(['jadwalUjians', 'jadwalUjians.mapel']);
        } else {
            $guru = $user->guru;
            if (!$guru) {
                return redirect()->back()->with('error', 'User tidak memiliki profil guru');
            }

            // Check if assigned through pivot table and load only relevant jadwal ujians
            $assignedJadwalIds = \App\Models\JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesiRuangan->id)
                ->where('pengawas_id', $guru->id)
                ->pluck('jadwal_ujian_id');

            if ($assignedJadwalIds->isEmpty()) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
            }

            // Load only the jadwal ujians that this pengawas is assigned to
            $assignedJadwals = \App\Models\JadwalUjian::with('mapel')
                ->whereIn('id', $assignedJadwalIds)
                ->get();

            $sesiRuangan->setRelation('jadwalUjians', $assignedJadwals);
        }

        return view('features.pengawas.assignment_detail', compact('sesiRuangan'));
    }

    /**
     * Mark students present or absent
     */
    public function updateAttendance(Request $request, $id)
    {
        $sesiRuangan = SesiRuangan::findOrFail($id);

        // Check if current guru is assigned to this sesi ruangan
        $user = Auth::user();

        // Skip the check for admin users
        if ($user->isAdmin()) {
            // Admin can update attendance for all assignments
        } else {
            $guru = $user->guru;
            if (!$guru) {
                return redirect()->back()->with('error', 'User tidak memiliki profil guru');
            }

            // Check if assigned through pivot table
            $pivotAssignment = \App\Models\JadwalUjianSesiRuangan::where('sesi_ruangan_id', $sesiRuangan->id)
                ->where('pengawas_id', $guru->id)
                ->exists();

            if (!$pivotAssignment) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses ke sesi ruangan ini');
            }
        }

        // Update attendance for each student
        if ($request->has('attendance')) {
            foreach ($request->attendance as $siswaId => $status) {
                SesiRuanganSiswa::where('sesi_ruangan_id', $sesiRuangan->id)
                    ->where('siswa_id', $siswaId)
                    ->update(['status_kehadiran' => $status]);
            }
        }

        return redirect()->back()->with('success', 'Kehadiran siswa berhasil diperbarui');
    }

    /**
     * Debug page to help diagnose assignment issues
     */
    public function debug()
    {
        // Get current user and their guru profile
        $user = Auth::user();
        $guru = $user->guru;

        // Today's date
        $today = Carbon::today();

        // Legacy direct assignments (no longer possible with removed pengawas_id column)
        $directAssignments = collect();

        // Get assignments from pivot table
        $pivotAssignments = collect();
        if ($guru) {
            $pivotSesiIds = \App\Models\JadwalUjianSesiRuangan::where('pengawas_id', $guru->id)
                ->pluck('sesi_ruangan_id');

            if ($pivotSesiIds->count() > 0) {
                $pivotAssignments = SesiRuangan::whereIn('id', $pivotSesiIds)
                    ->whereHas('jadwalUjians', function ($query) use ($today) {
                        $query->whereDate('tanggal', $today);
                    })
                    ->get();
            }
        }

        // Get all assignments as per normal dashboard query
        $assignments = collect();
        if ($user->isAdmin()) {
            $assignments = SesiRuangan::with(['jadwalUjians', 'ruangan', 'sesiRuanganSiswa'])
                ->whereHas('jadwalUjians', function ($query) use ($today) {
                    $query->whereDate('tanggal', $today);
                })
                ->get();
        } else if ($guru) {
            $assignments = SesiRuangan::with(['jadwalUjians', 'ruangan', 'sesiRuanganSiswa'])
                ->whereHas('jadwalUjians', function ($q) use ($guru) {
                    $q->where('jadwal_ujian_sesi_ruangan.pengawas_id', $guru->id);
                })
                ->whereHas('jadwalUjians', function ($query) use ($today) {
                    $query->whereDate('tanggal', $today);
                })
                ->get();
        }

        // Prepare data for display
        $assignmentsData = [];
        foreach ($assignments as $assignment) {
            $jadwalData = [];
            foreach ($assignment->jadwalUjians as $jadwal) {
                $jadwalData[] = [
                    'id' => $jadwal->id,
                    'judul' => $jadwal->judul,
                    'tanggal' => $jadwal->tanggal->format('Y-m-d'),
                    'mapel' => $jadwal->mapel ? $jadwal->mapel->nama : 'No Mapel'
                ];
            }

            $assignmentsData[] = [
                'id' => $assignment->id,
                'kode_sesi' => $assignment->kode_sesi,
                'nama_sesi' => $assignment->nama_sesi,
                'waktu_mulai' => $assignment->waktu_mulai,
                'waktu_selesai' => $assignment->waktu_selesai,
                'pengawas_id' => 'Moved to pivot table',
                'ruangan' => $assignment->ruangan ? $assignment->ruangan->nama_ruangan : 'No Ruangan',
                'jadwal_count' => count($jadwalData),
                'jadwal_data' => $jadwalData,
                'siswa_count' => $assignment->sesiRuanganSiswa->count()
            ];
        }

        return view('features.pengawas.debug', compact(
            'user',
            'guru',
            'assignments',
            'assignmentsData',
            'directAssignments',
            'pivotAssignments'
        ));
    }
}
