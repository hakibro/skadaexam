<?php

namespace Database\Seeders;

use App\Models\HasilUjian;
use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class HasilUjianSeeder extends Seeder
{
    public function run(): void
    {
        echo "📊 Seeding Hasil Ujian data...\n";

        // Only create results for completed enrollments
        $completedEnrollments = EnrollmentUjian::where('status_enrollment', 'completed')
            ->whereNotNull('waktu_selesai_ujian')
            ->get();

        if ($completedEnrollments->isEmpty()) {
            echo "❌ No completed enrollments found! Results seeder skipped.\n";
            return;
        }

        $count = 0;

        foreach ($completedEnrollments as $enrollment) {
            // Skip if result already exists
            if (HasilUjian::where('enrollment_ujian_id', $enrollment->id)->exists()) {
                continue;
            }

            // Get sesi ruangan
            $sesiRuangan = $enrollment->sesiRuangan;
            if (!$sesiRuangan) {
                continue;
            }

            // Get jadwal ujian id from enrollment directly
            $jadwalUjianId = $enrollment->jadwal_ujian_id;
            if (!$jadwalUjianId) {
                continue;
            }

            $jadwalUjian = JadwalUjian::find($jadwalUjianId);
            if (!$jadwalUjian) {
                continue;
            }

            // Generate realistic exam results
            $jumlahSoal = $jadwalUjian->jumlah_soal ?: rand(20, 50);
            $jumlahBenar = rand(ceil($jumlahSoal * 0.4), $jumlahSoal); // 40-100% correct
            $jumlahSalah = rand(0, $jumlahSoal - $jumlahBenar);
            $jumlahTidakDijawab = $jumlahSoal - $jumlahBenar - $jumlahSalah;
            $skor = $jumlahBenar; // Simple scoring: 1 point per correct answer

            // Generate sample answer data
            $jawabanSiswa = [];
            for ($i = 1; $i <= $jumlahSoal; $i++) {
                $isAnswered = $i <= ($jumlahBenar + $jumlahSalah);
                $isCorrect = $isAnswered && $i <= $jumlahBenar;

                $jawabanSiswa[] = [
                    'soal_id' => $i,
                    'jawaban' => $isAnswered ? ['A', 'B', 'C', 'D', 'E'][rand(0, 4)] : null,
                    'is_correct' => $isAnswered ? $isCorrect : null,
                ];
            }

            HasilUjian::create([
                'sesi_ruangan_id' => $enrollment->sesi_ruangan_id,
                'enrollment_ujian_id' => $enrollment->id,
                'siswa_id' => $enrollment->siswa_id,
                'jadwal_ujian_id' => $jadwalUjianId,
                'skor' => $skor,
                'jumlah_benar' => $jumlahBenar,
                'jumlah_salah' => $jumlahSalah,
                'jumlah_tidak_dijawab' => $jumlahTidakDijawab,
                'jumlah_soal' => $jumlahSoal,
                'jawaban_siswa' => $jawabanSiswa,
                'is_final' => true,
                'waktu_mulai' => $enrollment->waktu_mulai_ujian,
                'waktu_selesai' => $enrollment->waktu_selesai_ujian,
            ]);

            $count++;
        }

        echo "✅ {$count} hasil ujian seeded successfully!\n";
    }
}
