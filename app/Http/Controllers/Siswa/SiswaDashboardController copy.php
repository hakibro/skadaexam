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

        // Get jadwal ujian that this student is enrolled in
        $activeMapels = collect([]);
        $today = Carbon::today();

        // Get all enrollments for this student
        $studentEnrollments = EnrollmentUjian::with(['jadwalUjian.mapel', 'sesiRuangan'])
            ->where('siswa_id', $siswa->id)
            ->whereIn('status_enrollment', ['enrolled', 'active'])
            ->whereHas('jadwalUjian', function ($query) use ($today) {
                $query->whereDate('tanggal', $today)
                    ->where('status', 'aktif');
            })
            ->get();

        if ($studentEnrollments->isNotEmpty()) {
            // Transform to match view expectations
            $activeMapels = $studentEnrollments->map(function ($enrollment) {
                $jadwal = $enrollment->jadwalUjian;
                return [
                    'id' => $jadwal->id,
                    'nama_mapel' => $jadwal->mapel ? $jadwal->mapel->nama_mapel : $jadwal->judul,
                    'jadwal_id' => $jadwal->id,
                    'judul' => $jadwal->judul,
                    'status' => $jadwal->status,
                    'durasi' => $jadwal->durasi_menit,
                    'tanggal' => $jadwal->tanggal->format('d/m/Y'),
                    'kode' => $jadwal->kode_ujian ?? '-',
                    'can_start' => $jadwal->canStart(),
                    'enrollment_id' => $enrollment->id,
                    'sesi_ruangan' => $enrollment->sesiRuangan ? $enrollment->sesiRuangan->nama_sesi : 'Tidak ada sesi',
                ];
            });

            Log::info('Active jadwal ujian for enrolled siswa', [
                'siswa_id' => $siswa->id,
                'enrollments_count' => $studentEnrollments->count(),
                'active_jadwal_count' => $activeMapels->count(),
                'jadwal_ujian_ids' => $activeMapels->pluck('id')->toArray()
            ]);
        } else {
            Log::info('No active enrollments found for siswa', [
                'siswa_id' => $siswa->id,
                'date' => $today->format('Y-m-d')
            ]);
        }

        return view('features.siswa.dashboard', compact(
            'siswa',
            'currentEnrollment',
            'sesiRuanganId',
            'activeMapels'
        ));
    }

    public function portalIndex(Request $request)
    {
        // This is the old portal index method. We're now using the main index method for the dashboard.
        // Redirecting to the proper route
        return redirect()->route('siswa.dashboard');
    }

    public function exam(Request $request)
    {
        $siswa = Auth::guard('siswa')->user();
        $enrollmentId = $request->session()->get('current_enrollment_id');
        $sesiRuanganId = $request->session()->get('current_sesi_ruangan_id');
        $jadwalId = $request->input('jadwal_id');

        if (!$enrollmentId) {
            return redirect()->route('siswa.dashboard')->with('error', 'Sesi ujian tidak ditemukan.');
        }

        $enrollment = EnrollmentUjian::with([
            'sesiRuangan.jadwalUjians.mapel'
        ])->find($enrollmentId);

        if (!$enrollment) {
            return redirect()->route('siswa.dashboard')->with('error', 'Enrollment tidak ditemukan.');
        }

        // Get the requested jadwal ujian or the first one
        if ($jadwalId) {
            $jadwalUjian = $enrollment->sesiRuangan->jadwalUjians()
                ->where('jadwal_ujian.id', $jadwalId) // Specify the table name to avoid ambiguity
                ->first();
        } else {
            $jadwalUjian = $enrollment->sesiRuangan->jadwalUjians()->first();
        }

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
            'aktifkan_auto_logout' => $jadwalUjian->aktifkan_auto_logout ?? true, // Default to true for backward compatibility
        ];

        // Get or create hasil ujian record
        $hasilUjian = HasilUjian::firstOrCreate([
            'siswa_id' => $siswa->id,
            'jadwal_ujian_id' => $jadwalUjian->id,
        ], [
            'enrollment_ujian_id' => $enrollmentId,
            'sesi_ruangan_id' => $sesiRuanganId,
            'waktu_mulai' => $now,
            'durasi_menit' => $jadwalUjian->durasi_menit,
            'jumlah_soal' => $jadwalUjian->jumlah_soal ?: 0,
            'jumlah_dijawab' => 0,
            'jumlah_benar' => 0,
            'jumlah_salah' => 0,
            'jumlah_tidak_dijawab' => $jadwalUjian->jumlah_soal ?: 0,
            'skor' => 0,
            'is_final' => false,
            'status' => 'berlangsung'
        ]);

        // Get questions from bank_soal instead of jadwal_ujian relationship
        $soals = SoalUjian::where('bank_soal_id', $jadwalUjian->bank_soal_id)
            ->where('status', 'aktif')
            ->get();

        // Update jumlah_soal with actual count if different
        $actualJumlahSoal = $soals->count();
        if ($hasilUjian->jumlah_soal != $actualJumlahSoal) {
            $hasilUjian->update([
                'jumlah_soal' => $actualJumlahSoal,
                'jumlah_tidak_dijawab' => $actualJumlahSoal - ($hasilUjian->jumlah_benar + $hasilUjian->jumlah_salah)
            ]);
        }

        // Transform questions to match view expectations
        $questions = $soals->map(function ($soal, $index) use ($jadwalUjian, $siswa) {
            $options = [];

            // Build options array from database columns, handling both text and image options
            $options['A'] = [
                'teks' => $soal->pilihan_a_teks,
                'gambar' => $soal->pilihan_a_gambar,
                'tipe' => $soal->pilihan_a_tipe ?? 'teks'
            ];

            $options['B'] = [
                'teks' => $soal->pilihan_b_teks,
                'gambar' => $soal->pilihan_b_gambar,
                'tipe' => $soal->pilihan_b_tipe ?? 'teks'
            ];

            $options['C'] = [
                'teks' => $soal->pilihan_c_teks,
                'gambar' => $soal->pilihan_c_gambar,
                'tipe' => $soal->pilihan_c_tipe ?? 'teks'
            ];

            $options['D'] = [
                'teks' => $soal->pilihan_d_teks,
                'gambar' => $soal->pilihan_d_gambar,
                'tipe' => $soal->pilihan_d_tipe ?? 'teks'
            ];

            if ($soal->pilihan_e_teks || $soal->pilihan_e_gambar) {
                $options['E'] = [
                    'teks' => $soal->pilihan_e_teks,
                    'gambar' => $soal->pilihan_e_gambar,
                    'tipe' => $soal->pilihan_e_tipe ?? 'teks'
                ];
            }

            // Handle option randomization with consistent seed per student-question
            $correctAnswerAfterShuffle = $soal->kunci_jawaban; // Default to original

            if ($jadwalUjian->acak_jawaban) {
                // Use consistent seed based on siswa_id and soal_id for reproducible randomization
                $seed = $siswa->id * 1000 + $soal->id;
                mt_srand($seed);

                $keys = array_keys($options);
                shuffle($keys);
                $shuffledOptions = [];

                foreach ($keys as $i => $key) {
                    $newKey = chr(65 + $i); // A, B, C, D, E
                    $shuffledOptions[$newKey] = $options[$key];

                    // Track the new position of the correct answer
                    if ($key === $soal->kunci_jawaban) {
                        $correctAnswerAfterShuffle = $newKey;
                    }
                }
                $options = $shuffledOptions;

                // Reset random seed
                mt_srand();
            }

            return [
                'id' => $soal->id,
                'number' => $index + 1,
                'soal' => $soal->pertanyaan,
                'tipe_soal' => $soal->tipe_soal ?? 'pilihan_ganda',
                'tipe_pertanyaan' => $soal->tipe_pertanyaan ?? 'teks',
                'options' => $options,
                'gambar_soal' => $soal->gambar_pertanyaan,
                'kunci_jawaban' => $soal->kunci_jawaban, // Original key for reference
                'kunci_jawaban_acak' => $correctAnswerAfterShuffle // Shuffled key for validation
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

        // Get violations count
        $violationsCount = \App\Models\PelanggaranUjian::where('hasil_ujian_id', $hasilUjian->id)->count();

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
            'remainingTime' => (int) $remainingTime,
            'examSettings' => $examSettings,
            'hasilUjianId' => $hasilUjian->id,
            'jadwalUjianId' => $jadwalUjian->id,
            'violations_count' => $violationsCount
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
            $jawaban = JawabanSiswa::updateOrCreate([
                'hasil_ujian_id' => $request->hasil_ujian_id,
                'soal_ujian_id' => $request->soal_ujian_id
            ], [
                'jawaban' => $request->jawaban,
                'waktu_jawab' => now()
            ]);

            // Update jumlah_dijawab in hasil_ujian
            $totalAnswered = JawabanSiswa::where('hasil_ujian_id', $request->hasil_ujian_id)
                ->whereNotNull('jawaban')
                ->count();

            // Update nilai-nilai terkait
            if ($hasilUjian->jumlah_soal > 0) {
                // Hitung benar dan salah
                $benarCount = 0;
                $salahCount = 0;

                $jawabanList = JawabanSiswa::with('soalUjian')
                    ->where('hasil_ujian_id', $request->hasil_ujian_id)
                    ->whereNotNull('jawaban')
                    ->get();

                foreach ($jawabanList as $jwb) {
                    if ($jwb->soalUjian) {
                        // Get the correct answer key considering randomization
                        $correctAnswer = $this->getCorrectAnswerForStudent(
                            $jwb->soalUjian,
                            $siswa,
                            $hasilUjian->jadwalUjian
                        );

                        if ($jwb->jawaban === $correctAnswer) {
                            $benarCount++;
                        } else {
                            $salahCount++;
                        }
                    } else {
                        $salahCount++;
                    }
                }

                // Hitung nilai sebagai persentase dari jumlah benar
                $nilai = ($benarCount / $hasilUjian->jumlah_soal) * 100;

                // Update hasil ujian
                $hasilUjian->update([
                    'jumlah_benar' => $benarCount,
                    'jumlah_salah' => $salahCount,
                    'jumlah_dijawab' => $totalAnswered,
                    'jumlah_tidak_dijawab' => $hasilUjian->jumlah_soal - $totalAnswered,
                    'nilai' => $nilai
                ]);
            } else {
                $hasilUjian->update([
                    'jumlah_dijawab' => $totalAnswered,
                    'jumlah_tidak_dijawab' => $hasilUjian->jumlah_soal - $totalAnswered
                ]);
            }
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
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Exam not found'], 404);
            }

            // Check if the exam is already submitted or finalized
            if ($hasilUjian->status !== 'berlangsung' || $hasilUjian->is_final) {
                // Log attempt to submit a completed exam
                Log::warning('Attempt to submit already completed exam', [
                    'siswa_id' => $siswa->id,
                    'hasil_ujian_id' => $hasilUjian->id,
                    'current_status' => $hasilUjian->status,
                    'is_final' => $hasilUjian->is_final
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Exam already submitted',
                    'redirect_url' => route('siswa.dashboard')
                ], 200); // Return 200 instead of 404 to avoid error message
            }

            // Calculate score
            $score = $this->calculateScore($hasilUjian);

            // Update hasil ujian dengan nilai lengkap
            $currentSesiRuanganId = $request->session()->get('current_sesi_ruangan_id');
            $waktuMulai = $hasilUjian->waktu_mulai;
            $waktuSelesai = now();
            $durasiMenit = $waktuMulai ? $waktuSelesai->diffInMinutes($waktuMulai) : $hasilUjian->jadwalUjian->durasi_menit;

            // Hitung nilai (persentase) berdasarkan jumlah_benar dibanding total soal
            $nilai = 0;
            if (isset($score['total_soal']) && $score['total_soal'] > 0) {
                $nilai = ($score['jumlah_benar'] / $score['total_soal']) * 100;
            }

            // Tentukan status lulus berdasarkan nilai > 75
            $kkm = 75; // Standar KKM
            if ($hasilUjian->jadwalUjian && isset($hasilUjian->jadwalUjian->pengaturan['kkm'])) {
                $kkm = $hasilUjian->jadwalUjian->pengaturan['kkm'];
            }
            $lulus = $nilai >= $kkm;

            $hasilUjian->update([
                'waktu_selesai' => $waktuSelesai,
                'skor' => $score['total_skor'],
                'jumlah_benar' => $score['jumlah_benar'],
                'jumlah_salah' => $score['jumlah_salah'],
                'jumlah_soal' => $score['total_soal'],
                'jumlah_dijawab' => $score['jumlah_dijawab'],
                'jumlah_tidak_dijawab' => $score['jumlah_tidak_dijawab'],
                'durasi_menit' => $durasiMenit,
                'nilai' => $nilai,
                'lulus' => $lulus,
                'sesi_ruangan_id' => $currentSesiRuanganId,
                'status' => 'selesai',
                'is_final' => true
            ]);            // Update enrollment status
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
                'redirect_url' => route('ujian.result', ['hasil' => $hasilUjian->id])
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting exam', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            $errorMessage = config('app.debug') ? $e->getMessage() : 'Failed to submit exam';
            return response()->json(['success' => false, 'message' => $errorMessage], 500);
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

        // Gunakan soals() sebagai pengganti soalUjians()
        $soalUjians = $jadwalUjian->bankSoal ? SoalUjian::where('bank_soal_id', $jadwalUjian->bank_soal_id)->get() : collect();

        // Ambil jawaban siswa dan konversi ke key-value untuk pencarian lebih cepat
        $jawabanSiswas = $hasilUjian->jawabanSiswas()->get()->keyBy('soal_ujian_id');

        // Hitung jawaban yang benar-benar dijawab (tidak null jawaban)
        $jawabanDijawab = $hasilUjian->jawabanSiswas()
            ->whereNotNull('jawaban')
            ->count();

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalSkor = 0;

        foreach ($soalUjians as $soal) {
            $jawaban = $jawabanSiswas->get($soal->id);

            if ($jawaban && $jawaban->jawaban) {
                // Get the correct answer key considering randomization
                $correctAnswer = $this->getCorrectAnswerForStudent($soal, $hasilUjian->siswa, $jadwalUjian);

                if ($jawaban->jawaban === $correctAnswer) {
                    $jumlahBenar++;
                    $totalSkor += $soal->bobot ?? 1;
                } else {
                    $jumlahSalah++;
                }
            } else {
                // Soal tidak dijawab, tidak dihitung sebagai salah
                // Hanya dihitung dalam "tidak dijawab"
            }
        }

        // Jumlah soal yang benar-benar dijawab (tidak termasuk yang tidak dijawab)
        $jumlahDijawab = $jawabanDijawab;

        // Jumlah soal yang salah hanya yang dijawab tapi salah
        $jumlahSalah = $jumlahDijawab - $jumlahBenar;

        // Total soal adalah jumlah soal dari bank soal
        $totalSoal = $soalUjians->count();

        // Persentase dihitung dari jumlah benar dibagi total soal
        $persentase = $totalSoal > 0 ? ($jumlahBenar / $totalSoal) * 100 : 0;

        return [
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'jumlah_dijawab' => $jumlahDijawab,
            'jumlah_tidak_dijawab' => $totalSoal - $jumlahDijawab,
            'total_skor' => $totalSkor,
            'total_soal' => $totalSoal,
            'persentase' => $persentase
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

    /**
     * Handle automatic logout when student changes tabs or minimizes browser
     */
    public function logoutFromExam(Request $request)
    {
        try {
            $siswa = Auth::guard('siswa')->user();

            $request->validate([
                'hasil_ujian_id' => 'required|exists:hasil_ujian,id',
                'reason' => 'required|string'
            ]);

            // Verify hasil ujian belongs to current siswa
            $hasilUjian = HasilUjian::where('id', $request->hasil_ujian_id)
                ->where('siswa_id', $siswa->id)
                ->first();

            if (!$hasilUjian) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check if this is a page refresh during navigation (which should not count as a violation)
            if ($request->reason === 'refresh' && $request->has('is_navigation') && $request->input('is_navigation')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Navigation refresh, not counted as violation',
                    'violations_count' => $hasilUjian->violations_count ?? 0,
                    'continue_exam' => true
                ]);
            }

            // Check if there's already a recent violation of the same type (within the last 60 seconds)
            $recentViolation = \App\Models\PelanggaranUjian::where('hasil_ujian_id', $hasilUjian->id)
                ->where('jenis_pelanggaran', $request->reason)
                ->where('waktu_pelanggaran', '>', now()->subSeconds(60))
                ->first();

            if ($recentViolation) {
                // Don't create a new violation record, but update the count for the client
                return response()->json([
                    'success' => true,
                    'message' => 'Pelanggaran terdeteksi. Pastikan untuk tidak berpindah tab atau meminimalkan browser selama ujian.',
                    'violations_count' => $hasilUjian->violations_count ?? 0,
                    'continue_exam' => true
                ]);
            }

            // Log the incident
            Log::warning('Student exam integrity violation', [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama,
                'hasil_ujian_id' => $hasilUjian->id,
                'jadwal_ujian_id' => $hasilUjian->jadwal_ujian_id,
                'reason' => $request->reason,
                'time' => now(),
                'ip' => $request->ip()
            ]);

            // Record the violation in the pelanggaran_ujian table
            $deskripsi = '';
            switch ($request->reason) {
                case 'tab_switching':
                    $deskripsi = 'Berpindah tab/meminimalkan browser saat ujian berlangsung';
                    break;
                case 'refresh':
                    $deskripsi = 'Me-refresh halaman ujian tanpa izin';
                    break;
                default:
                    $deskripsi = 'Melakukan pelanggaran integritas ujian: ' . $request->reason;
            }

            // Create the violation record
            \App\Models\PelanggaranUjian::create([
                'siswa_id' => $siswa->id,
                'hasil_ujian_id' => $hasilUjian->id,
                'jadwal_ujian_id' => $hasilUjian->jadwal_ujian_id,
                'sesi_ruangan_id' => $hasilUjian->sesi_ruangan_id,
                'jenis_pelanggaran' => $request->reason,
                'deskripsi' => $deskripsi,
                'waktu_pelanggaran' => now(),
                'is_dismissed' => false,
                'is_finalized' => false
            ]);

            // Count total violations for this exam
            $violationsCount = \App\Models\PelanggaranUjian::where('hasil_ujian_id', $hasilUjian->id)
                ->count();

            // Update violations count in hasil ujian
            $hasilUjian->update([
                'violations_count' => $violationsCount
            ]);

            // Update enrollment with violation note but keep it active
            $enrollment = EnrollmentUjian::where('siswa_id', $siswa->id)
                ->where('sesi_ruangan_id', $hasilUjian->sesi_ruangan_id)
                ->first();

            if ($enrollment) {
                $currentNote = $enrollment->catatan ?? '';
                $enrollment->update([
                    'catatan' => trim($currentNote . "\n" . 'Pelanggaran terdeteksi: ' . $request->reason . ' pada ' . now()->format('Y-m-d H:i:s'))
                ]);
            }

            // Determine if the student should be logged out based on violation count
            // After 3 violations, we'll force logout but let them log back in to continue
            $maxViolations = 3;
            $continueExam = $violationsCount < $maxViolations;
            $shouldForceLogout = $violationsCount >= $maxViolations;

            $message = 'Pelanggaran dicatat' . ($shouldForceLogout ?
                '. Anda akan dikeluarkan dari ujian karena melakukan pelanggaran berulang kali. Anda dapat login kembali untuk melanjutkan ujian.' :
                ', Anda masih dapat melanjutkan ujian.');

            return response()->json([
                'success' => true,
                'message' => $message,
                'violations_count' => $violationsCount,
                'continue_exam' => $continueExam,
                'force_logout' => $shouldForceLogout,
                'logout_url' => route('siswa.logout')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in logout from exam', [
                'error' => $e->getMessage(),
                'siswa_id' => $siswa->id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json(['error' => 'Failed to process logout'], 500);
        }
    }

    /**
     * Get the correct answer key for a student considering randomization
     */
    private function getCorrectAnswerForStudent($soal, $siswa, $jadwalUjian)
    {
        // If answer randomization is not enabled, return original key
        if (!$jadwalUjian->acak_jawaban) {
            return $soal->kunci_jawaban;
        }

        // Build original options
        $options = [];
        $options['A'] = $soal->pilihan_a_teks;
        $options['B'] = $soal->pilihan_b_teks;
        $options['C'] = $soal->pilihan_c_teks;
        $options['D'] = $soal->pilihan_d_teks;
        if ($soal->pilihan_e_teks || $soal->pilihan_e_gambar) {
            $options['E'] = $soal->pilihan_e_teks;
        }

        // Apply the same randomization logic as used in exam display
        $seed = $siswa->id * 1000 + $soal->id;
        mt_srand($seed);

        $keys = array_keys($options);
        shuffle($keys);

        // Find where the original correct answer ended up
        foreach ($keys as $i => $originalKey) {
            if ($originalKey === $soal->kunci_jawaban) {
                $newKey = chr(65 + $i); // A, B, C, D, E
                mt_srand(); // Reset seed
                return $newKey;
            }
        }

        mt_srand(); // Reset seed
        return $soal->kunci_jawaban; // Fallback to original
    }
}
