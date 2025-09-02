<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Services\EnrollmentService;
use App\Services\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianLoginController extends Controller
{
    protected $enrollmentService;
    protected $ujianService;

    public function __construct(EnrollmentService $enrollmentService, UjianService $ujianService)
    {
        $this->enrollmentService = $enrollmentService;
        $this->ujianService = $ujianService;
    }

    /**
     * Show the token login form
     */
    public function showTokenForm()
    {
        return view('auth.token-login');
    }

    /**
     * Process token login
     */
    public function loginWithToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|size:6'
        ]);

        $token = strtoupper($request->token);
        $enrollment = $this->enrollmentService->validateToken($token);

        if (!$enrollment) {
            return redirect()->back()
                ->withErrors(['token' => 'Token tidak valid atau sudah kedaluwarsa'])
                ->withInput();
        }

        // Start the exam session
        $hasilUjian = $this->ujianService->startUjian($token);

        if (!$hasilUjian) {
            return redirect()->back()
                ->withErrors(['token' => 'Gagal memulai ujian, silakan coba lagi'])
                ->withInput();
        }

        // Login the student
        Auth::guard('siswa')->login($enrollment->siswa);

        // Store exam session information in the session
        session([
            'ujian_aktif' => true,
            'hasil_ujian_id' => $hasilUjian->id,
            'jadwal_ujian_id' => $enrollment->jadwal_ujian_id,
            'sesi_ujian_id' => $enrollment->sesi_ujian_id,
            'enrollment_id' => $enrollment->id,
            'waktu_mulai' => $hasilUjian->waktu_mulai,
            'durasi' => $enrollment->jadwalUjian->durasi // in minutes
        ]);

        return redirect()->route('ujian.start');
    }

    /**
     * Logout from the exam session
     */
    public function logout(Request $request)
    {
        // If there's an active exam, finalize it
        if (session('ujian_aktif') && session('hasil_ujian_id')) {
            $hasilUjian = \App\Models\HasilUjian::find(session('hasil_ujian_id'));

            if ($hasilUjian && !$hasilUjian->is_final) {
                $this->ujianService->finalizeUjian($hasilUjian);
            }
        }

        // Clear exam session data
        $request->session()->forget([
            'ujian_aktif',
            'hasil_ujian_id',
            'jadwal_ujian_id',
            'sesi_ujian_id',
            'enrollment_id',
            'waktu_mulai',
            'durasi'
        ]);

        // Logout the student
        Auth::guard('siswa')->logout();

        return redirect()->route('ujian.token');
    }
}
