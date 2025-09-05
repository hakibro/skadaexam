<?php
// filepath: database\seeders\JadwalUjianSeeder.php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\JadwalUjian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;


class JadwalUjianSeeder extends Seeder
{
    public function run(): void
    {
        echo "📅 Seeding Jadwal Ujian data...\n";

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

        $count = 0;
        $examTypes = ['UH', 'UTS', 'UAS', 'Remedial', 'Quiz'];

        /// Create 5 jadwal ujian for each bank soal
        foreach ($bankSoalList as $bankSoal) {
            $examType  = $examTypes[array_rand($examTypes)];
            $startDate = Carbon::now()->addDays(rand(1, 30));

            $jadwalUjian = JadwalUjian::create([
                'judul'           => $examType . ' ' . $bankSoal->mapel->nama . ' ' . $bankSoal->tingkat,
                'mapel_id'        => $bankSoal->mapel_id,
                'tanggal'   => $startDate,
                'durasi_menit'    => rand(3, 12) * 15, // 45–180 menit
                'deskripsi'       => 'Ujian ' . $examType . ' untuk mata pelajaran ' . $bankSoal->mapel->nama . ' kelas ' . $bankSoal->tingkat,
                'status'          => ['draft', 'aktif', 'selesai', 'dibatalkan'][array_rand(['draft', 'aktif', 'selesai', 'dibatalkan'])],
                'tampilkan_hasil' => (bool)rand(0, 1),
                'jumlah_soal'     => min(rand(10, 20), $bankSoal->soals->count()),
                'bank_soal_id'    => $bankSoal->id,
                'created_by'      => $user->id,
                'kelas_target'    => json_encode([]),

                // kolom tambahan wajib
                'kode_ujian'      => strtoupper(Str::random(6)), // misal: ABX92F
                'jenis_ujian'     => $examType,                  // sesuai jenis (UH/UTS/UAS/Quiz)
                'acak_soal'       => (bool)rand(0, 1),
                'acak_jawaban'    => (bool)rand(0, 1),
            ]);

            $count++;
        }


        echo "✅ {$count} jadwal ujian seeded successfully!\n";
    }
}
