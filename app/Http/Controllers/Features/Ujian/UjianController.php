<?php

namespace App\Http\Controllers\Features\Ujian;

use App\Http\Controllers\Controller;
use App\Models\HasilUjian;
use App\Models\Soal;
use App\Services\UjianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjianController extends Controller
{
    protected $ujianService;

    public function __construct(UjianService $ujianService)
    {
        $this->ujianService = $ujianService;
        $this->middleware('auth:siswa');
        $this->middleware('ujian.active')->except(['finish', 'result']);
    }

    /**
     * Start the exam
     */
    public function start()
    {
        $hasilUjian = HasilUjian::findOrFail(session('hasil_ujian_id'));
        $jadwalUjian = $hasilUjian->jadwalUjian;
        $sesiRuangan = $hasilUjian->sesiRuangan;

        return view('ujian.start', compact('hasilUjian', 'jadwalUjian', 'sesiRuangan'));
    }

    /**
     * Display the question page
     */
    public function showSoal($soalIndex = 0)
    {
        $hasilUjian = HasilUjian::findOrFail(session('hasil_ujian_id'));

        // Convert to zero-based index if not already
        $soalIndex = (int) $soalIndex;

        if ($hasilUjian->is_final) {
            return redirect()->route('ujian.finish');
        }

        // Get all soal IDs from jawaban_siswa
        $jawabanSiswa = $hasilUjian->jawaban_siswa;

        if ($soalIndex < 0 || $soalIndex >= count($jawabanSiswa)) {
            return redirect()->route('ujian.soal', 0);
        }

        // Get current soal details
        $currentSoal = $jawabanSiswa[$soalIndex];
        $soalId = $currentSoal['soal_id'];
        $soal = Soal::findOrFail($soalId);

        // Calculate progress
        $totalSoal = count($jawabanSiswa);
        $progress = [
            'current' => $soalIndex + 1,
            'total' => $totalSoal,
            'percentage' => ($soalIndex + 1) / $totalSoal * 100,
            'terjawab' => collect($jawabanSiswa)->filter(fn($j) => !is_null($j['jawaban']))->count(),
            'belum_terjawab' => collect($jawabanSiswa)->filter(fn($j) => is_null($j['jawaban']))->count()
        ];

        // Calculate time remaining
        $durasiUjian = session('durasi'); // in minutes
        $waktuMulai = session('waktu_mulai');
        $waktuSelesai = \Carbon\Carbon::parse($waktuMulai)->addMinutes($durasiUjian);
        $sekarang = \Carbon\Carbon::now();
        $sisaWaktu = $sekarang->diffInSeconds($waktuSelesai, false);

        return view('ujian.soal', compact(
            'hasilUjian',
            'soal',
            'soalIndex',
            'progress',
            'sisaWaktu',
            'currentSoal'
        ));
    }

    /**
     * Save student's answer
     */
    public function saveJawaban(Request $request)
    {
        $request->validate([
            'soal_id' => 'required|exists:soal,id',
            'jawaban' => 'nullable|string|max:1',
            'soal_index' => 'required|integer|min:0',
            'action' => 'required|in:save,save_next,save_prev,finish'
        ]);

        $hasilUjian = HasilUjian::findOrFail(session('hasil_ujian_id'));

        if ($hasilUjian->is_final) {
            return redirect()->route('ujian.finish');
        }

        // Save the answer
        $this->ujianService->saveJawaban(
            $hasilUjian,
            $request->soal_id,
            $request->jawaban
        );

        // Determine next action
        switch ($request->action) {
            case 'finish':
                return redirect()->route('ujian.confirm_finish');

            case 'save_next':
                $nextIndex = $request->soal_index + 1;
                $totalSoal = count($hasilUjian->jawaban_siswa);

                if ($nextIndex >= $totalSoal) {
                    $nextIndex = $totalSoal - 1;
                }

                return redirect()->route('ujian.soal', $nextIndex);

            case 'save_prev':
                $prevIndex = $request->soal_index - 1;

                if ($prevIndex < 0) {
                    $prevIndex = 0;
                }

                return redirect()->route('ujian.soal', $prevIndex);

            default:
                return redirect()->route('ujian.soal', $request->soal_index);
        }
    }

    /**
     * Show confirmation page before finishing the exam
     */
    public function confirmFinish()
    {
        $hasilUjian = HasilUjian::findOrFail(session('hasil_ujian_id'));

        if ($hasilUjian->is_final) {
            return redirect()->route('ujian.finish');
        }

        $jawabanSiswa = $hasilUjian->jawaban_siswa;
        $totalSoal = count($jawabanSiswa);
        $terjawab = collect($jawabanSiswa)->filter(fn($j) => !is_null($j['jawaban']))->count();
        $belumTerjawab = $totalSoal - $terjawab;

        return view('ujian.confirm_finish', compact(
            'hasilUjian',
            'totalSoal',
            'terjawab',
            'belumTerjawab'
        ));
    }

    /**
     * Finish the exam
     */
    public function finish(Request $request)
    {
        // If no active exam, redirect to dashboard
        if (!session('ujian_aktif') || !session('hasil_ujian_id')) {
            return redirect()->route('siswa.dashboard');
        }

        $hasilUjian = HasilUjian::findOrFail(session('hasil_ujian_id'));

        if (!$hasilUjian->is_final) {
            // Finalize the exam
            $this->ujianService->finalizeUjian($hasilUjian);
        }

        // Clear exam session data
        $request->session()->forget([
            'ujian_aktif',
            'waktu_mulai',
            'durasi'
        ]);

        return redirect()->route('ujian.result', $hasilUjian->id);
    }

    /**
     * Show the exam result
     */
    public function result($hasilId)
    {
        $hasilUjian = HasilUjian::findOrFail($hasilId);

        // Make sure only the owner can see their result
        if ($hasilUjian->siswa_id != Auth::guard('siswa')->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('ujian.result', compact('hasilUjian'));
    }
}
