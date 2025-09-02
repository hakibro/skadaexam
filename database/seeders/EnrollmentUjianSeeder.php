<?php

namespace Database\Seeders;

use App\Models\EnrollmentUjian;
use App\Models\SesiRuangan;
use App\Models\SesiRuanganSiswa;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EnrollmentUjianSeeder extends Seeder
{
    public function run(): void
    {
        echo "🎫 Seeding Enrollment Ujian data...\n";

        // Get all sesi ruangan
        $sesiRuanganList = SesiRuangan::all();
        if ($sesiRuanganList->isEmpty()) {
            echo "❌ No Sesi Ruangan found! Please run the SesiRuanganSeeder first.\n";
            return;
        }

        // Get all siswa
        $siswaList = Siswa::all();
        if ($siswaList->isEmpty()) {
            echo "❌ No Siswa found! Please run the SiswaSeeder first.\n";
            return;
        }

        $count = 0;
        $sesiRuanganSiswaCount = 0;
        $now = Carbon::now();
        $statusOptions = ['enrolled', 'completed', 'cancelled'];

        foreach ($sesiRuanganList as $sesiRuangan) {
            // Get max capacity from room
            $maxCapacity = $sesiRuangan->ruangan->kapasitas ?? 30;
            $enrollmentCount = rand(min(10, $maxCapacity - 5), min(25, $maxCapacity));
            $selectedStudents = $siswaList->random($enrollmentCount);

            foreach ($selectedStudents as $student) {
                // Skip if already enrolled in this sesi ruangan
                $existingEnrollment = EnrollmentUjian::where('siswa_id', $student->id)
                    ->where('sesi_ruangan_id', $sesiRuangan->id)
                    ->first();
                if ($existingEnrollment) {
                    continue;
                }

                $status = $statusOptions[array_rand($statusOptions)];

                $token = strtoupper(Str::random(6));
                $tokenCreatedAt = $now->copy()->subHours(rand(0, 72));
                $tokenUsedAt = ($status === 'completed') ? $tokenCreatedAt->copy()->addMinutes(rand(5, 30)) : null;

                $startTime = $tokenUsedAt;
                $endTime = ($status === 'completed' && $startTime) ?
                    $startTime->copy()->addMinutes(rand(15, 90)) : null;

                // Get berita acara to find potential jadwal ujian info
                $beritaAcara = \App\Models\BeritaAcaraUjian::where('sesi_ruangan_id', $sesiRuangan->id)->first();

                // Create enrollment
                EnrollmentUjian::create([
                    'siswa_id' => $student->id,
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'status_enrollment' => $status,
                    'token_login' => $token,
                    'token_dibuat_pada' => $tokenCreatedAt,
                    'token_digunakan_pada' => $tokenUsedAt,
                    'waktu_mulai_ujian' => $startTime,
                    'waktu_selesai_ujian' => $endTime,
                    'last_login_at' => $tokenUsedAt,
                    'last_logout_at' => $endTime,
                    'catatan' => rand(0, 5) > 4 ? 'Catatan untuk siswa ini' : null,
                ]);

                // Create corresponding sesi ruangan siswa record
                $attendanceStatus = match ($status) {
                    'completed' => 'hadir',
                    'cancelled' => 'tidak_hadir',
                    default => rand(0, 10) > 3 ? 'hadir' : 'tidak_hadir'
                };

                SesiRuanganSiswa::create([
                    'sesi_ruangan_id' => $sesiRuangan->id,
                    'siswa_id' => $student->id,
                    'status' => $attendanceStatus,
                ]);

                $count++;
                $sesiRuanganSiswaCount++;
            }
        }

        echo "✅ {$count} enrollment ujian seeded successfully!\n";
        echo "✅ {$sesiRuanganSiswaCount} sesi ruangan siswa records seeded successfully!\n";
    }
}
