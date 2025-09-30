<?php

namespace App\Services;

use App\Models\EnrollmentUjian;
use App\Models\HasilUjian;
use App\Models\SesiRuangan;
use App\Models\Siswa;
use App\Models\Soal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class UjianService
{
    /**
     * Start an exam for a student with a valid token
     *
     * @param string $token
     * @return HasilUjian|false
     */
    public function startUjian($token)
    {
        // Validate the token
        $enrollment = EnrollmentUjian::where('token', $token)
            ->where('token_expires_at', '>', Carbon::now())
            ->where('token_used_at', null)
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Check if session is still active
        if (!$enrollment->sesiRuangan->isActive()) {
            return false;
        }

        DB::beginTransaction();
        try {
            // Mark token as used
            $enrollment->useToken();

            // Check if there's an existing result that's not finalized
            $existingHasil = HasilUjian::where('enrollment_ujian_id', $enrollment->id)
                ->where('is_final', false)
                ->first();

            if ($existingHasil) {
                DB::commit();
                return $existingHasil;
            }

            // Get soal from bank soal
            $soalList = $this->getSoalForUjian($enrollment->jadwalUjian);

            if (empty($soalList)) {
                throw new Exception("No questions available for this exam");
            }

            // Create a new hasil ujian record
            $hasilUjian = new HasilUjian([
                'enrollment_ujian_id' => $enrollment->id,
                'siswa_id' => $enrollment->siswa_id,
                'sesi_ujian_id' => $enrollment->sesi_ujian_id,
                'jadwal_ujian_id' => $enrollment->jadwal_ujian_id,
                'jumlah_soal' => count($soalList),
                'jumlah_benar' => 0,
                'jumlah_salah' => 0,
                'jumlah_tidak_dijawab' => count($soalList),
                'skor' => 0,
                'is_final' => false,
                'waktu_mulai' => Carbon::now(),
                'jawaban_siswa' => array_map(function ($soal) {
                    return [
                        'soal_id' => $soal['id'],
                        'jawaban' => null,
                        'is_correct' => null
                    ];
                }, $soalList)
            ]);

            $hasilUjian->save();
            DB::commit();

            return $hasilUjian;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error starting exam: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get questions for an exam
     *
     * @param mixed $jadwalUjian
     * @return array
     */
    protected function getSoalForUjian($jadwalUjian)
    {
        // This is a simplified version - should be expanded based on your bank soal structure
        $bankSoalId = $jadwalUjian->bank_soal_id;

        if (!$bankSoalId) {
            return [];
        }

        $soalList = Soal::where('bank_soal_id', $bankSoalId)
            ->where('status', 'active')
            ->select(['id', 'pertanyaan', 'pilihan', 'kunci_jawaban', 'bobot'])
            ->get()
            ->toArray();

        // Shuffle questions if needed based on jadwal settings
        if ($jadwalUjian->acak_soal) {
            shuffle($soalList);
        }

        // Limit number of questions if specified
        if ($jadwalUjian->jumlah_soal > 0 && count($soalList) > $jadwalUjian->jumlah_soal) {
            $soalList = array_slice($soalList, 0, $jadwalUjian->jumlah_soal);
        }

        return $soalList;
    }

    /**
     * Save student's answer
     *
     * @param HasilUjian $hasilUjian
     * @param int $soalId
     * @param string|null $jawaban
     * @return bool
     */
    public function saveJawaban(HasilUjian $hasilUjian, $soalId, $jawaban)
    {
        if ($hasilUjian->is_final) {
            return false; // Cannot modify after finalized
        }

        $jawaban_siswa = $hasilUjian->jawaban_siswa;
        $found = false;

        foreach ($jawaban_siswa as &$item) {
            if ($item['soal_id'] == $soalId) {
                $found = true;

                // Get the question to check if answer is correct
                $soal = Soal::find($soalId);

                if (!$soal) {
                    return false;
                }

                $item['jawaban'] = $jawaban;
                $item['is_correct'] = ($soal->kunci_jawaban === $jawaban);
                break;
            }
        }

        if (!$found) {
            return false;
        }

        $hasilUjian->jawaban_siswa = $jawaban_siswa;
        $hasilUjian->calculateResults();

        return true;
    }

    /**
     * Finalize the exam and calculate the final score
     *
     * @param HasilUjian $hasilUjian
     * @return bool
     */
    public function finalizeUjian(HasilUjian $hasilUjian)
    {
        if ($hasilUjian->is_final) {
            return false; // Already finalized
        }

        return $hasilUjian->finalize();
    }
    /**
     * Auto-submit a HasilUjian when exam time is over
     */
    public function autoSubmitHasilUjian(HasilUjian $hasilUjian)
    {
        if ($hasilUjian->is_final) {
            return;
        }

        // Hitung skor
        $score = $this->calculateScore($hasilUjian);

        // Nilai dalam persentase
        $nilai = $hasilUjian->jumlah_soal > 0 ? ($score['jumlah_benar'] / $score['total_soal']) * 100 : 0;

        // Standar KKM (default 75, bisa ambil dari pengaturan jadwal)
        $kkm = $hasilUjian->jadwalUjian->pengaturan['kkm'] ?? 75;
        $lulus = $nilai >= $kkm;

        // Update hasil ujian
        $hasilUjian->update([
            'waktu_selesai' => now(),
            'skor' => $score['total_skor'],
            'jumlah_benar' => $score['jumlah_benar'],
            'jumlah_salah' => $score['jumlah_salah'],
            'jumlah_dijawab' => $score['jumlah_dijawab'],
            'jumlah_tidak_dijawab' => $score['jumlah_tidak_dijawab'],
            'nilai' => $nilai,
            'lulus' => $lulus,
            'status' => 'selesai',
            'is_final' => true
        ]);

        // Update enrollment jika ada
        $enrollment = EnrollmentUjian::where('id', $hasilUjian->enrollment_ujian_id)->first();
        if ($enrollment) {
            $enrollment->update([
                'status_enrollment' => 'completed',
                'waktu_selesai_ujian' => now()
            ]);
        }

        Log::info('Auto-submitted exam', [
            'hasil_ujian_id' => $hasilUjian->id,
            'siswa_id' => $hasilUjian->siswa_id
        ]);
    }

    /**
     * Reuse existing calculateScore() logic
     */
    private function calculateScore(HasilUjian $hasilUjian)
    {
        // Ambil logika yang sudah ada di controller sebelumnya
        $jadwalUjian = $hasilUjian->jadwalUjian;
        $soals = $jadwalUjian->bankSoal ? $jadwalUjian->bankSoal->soals : collect();
        $jawabanSiswas = $hasilUjian->jawabanSiswas()->get()->keyBy('soal_ujian_id');

        $jumlahBenar = 0;
        $jumlahSalah = 0;
        $totalSkor = 0;
        $jumlahDijawab = 0;

        foreach ($soals as $soal) {
            $jawaban = $jawabanSiswas->get($soal->id);
            if ($jawaban && $jawaban->jawaban) {
                $jumlahDijawab++;
                $correctAnswer = $this->getCorrectAnswerForStudent($soal, $hasilUjian->siswa, $jadwalUjian);
                if ($jawaban->jawaban === $correctAnswer) {
                    $jumlahBenar++;
                    $totalSkor += $soal->bobot ?? 1;
                } else {
                    $jumlahSalah++;
                }
            }
        }
        Log::info('Hasil Ujian', [
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'jumlah_dijawab' => $jumlahDijawab,
            'jumlah_tidak_dijawab' => $soals->count() - $jumlahDijawab,
            'total_skor' => $totalSkor,
            'total_soal' => $soals->count()
        ]);
        return [
            'jumlah_benar' => $jumlahBenar,
            'jumlah_salah' => $jumlahSalah,
            'jumlah_dijawab' => $jumlahDijawab,
            'jumlah_tidak_dijawab' => $soals->count() - $jumlahDijawab,
            'total_skor' => $totalSkor,
            'total_soal' => $soals->count()
        ];
    }

    /**
     * Reuse randomization logic
     */
    private function getCorrectAnswerForStudent($soal, $siswa, $jadwalUjian)
    {
        if (!$jadwalUjian->acak_jawaban) {
            return $soal->kunci_jawaban;
        }

        $options = [];
        foreach (['A', 'B', 'C', 'D', 'E'] as $key) {
            $teks = $soal->{"pilihan_{$key}_teks"};
            $gambar = $soal->{"pilihan_{$key}_gambar"};
            if ($teks || $gambar) $options[$key] = $teks;
        }

        $seed = $siswa->id * 1000 + $soal->id;
        mt_srand($seed);
        $keys = array_keys($options);
        shuffle($keys);

        foreach ($keys as $i => $originalKey) {
            if ($originalKey === $soal->kunci_jawaban) {
                mt_srand(); // reset
                return chr(65 + $i);
            }
        }

        mt_srand();
        return $soal->kunci_jawaban;
    }
}
