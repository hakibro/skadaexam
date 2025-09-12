<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentUjian;
use App\Models\SoalUjian;
use App\Models\JawabanSiswa;
use App\Models\HasilUjian;
use App\Models\JadwalUjian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            $currentEnrollment = EnrollmentUjian::with([
                'sesiRuangan.ruangan',
                'sesiRuangan.jadwalUjians.mapel'
            ])->find($enrollmentId);
        }

        // Fallback: if no session enrollment, find active enrollment for this siswa
        if (!$currentEnrollment) {
            $currentEnrollment = EnrollmentUjian::with([
                'sesiRuangan.ruangan',
                'sesiRuangan.jadwalUjians.mapel'
            ])
                ->where('siswa_id', $siswa->id)
                ->whereIn('status_enrollment', ['enrolled', 'active'])
                ->whereHas('sesiRuangan', function ($query) {
                    $query->whereIn('status', ['berlangsung', 'belum_mulai'])
                        ->where('token_expired_at', '>', now());
                })
                ->latest()
                ->first();

            // If found, update session
            if ($currentEnrollment) {
                $request->session()->put('current_enrollment_id', $currentEnrollment->id);
                $request->session()->put('current_sesi_ruangan_id', $currentEnrollment->sesi_ruangan_id);
                $sesiRuanganId = $currentEnrollment->sesi_ruangan_id;
            }
        }

        return view('features.siswa.dashboard', compact(
            'siswa',
            'currentEnrollment',
            'sesiRuanganId'
        ));
    }

    public function portalIndex(Request $request)
    {
        // Check if this is an exam request (has question parameter)
        if ($request->has('question') || $request->has('exam')) {
            // Redirect to exam method
            return $this->exam($request);
        }

        // Otherwise, show the normal dashboard
        return $this->index($request);
    }

    public function exam(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $enrollmentId = $request->session()->get('current_enrollment_id');
        $sesiRuanganId = $request->session()->get('current_sesi_ruangan_id');

        if (!$enrollmentId) {
            return redirect()->route('siswa.dashboard')->with('error', 'Sesi ujian tidak ditemukan.');
        }

        $enrollment = EnrollmentUjian::with([
            'sesiRuangan.jadwalUjians.mapel'
        ])->find($enrollmentId);

        if (!$enrollment) {
            return redirect()->route('siswa.dashboard')->with('error', 'Enrollment tidak ditemukan.');
        }

        // Get the first jadwal ujian for this session
        $jadwalUjian = $enrollment->sesiRuangan->jadwalUjians()->first();

        if (!$jadwalUjian) {
            return redirect()->route('siswa.dashboard')->with('error', 'Jadwal ujian tidak ditemukan.');
        }

        // Check if exam is active
        $now = Carbon::now();
        $examDate = Carbon::parse($jadwalUjian->tanggal)->format('Y-m-d');
        $examStart = Carbon::parse($examDate . ' ' . $enrollment->sesiRuangan->waktu_mulai);
        $examEnd = Carbon::parse($examDate . ' ' . $enrollment->sesiRuangan->waktu_selesai);

        if ($now->lt($examStart)) {
            return redirect()->route('siswa.dashboard')->with('error', 'Ujian belum dimulai.');
        }

        if ($now->gt($examEnd)) {
            return redirect()->route('siswa.dashboard')->with('error', 'Waktu ujian telah berakhir.');
        }

        // Debug info for randomization settings
        Log::info('Jadwal Ujian Settings', [
            'jadwal_ujian_id' => $jadwalUjian->id,
            'acak_soal' => $jadwalUjian->acak_soal ? 'true' : 'false',
            'acak_jawaban' => $jadwalUjian->acak_jawaban ? 'true' : 'false',
            'tampilkan_hasil' => $jadwalUjian->tampilkan_hasil ? 'true' : 'false',
            'durasi_menit' => $jadwalUjian->durasi_menit
        ]);

        // Get exam settings
        $examSettings = [
            'acak_soal' => $jadwalUjian->acak_soal ?? false,
            'acak_jawaban' => $jadwalUjian->acak_jawaban ?? false,
            'tampilkan_hasil' => $jadwalUjian->tampilkan_hasil ?? false,
            'batas_waktu' => $jadwalUjian->durasi_menit ?? 0, // in minutes
        ];

        // Get or create hasil ujian record
        $hasilUjian = HasilUjian::firstOrCreate([
            'siswa_id' => $siswa->id,
            'jadwal_ujian_id' => $jadwalUjian->id,
        ], [
            'enrollment_ujian_id' => $enrollmentId,
            'waktu_mulai' => $now,
            'skor' => 0,
            'status' => 'berlangsung'
        ]);

        // Get questions from bank_soal instead of jadwal_ujian relationship
        $soals = SoalUjian::where('bank_soal_id', $jadwalUjian->bank_soal_id)
            ->where('status', 'aktif')
            ->get();

        // Transform questions to match view expectations
        $questions = $soals->map(function ($soal, $index) use ($jadwalUjian, $siswa) {
            $options = [];

            // Build options array from database columns
            if ($soal->pilihan_a_teks) $options['A'] = $soal->pilihan_a_teks;
            if ($soal->pilihan_b_teks) $options['B'] = $soal->pilihan_b_teks;
            if ($soal->pilihan_c_teks) $options['C'] = $soal->pilihan_c_teks;
            if ($soal->pilihan_d_teks) $options['D'] = $soal->pilihan_d_teks;
            if ($soal->pilihan_e_teks) $options['E'] = $soal->pilihan_e_teks;

            // Handle option randomization with consistent seed per student-question
            if ($jadwalUjian->acak_jawaban) {
                // Use consistent seed based on siswa_id and soal_id for reproducible randomization
                $seed = $siswa->id * 1000 + $soal->id;
                mt_srand($seed);

                $keys = array_keys($options);
                shuffle($keys);
                $shuffledOptions = [];
                foreach ($keys as $i => $key) {
                    $shuffledOptions[chr(65 + $i)] = $options[$key];
                }
                $options = $shuffledOptions;

                // Reset random seed
                mt_srand();
            }

            return [
                'id' => $soal->id,
                'number' => $index + 1,
                'soal' => $soal->pertanyaan,
                'options' => $options,
                'gambar_soal' => $soal->gambar_pertanyaan,
                'kunci_jawaban' => $soal->kunci_jawaban
            ];
        })->toArray();

        // Shuffle questions if enabled
        if ($examSettings['acak_soal']) {
            // Use consistent seed based on siswa_id and jadwal_ujian_id for reproducible randomization
            $seed = $siswa->id + $jadwalUjian->id;
            mt_srand($seed);
            shuffle($questions);
            mt_srand(); // Reset seed
        }

        // Get current question index
        $currentQuestionIndex = intval($request->get('question', 0));
        if ($currentQuestionIndex >= count($questions)) {
            $currentQuestionIndex = count($questions) - 1;
        }
        if ($currentQuestionIndex < 0) {
            $currentQuestionIndex = 0;
        }

        // Get existing answers
        $existingAnswers = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
            ->pluck('jawaban', 'soal_ujian_id')
            ->toArray();

        // Get flagged questions
        $flaggedQuestions = JawabanSiswa::where('hasil_ujian_id', $hasilUjian->id)
            ->where('is_flagged', true)
            ->pluck('soal_ujian_id')
            ->toArray();

        // Calculate remaining time
        $remainingTime = 0;
        if ($examSettings['batas_waktu'] > 0) {
            $timeLimit = $examSettings['batas_waktu'] * 60; // Convert to seconds
            $elapsedTime = $now->diffInSeconds($hasilUjian->waktu_mulai);
            $remainingTime = max(0, $timeLimit - $elapsedTime);
        }

        // Prepare exam data
        $examData = [
            'title' => $jadwalUjian->mapel->nama_mapel ?? 'Ujian',
            'questions' => $questions,
            'currentQuestionIndex' => $currentQuestionIndex,
            'answers' => $existingAnswers,
            'flaggedQuestions' => $flaggedQuestions,
            'totalQuestions' => count($questions),
            'answeredCount' => count(array_filter($existingAnswers)),
            'timeLimit' => $examSettings['batas_waktu'] * 60, // in seconds
            'remainingTime' => $remainingTime,
            'examSettings' => $examSettings,
            'hasilUjianId' => $hasilUjian->id,
            'jadwalUjianId' => $jadwalUjian->id
        ];

        return view('features.siswa.exam', compact('siswa', 'examData'));
    }

    public function saveAnswer(Request $request)
    {
        try {
            $siswa = Auth::guard('siswa')->user();

            $request->validate([
                'hasil_ujian_id' => 'required|exists:hasil_ujian,id',
                'soal_ujian_id' => 'required|exists:soal,id',
                'jawaban' => 'required|string'
            ]);

            // Verify hasil ujian belongs to current siswa
            $hasilUjian = HasilUjian::where('id', $request->hasil_ujian_id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Save or update answer
            JawabanSiswa::updateOrCreate([
                'hasil_ujian_id' => $request->hasil_ujian_id,
                'soal_ujian_id' => $request->soal_ujian_id
            ], [
                'jawaban' => $request->jawaban,
                'waktu_jawab' => now()
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving answer', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to save answer'], 500);
        }
    }

    public function flagQuestion(Request $request)
    {
        try {
            $siswa = Auth::guard('siswa')->user();

            $request->validate([
                'hasil_ujian_id' => 'required|exists:hasil_ujian,id',
                'soal_ujian_id' => 'required|exists:soal,id',
                'is_flagged' => 'required|boolean'
            ]);

            // Verify hasil ujian belongs to current siswa
            $hasilUjian = HasilUjian::where('id', $request->hasil_ujian_id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Update or create jawaban siswa record for flagging
            JawabanSiswa::updateOrCreate([
                'hasil_ujian_id' => $request->hasil_ujian_id,
                'soal_ujian_id' => $request->soal_ujian_id
            ], [
                'is_flagged' => $request->is_flagged
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error flagging question', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to flag question'], 500);
        }
    }

    public function submitExam(Request $request)
    {
        try {
            $siswa = Auth::guard('siswa')->user();

            $request->validate([
                'hasil_ujian_id' => 'required|exists:hasil_ujian,id',
                'is_auto_submit' => 'boolean'
            ]);

            // Verify hasil ujian belongs to current siswa
            $hasilUjian = HasilUjian::where('id', $request->hasil_ujian_id)
                ->where('siswa_id', $siswa->id)
                ->where('status', 'berlangsung')
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Exam not found or already submitted'], 404);
            }

            // Calculate score
            $score = $this->calculateScore($hasilUjian);

            // Update hasil ujian
            $hasilUjian->update([
                'waktu_selesai' => now(),
                'skor' => $score['total_skor'],
                'jumlah_benar' => $score['jumlah_benar'],
                'jumlah_salah' => $score['jumlah_salah'],
                'status' => 'selesai'
            ]);

            // Update enrollment status
            $enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
                ->where('sesi_ruangan_id', $request->session()->get('current_sesi_ruangan_id'))
                ->first();

            if ($enrollment) {
                $enrollment->update(['status_enrollment' => 'completed']);
            }

            Log::info('Exam submitted', [
                'siswa_id' => $siswa->id,
                'hasil_ujian_id' => $hasilUjian->id,
                'score' => $score,
                'is_auto_submit' => $request->is_auto_submit ?? false
            ]);

            return response()->json([
                'success' => true,
                'score' => $score,
                'redirect_url' => route('siswa.exam.result', ['hasil' => $hasilUjian->id])
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting exam', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to submit exam'], 500);
        }
    }

    public function examResult(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $hasilUjianId = $request->get('hasil');

        $hasilUjian = HasilUjian::with([
            'jadwalUjian.mapel',
            'jawabanSiswas.soalUjian'
        ])
            ->where('id', $hasilUjianId)
            ->where('siswa_id', $siswa->id)
            ->first();

        if (!$hasilUjian) {
            return redirect()->route('siswa.dashboard')->with('error', 'Hasil ujian tidak ditemukan.');
        }

        // Check if results should be shown
        if (!($hasilUjian->jadwalUjian->tampilkan_hasil ?? false)) {
            return redirect()->route('siswa.dashboard')->with('success', 'Ujian berhasil dikumpulkan. Hasil akan diumumkan kemudian.');
        }

        return view('features.siswa.exam-result', compact('siswa', 'hasilUjian'));
    }

    private function calculateScore(HasilUjian $hasilUjian)
    {
        $jadwalUjian = $hasilUjian->jadwalUjian;
        $soalUjians = $jadwalUjian->soalUjians()->where('status', 'aktif')->get();
        $jawabanSiswas = $hasilUjian->jawabanSiswas()->get()->keyBy('soal_ujian_id');

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalSkor = 0;

        foreach ($soalUjians as $soal) {
            $jawaban = $jawabanSiswas->get($soal->id);

            if ($jawaban) {
                if ($jawaban->jawaban === $soal->kunci_jawaban) {
                    $jumlahBenar++;
                    $totalSkor += $soal->bobot ?? 1;
                } else {
                    $jumlahSalah++;
                }
            } else {
                $jumlahSalah++;
            }
        }

        return [
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'total_skor' => $totalSkor,
            'total_soal' => $soalUjians->count(),
            'persentase' => $soalUjians->count() > 0 ? ($jumlahBenar / $soalUjians->count()) * 100 : 0
        ];
    }

    public function toggleFlag(Request $request)
    {
        try {
            $siswa = Auth::guard('siswa')->user();

            $request->validate([
                'hasil_ujian_id' => 'required|exists:hasil_ujian,id',
                'soal_ujian_id' => 'required|exists:soal,id'
            ]);

            // Verify hasil ujian belongs to current siswa
            $hasilUjian = HasilUjian::where('id', $request->hasil_ujian_id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get current flag status or create new record
            $jawabanSiswa = JawabanSiswa::where('hasil_ujian_id', $request->hasil_ujian_id)
                ->where('soal_ujian_id', $request->soal_ujian_id)
                ->first();

            $newFlagStatus = !($jawabanSiswa && $jawabanSiswa->is_flagged);

            // Update or create jawaban siswa record for flagging
            JawabanSiswa::updateOrCreate([
                'hasil_ujian_id' => $request->hasil_ujian_id,
                'soal_ujian_id' => $request->soal_ujian_id
            ], [
                'is_flagged' => $newFlagStatus
            ]);

            return response()->json([
                'success' => true,
                'is_flagged' => $newFlagStatus
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling flag', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to toggle flag'], 500);
        }
    }
}
