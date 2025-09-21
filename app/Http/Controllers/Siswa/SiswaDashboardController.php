<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SiswaDashboardController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $enrollmentId = $request->session()->get('current_enrollment_id');
        $sesiRuanganId = $request->session()->get('current_sesi_ruangan_id');

        $currentEnrollment = null;
        if ($enrollmentId) {
            $currentEnrollment = EnrollmentUjian::with(['sesiRuangan.jadwalUjians.mapel'])
                ->find($enrollmentId);
        }

        // Fallback: if no session enrollment, find active enrollment for this siswa
        if (!$currentEnrollment) {
            $currentEnrollment = EnrollmentUjian::with([
                'sesiRuangan.jadwalUjians.mapel',
                'siswa'
            ])
                ->where('siswa_id', $siswa->id)
                ->whereHas('sesiRuangan', function ($query) {
                    $query->whereIn('status', ['berlangsung', 'belum_mulai']);
                })
                ->whereHas('sesiRuangan.jadwalUjians', function ($query) {
                    $today = Carbon::today();
                    $query->whereDate('tanggal', '>=', $today);
                })
                ->first();

            if ($currentEnrollment) {
                $request->session()->put('current_enrollment_id', $currentEnrollment->id);
                $request->session()->put('current_sesi_ruangan_id', $currentEnrollment->sesi_ruangan_id);
            }
        }

        // Get jadwal ujian that this student is enrolled in
        $activeMapels = collect([]);
        $today = Carbon::today();

        // Get all enrollments for this student
        $studentEnrollments = EnrollmentUjian::with(['jadwalUjian.mapel', 'sesiRuangan'])
            ->where('siswa_id', $siswa->id)
            ->whereHas('jadwalUjian', function ($query) use ($today) {
                $query->whereDate('tanggal', '>=', $today);
            })
            ->get();

        if ($studentEnrollments->isNotEmpty()) {
            $activeMapels = $studentEnrollments->map(function ($enrollment) use ($today) {
                $jadwal = $enrollment->jadwalUjian;
                $sesi = $enrollment->sesiRuangan;

                return [
                    'jadwal_id' => $jadwal->id,
                    'mapel_name' => $jadwal->mapel->nama_mapel ?? 'Unknown',
                    'mapel_kode' => $jadwal->mapel->kode_mapel ?? 'Unknown',
                    'tanggal' => $jadwal->tanggal,
                    'waktu_mulai' => $sesi->waktu_mulai ?? 'N/A',
                    'waktu_selesai' => $sesi->waktu_selesai ?? 'N/A',
                    'durasi_menit' => $jadwal->durasi_menit,
                    'sesi_name' => $sesi->nama_sesi ?? 'Unknown',
                    'ruangan' => $sesi->ruangan ?? 'Unknown',
                    'enrollment_id' => $enrollment->id,
                    'sesi_ruangan_id' => $enrollment->sesi_ruangan_id,
                    'sesi_ruangan_name' => $enrollment->sesiRuangan->nama_sesi ?? 'Unknown',
                    'is_today' => Carbon::parse($jadwal->tanggal)->isToday(),
                    'is_active' => in_array($sesi->status, ['berlangsung', 'belum_mulai']) &&
                        Carbon::parse($jadwal->tanggal)->isToday()
                ];
            });
        } else {
            // No enrollments found - student might need to be assigned to sessions
            $activeMapels = collect([]);
        }

        return view('features.siswa.dashboard', compact(
            'siswa',
            'currentEnrollment',
            'enrollmentId',
            'sesiRuanganId',
            'activeMapels'
        ));
    }
}
