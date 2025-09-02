<?php
// filepath: database\seeders\JadwalUjianDummySeeder.php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\JadwalUjian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JadwalUjianDummySeeder extends Seeder
{
    public function run(): void
    {
        echo "📅 Seeding Jadwal Ujian Dummy data...\n";

        // Get bank soal for reference
        $bankSoalList = BankSoal::all();
        if ($bankSoalList->isEmpty()) {
            echo "❌ No Bank Soal found! Please run the BankSoalSeeder first.\n";
            return;
        }

        // Get a user for created_by
        $user = User::first();
        if (!$user) {
            echo "❌ No User found! Please run the UserSeeder first.\n";
            return;
        }

        // Check if we already have jadwal ujian
        $existingCount = JadwalUjian::count();
        if ($existingCount > 0) {
            echo "⏩ Skipping jadwal ujian seeding - {$existingCount} jadwal ujian already exist\n";
            return;
        }

        $count = 0;
        $examTypes = ['UH', 'UTS', 'UAS', 'Remedial', 'Quiz'];

        // Create jadwal ujian for each bank soal
        foreach ($bankSoalList as $bankSoal) {
            $examType = $examTypes[array_rand($examTypes)];
            $startDate = Carbon::now()->addDays(rand(1, 30));
            $kodeUjian = 'U' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

            $jadwalUjian = new JadwalUjian([
                'judul' => $examType . ' ' . $bankSoal->mapel->nama_mapel . ' ' . $bankSoal->tingkat,
                'mapel_id' => $bankSoal->mapel_id,
                'tanggal_mulai' => $startDate->format('Y-m-d H:i:s'),
                'durasi_menit' => rand(3, 12) * 15, // 45-180 minutes in 15-min increments
                'deskripsi' => 'Ujian ' . $examType . ' untuk mata pelajaran ' . $bankSoal->mapel->nama_mapel . ' kelas ' . $bankSoal->tingkat,
                'status' => 'active',
                'tampilkan_hasil' => (bool)rand(0, 1),
                'jumlah_soal' => min(rand(5, 10), $bankSoal->soals->count() ?: 10),
                'kelas_target' => [rand(1, 3), rand(4, 5)],  // Random kelas IDs for demonstration
                'bank_soal_id' => $bankSoal->id,
                'created_by' => $user->id,
                'kode_ujian' => $kodeUjian,
                'jenis_ujian' => strtolower($examType),
                'acak_soal' => (bool)rand(0, 1),
                'acak_jawaban' => (bool)rand(0, 1)
            ]);

            $jadwalUjian->save();
            $count++;
        }

        echo "✅ {$count} jadwal ujian seeded successfully!\n";
    }
}
