<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Check payment status - allow if 'Lunas' or has recommendation 'ya'
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
            ]);

            // Find active enrollment where sesi ruangan has matching token
            $enrollment = EnrollmentUjian::with(['sesiRuangan', 'siswa'])
                ->where('siswa_id', $siswa->id)
                ->whereHas('sesiRuangan', function ($query) use ($token) {
                    $query->where('token_ujian', $token);
                })
                ->whereIn('status_enrollment', ['enrolled', 'active'])
                ->first();

            // If not found, try to find any enrollment with this sesi ruangan token for this student
            // (maybe the status is different)
            if (!$enrollment) {
                $enrollment = EnrollmentUjian::with(['sesiRuangan', 'siswa'])
                    ->where('siswa_id', $siswa->id)
                    ->whereHas('sesiRuangan', function ($query) use ($token) {
                        $query->where('token_ujian', $token);
                    })
                    ->first();

                if ($enrollment && !in_array($enrollment->status_enrollment, ['enrolled', 'active'])) {
                    Log::warning('Found enrollment with non-active status', [
                        'siswa_id' => $siswa->id,
                        'enrollment_id' => $enrollment->id,
                        'status' => $enrollment->status_enrollment,
                        'token' => $token,
                    ]);

                    return back()->withErrors([
                        'token' => 'Token ditemukan tapi status enrollment tidak aktif: ' . $enrollment->status_enrollment .
                            '. Silahkan hubungi pengawas.'
                    ])->withInput();
                }
            }

            if (!$enrollment) {
                // Debug: Check what enrollments exist for this student and their sesi ruangan tokens
                $allEnrollments = EnrollmentUjian::with('sesiRuangan')->where('siswa_id', $siswa->id)->get();
                $sesiWithToken = SesiRuangan::where('token_ujian', $token)->get();

                Log::warning('No matching enrollment found', [
                    'siswa_id' => $siswa->id,
                    'token_search' => $token,
                    'student_enrollments' => $allEnrollments->map(function ($e) {
                        return [
                            'id' => $e->id,
                            'sesi_ruangan_id' => $e->sesi_ruangan_id,
                            'sesi_token' => $e->sesiRuangan->token_ujian ?? null,
                            'sesi_expired' => $e->sesiRuangan->token_expired_at ?? null,
                            'status' => $e->status_enrollment,
                        ];
                    })->toArray(),
                    'sesi_with_token' => $sesiWithToken->map(function ($s) {
                        return [
                            'id' => $s->id,
                            'nama' => $s->nama_sesi,
                            'token' => $s->token_ujian,
                            'expired' => $s->token_expired_at,
                        ];
                    })->toArray(),
                ]);

                return back()->withErrors([
                    'token' => 'Token tidak valid atau sudah tidak aktif. Silahkan hubungi pengawas.' .
                        ' (Debug: Siswa ID ' . $siswa->id . ', Token: ' . $token . ')'
                ])->withInput();
            }

            // Validate sesi ruangan token and session
            $sesiRuangan = $enrollment->sesiRuangan;
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
            if (!$enrollment->sesiRuangan || !in_array($enrollment->sesiRuangan->status, ['berlangsung', 'belum_mulai'])) {
                return back()->withErrors([
                    'token' => 'Sesi ujian belum dimulai atau sudah selesai. Silahkan hubungi pengawas.'
                ])->withInput();
            }

            // Check if this is within exam time
            $sesiRuangan = $enrollment->sesiRuangan;
            $now = now();

            // Get exam date from jadwal ujian related to this sesi ruangan
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

            // Mark enrollment as active and log login
            $enrollment->startExam();

            // Log successful authentication
            Log::info('Student login successful', [
                'siswa_id' => $siswa->id,
                'idyayasan' => $idyayasan,
                'enrollment_id' => $enrollment->id,
                'sesi_ruangan_id' => $enrollment->sesi_ruangan_id,
                'login_time' => now(),
                'ip_address' => $request->ip(),
            ]);

            // Login student using siswa guard
            Auth::guard('siswa')->login($siswa, true);
            $request->session()->regenerate();

            // Store enrollment info in session for exam context
            $request->session()->put('current_enrollment_id', $enrollment->id);
            $request->session()->put('current_sesi_ruangan_id', $enrollment->sesi_ruangan_id);

            // Debug log for redirecting
            Log::info('Redirecting student after login', [
                'siswa_id' => $siswa->id,
                'route' => 'siswa.dashboard',
                'intended_url' => '/siswa/dashboard'
            ]);

            return redirect()->route('siswa.dashboard')->with(
                'success',
                'Login berhasil! Selamat datang ' . $siswa->nama . '. Ujian: ' .
                    ($enrollment->sesiRuangan->nama_sesi ?? 'Ujian')
            );
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
