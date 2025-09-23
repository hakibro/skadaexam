<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\HasilUjian;
use App\Models\EnrollmentUjian;
use Carbon\Carbon;

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
            return redirect()->route('siswa.dashboard')
                ->with('error', 'Tidak ada ujian yang sedang berlangsung');
        }

        $hasilUjianId = session('hasil_ujian_id');
        $hasilUjian = HasilUjian::find($hasilUjianId);

        // Jika hasil ujian tidak ditemukan
        if (!$hasilUjian) {
            $this->forceLogout($request);
            return redirect()->route('siswa.login')->with('error', 'Sesi ujian tidak valid.');
        }

        // Cek enrollment status
        $enrollment = EnrollmentUjian::find($hasilUjian->enrollment_ujian_id);
        if (!$enrollment || $enrollment->status_enrollment !== 'active') {
            $this->forceLogout($request);
            return redirect()->route('siswa.login')->with('error', 'Anda telah dikeluarkan dari ujian.');
        }

        // Check if the exam has not expired
        $waktuMulai = session('waktu_mulai');
        $durasi = session('durasi'); // in minutes
        $waktuSelesai = \Carbon\Carbon::parse($waktuMulai)->addMinutes($durasi);
        $sekarang = \Carbon\Carbon::now();

        if ($sekarang->gt($waktuSelesai)) {
            // Time's up, finish the exam automatically
            return redirect()->route('ujian.finish')
                ->with('warning', 'Waktu ujian telah habis');
        }

        return $next($request);
    }
    private function forceLogout(Request $request)
    {
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
