<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $mapels = Mapel::withCount('enrolledStudents')->orderBy('nama_mapel')->get();

        // Filter options
        $tingkatList = Mapel::distinct('tingkat')->pluck('tingkat')->filter()->values();
        $jurusanList = Mapel::distinct('jurusan')->pluck('jurusan')->filter()->values();

        return view('features.naskah.enrollment.index', compact('mapels', 'tingkatList', 'jurusanList'));
    }

    /**
     * Show enrollment for a specific mapel
     */
    public function show(Mapel $mapel)
    {
        $siswaEnrolled = $mapel->siswa()
            ->wherePivot('status_enrollment', 'aktif')
            ->orderBy('nama_lengkap')
            ->paginate(20);

        return view('features.naskah.enrollment.show', compact('mapel', 'siswaEnrolled'));
    }

    /**
     * Show form to enroll students to a mapel
     */
    public function create(Mapel $mapel)
    {
        $kelas = Kelas::orderBy('name')->get();

        return view('features.naskah.enrollment.create', compact('mapel', 'kelas'));
    }

    /**
     * Store enrollment data
     */
    public function store(Request $request, Mapel $mapel)
    {
        $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'exists:siswa,id',
        ]);

        try {
            DB::beginTransaction();

            $enrolled = 0;
            $skipped = 0;

            foreach ($request->siswa_ids as $siswaId) {
                // Check if already enrolled
                $exists = $mapel->siswa()
                    ->where('siswa.id', $siswaId)
                    ->exists();

                if (!$exists) {
                    $mapel->siswa()->attach($siswaId, [
                        'tanggal_daftar' => Carbon::today(),
                        'status_enrollment' => 'aktif',
                        'enrolled_by' => Auth::id(),
                    ]);
                    $enrolled++;
                } else {
                    $skipped++;
                }
            }

            DB::commit();

            return redirect()->route('naskah.enrollment.show', $mapel)
                ->with('success', "{$enrolled} siswa berhasil didaftarkan ke mata pelajaran {$mapel->nama_mapel}. {$skipped} siswa dilewati karena sudah terdaftar.");
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Unenroll a student from a mapel
     */
    public function destroy(Mapel $mapel, Siswa $siswa)
    {
        try {
            $mapel->siswa()->detach($siswa->id);

            return redirect()->route('naskah.enrollment.show', $mapel)
                ->with('success', "Siswa {$siswa->nama_lengkap} berhasil dikeluarkan dari mata pelajaran {$mapel->nama_mapel}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Change enrollment status
     */
    public function updateStatus(Request $request, Mapel $mapel, Siswa $siswa)
    {
        $request->validate([
            'status' => 'required|in:aktif,tidak_aktif,lulus',
        ]);

        try {
            $mapel->siswa()->updateExistingPivot($siswa->id, [
                'status_enrollment' => $request->status,
            ]);

            return redirect()->route('naskah.enrollment.show', $mapel)
                ->with('success', "Status enrollment siswa {$siswa->nama_lengkap} diubah menjadi {$request->status}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get students for a specific kelas (AJAX)
     */
    public function getSiswaByKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
        ]);

        $mapel = Mapel::findOrFail($request->mapel_id);
        $enrolledIds = $mapel->siswa()->pluck('siswa.id')->toArray();

        $siswa = Siswa::where('kelas_id', $request->kelas_id)
            ->whereNotIn('id', $enrolledIds)
            ->select('id', 'nama_lengkap', 'nis', 'nisn')
            ->orderBy('nama_lengkap')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $siswa,
            'count' => $siswa->count(),
        ]);
    }
}
