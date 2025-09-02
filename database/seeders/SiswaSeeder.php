<?php
// filepath: database\seeders\SiswaSeeder.php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        echo "👨‍🎓 Seeding Siswa data...\n";

        // Get all kelas IDs for reference
        $kelasIds = Kelas::pluck('id', 'nama_kelas')->toArray();

        if (empty($kelasIds)) {
            echo "❌ No Kelas found! Please run the KelasSeeder first.\n";
            return;
        }

        $count = 0;
        $totalToCreate = 100; // Create 100 students

        // Create sample students with incrementing NIS numbers
        for ($i = 1; $i <= $totalToCreate; $i++) {
            // Generate a NIS (Student ID Number) - Format: Year + Sequential Number
            $nis = '2025' . str_pad($i, 4, '0', STR_PAD_LEFT); // e.g., 20250001

            // Select random class
            $kelasNames = array_keys($kelasIds);
            $randomKelasName = $kelasNames[array_rand($kelasNames)];
            $kelasId = $kelasIds[$randomKelasName];

            // Create the student
            Siswa::updateOrCreate(
                ['nis' => $nis],
                [
                    'idyayasan' => 'S' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'nama' => 'Siswa ' . $nis,
                    'kelas_id' => $kelasId,
                    'email' => 'siswa' . $i . '@example.com',
                    'password' => Hash::make('password'), // Default password
                    'status_pembayaran' => rand(0, 1) ? 'Lunas' : 'Belum Lunas',
                    'rekomendasi' => rand(0, 5) == 5 ? 'ya' : 'tidak',
                    'catatan_rekomendasi' => rand(0, 5) == 5 ? 'Catatan rekomendasi untuk siswa ' . $i : null
                ]
            );
            $count++;
        }

        echo "✅ {$count} siswa seeded successfully!\n";
    }
}
