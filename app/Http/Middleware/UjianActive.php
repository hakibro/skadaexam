<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
}
