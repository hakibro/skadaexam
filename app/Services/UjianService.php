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
}
