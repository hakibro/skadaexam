<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HasilUjian;
use App\Models\EnrollmentUjian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UjianActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if there's an active exam session
        if (!session('ujian_aktif') || !session('hasil_ujian_id')) {
            Log::warning('No active exam session found in middleware UjianActive', [
                'session_data' => $request->session()->all(),
                'user_id' => Auth::guard('siswa')->id(),
            ]);
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Tidak ada ujian yang sedang berlangsung');
        }

        $hasilUjianId = session('hasil_ujian_id');
        $hasilUjian = HasilUjian::find($hasilUjianId);

        // Jika hasil ujian tidak ditemukan
        if (!$hasilUjian) {
            Log::error('Hasil Ujian not found in middleware UjianActive', [
                'hasil_ujian_id' => $hasilUjianId,
                'user_id' => Auth::guard('siswa')->id(),
            ]);
            $this->forceLogout($request);
            return redirect()->route('login.siswa')->with('error', 'Sesi ujian tidak valid.');
        }

        // Cek enrollment status
        $enrollment = EnrollmentUjian::find($hasilUjian->enrollment_ujian_id);
        if (!$enrollment || $enrollment->status_enrollment !== 'active') {
            Log::error('Enrollment not active in middleware UjianActive', [
                'status_enrollment' => $enrollment ? $enrollment->status_enrollment : 'not found',
                'user_id' => Auth::guard('siswa')->id(),
            ]);
            // $this->forceLogout($request);
            // Log::info('Redirecting to login.siswa after forceLogout', [
            //     'route' => 'login.siswa',
            //     'user_id' => Auth::guard('siswa')->id(),
            // ]);
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Status enrollment tidak aktif. Ujian dihentikan.');
        }

        // Check if the exam has not expired
        $waktuMulai = session('waktu_mulai');
        $durasi = session('durasi'); // in minutes
        $waktuSelesai = \Carbon\Carbon::parse($waktuMulai)->addMinutes($durasi);
        $sekarang = \Carbon\Carbon::now();

        if ($sekarang->gt($waktuSelesai)) {
            // Time's up, finish the exam automatically

            $enrollment->status_enrollment = 'completed';
            $enrollment->catatan = 'Ujian selesai otomatis karena waktu habis.';
            $enrollment->save();

            return redirect()->route('siswa.dashboard')
                ->with('warning', 'Waktu ujian telah habis');
        }

        return $next($request);
    }
    private function forceLogout(Request $request)
    {
        Log::info('Forcing logout in UjianActive middleware', [
            'user_id' => Auth::guard('siswa')->id(),
        ]);
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
