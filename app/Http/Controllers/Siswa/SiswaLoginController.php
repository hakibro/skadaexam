<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SiswaLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login-siswa');
    }

    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'idyayasan' => 'required|string',
            'token' => 'required|string|size:6',
        ], [
            'idyayasan.required' => 'ID Yayasan wajib diisi',
            'token.required' => 'Token wajib diisi',
            'token.size' => 'Token harus 6 karakter',
        ]);

        $idyayasan = trim($request->idyayasan);
        $token = strtoupper(trim($request->token));

        try {
            // Find siswa by idyayasan  
            $siswa = Siswa::where('idyayasan', $idyayasan)->first();

            if (!$siswa) {
                return back()->withErrors([
                    'idyayasan' => 'ID Yayasan tidak ditemukan. Silahkan hubungi admin.'
                ])->withInput();
            }

            // Check payment status - allow if 'Lunas' OR has recommendation 'ya'
            if ($siswa->status_pembayaran !== 'Lunas' && $siswa->rekomendasi !== 'ya') {
                return back()->withErrors([
                    'idyayasan' => 'Status pembayaran belum lunas dan tidak ada rekomendasi. Silahkan hubungi admin keuangan.'
                ])->withInput();
            }

            // Debug logging for token validation
            Log::info('Student login attempt', [
                'siswa_id' => $siswa->id,
                'idyayasan' => $idyayasan,
                'token_input' => $token,
                'payment_status' => $siswa->status_pembayaran,
                'rekomendasi' => $siswa->rekomendasi,
            ]);

            // Find sesi ruangan with matching token (no enrollment validation required)
            $sesiRuangan = SesiRuangan::where('token_ujian', $token)
                ->whereIn('status', ['berlangsung', 'belum_mulai'])
                ->first();

            if (!$sesiRuangan) {
                Log::warning('No valid token found', [
                    'siswa_id' => $siswa->id,
                    'token_search' => $token,
                ]);

                return back()->withErrors([
                    'token' => 'Token tidak valid atau sudah tidak aktif. Silahkan hubungi pengawas.'
                ])->withInput();
            }

            $enrollmentPivot = $sesiRuangan->siswa()
                ->where('siswa_id', $siswa->id)
                ->exists();

            if (!$enrollmentPivot) {
                return back()->withErrors([
                    'token' => 'Token tidak berlaku untuk siswa ini. Silahkan hubungi pengawas.'
                ])->withInput();
            }

            // Optional: Find enrollment if exists (for context only, not required for login)
            $enrollment = EnrollmentUjian::with(['sesiRuangan', 'siswa'])
                ->where('siswa_id', $siswa->id)
                ->where('sesi_ruangan_id', $sesiRuangan->id)
                ->first();
            // Validate sesi ruangan token
            if (!$sesiRuangan->token_ujian || $sesiRuangan->token_ujian !== $token) {
                return back()->withErrors([
                    'token' => 'Token sesi ruangan tidak valid.'
                ])->withInput();
            }

            // Check if token is expired
            if ($sesiRuangan->token_expired_at && $sesiRuangan->token_expired_at < now()) {
                return back()->withErrors([
                    'token' => 'Token sudah kadaluarsa. Silahkan hubungi pengawas untuk token baru.'
                ])->withInput();
            }

            // Check if session room is active
            if (!in_array($sesiRuangan->status, ['berlangsung', 'belum_mulai'])) {
                return back()->withErrors([
                    'token' => 'Sesi ujian belum dimulai atau sudah selesai. Silahkan hubungi pengawas.'
                ])->withInput();
            }

            // Check if this is within exam time (optional timing validation)
            $now = now();
            $jadwalUjian = $sesiRuangan->jadwalUjians()->first();

            if ($jadwalUjian) {
                $sessionDate = $jadwalUjian->tanggal->format('Y-m-d');
                $startTime = $sessionDate . ' ' . $sesiRuangan->waktu_mulai;
                $endTime = $sessionDate . ' ' . $sesiRuangan->waktu_selesai;

                if ($now->lt($startTime)) {
                    return back()->withErrors([
                        'token' => 'Ujian belum dimulai. Waktu mulai: ' . \Carbon\Carbon::parse($startTime)->format('d M Y H:i')
                    ])->withInput();
                }

                if ($now->gt($endTime)) {
                    return back()->withErrors([
                        'token' => 'Waktu ujian sudah berakhir.'
                    ])->withInput();
                }
            }

            // Mark enrollment as active if exists (optional, for tracking purposes)
            // if ($enrollment) {
            //     $enrollment->startExam();
            // }

            // Log successful authentication
            Log::info('Student login successful', [
                'siswa_id' => $siswa->id,
                'idyayasan' => $idyayasan,
                'enrollment_id' => $enrollment ? $enrollment->id : 'no_enrollment',
                'sesi_ruangan_id' => $sesiRuangan->id,
                'login_time' => now(),
                'ip_address' => $request->ip(),
                'has_enrollment' => $enrollment !== null,
                'payment_status' => $siswa->status_pembayaran,
                'rekomendasi' => $siswa->rekomendasi,
            ]);

            // Login student using siswa guard
            Auth::guard('siswa')->login($siswa, true);
            $request->session()->regenerate();


            // Update status kehadiran di pivot sesi_ruangan_siswa
            try {
                $sesiRuangan->siswa()->updateExistingPivot($siswa->id, [
                    'status_kehadiran' => 'hadir',
                    'keterangan' => 'Login ' . now()->format('d-m-Y H:i:s'),
                    'updated_at' => now(),
                ]);

                Log::info('Kehadiran siswa tercatat', [
                    'siswa_id' => $siswa->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'status_kehadiran' => 'hadir',
                ]);
            } catch (\Exception $e) {
                Log::error('Gagal update status kehadiran siswa', [
                    'siswa_id' => $siswa->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'error' => $e->getMessage(),
                ]);
            }



            // Store enrollment info in session for exam context (if exists)
            if ($enrollment) {
                $request->session()->put('current_enrollment_id', $enrollment->id);
            } else {
                $request->session()->forget('current_enrollment_id');
            }
            $request->session()->put('current_sesi_ruangan_id', $sesiRuangan->id);

            // Debug log for redirecting
            Log::info('Redirecting student after login', [
                'siswa_id' => $siswa->id,
                'route' => 'siswa.dashboard',
                'intended_url' => '/siswa/dashboard',
                'has_enrollment' => $enrollment !== null,
            ]);

            $welcomeMessage = 'Login berhasil! Selamat datang ' . $siswa->nama . '. Sesi: ' . $sesiRuangan->nama_sesi;
            if (!$enrollment) {
                $welcomeMessage .= ' (Anda dapat mengikuti ujian sesuai dengan sesi yang tersedia)';
            }

            return redirect()->route('siswa.dashboard')->with('success', $welcomeMessage);
        } catch (\Exception $e) {
            Log::error('Student login error', [
                'idyayasan' => $idyayasan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'token' => 'Terjadi kesalahan sistem. Silahkan coba lagi atau hubungi admin.'
            ])->withInput();
        }
    }

    public function logout(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $enrollmentId = $request->session()->get('current_enrollment_id');

        // Log the logout in enrollment if available
        if ($enrollmentId && $siswa) {
            try {
                $enrollment = EnrollmentUjian::find($enrollmentId);
                if ($enrollment && $enrollment->siswa_id === $siswa->id) {
                    $enrollment->logLogout();
                }

                Log::info('Student logout', [
                    'siswa_id' => $siswa->id,
                    'enrollment_id' => $enrollmentId,
                    'logout_time' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error logging student logout', [
                    'error' => $e->getMessage(),
                    'siswa_id' => $siswa?->id,
                    'enrollment_id' => $enrollmentId
                ]);
            }
        }

        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login/siswa')->with('success', 'Anda telah logout dari sistem ujian.');
    }
}
