<?php
// filepath: database\seeders\BankSoalDummySeeder.php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\User;
use App\Models\Soal;
use Illuminate\Database\Seeder;

class BankSoalDummySeeder extends Seeder
{
    public function run(): void
    {
        echo "📝 Seeding Bank Soal Dummy data...\n";

        // Get available mapel IDs
        $mapelIds = Mapel::pluck('id')->toArray();
        if (empty($mapelIds)) {
            echo "❌ No Mapel found! Please run the MapelSeeder first.\n";
            return;
        }

        // Get a user for created_by
        $user = User::first();
        if (!$user) {
            echo "❌ No User found! Please run the UserSeeder first.\n";
            return;
        }

        // Check if we already have bank soal
        $existingCount = BankSoal::count();
        if ($existingCount > 0) {
            echo "⏩ Skipping bank soal seeding - {$existingCount} bank soal already exist\n";
            return;
        }

        $bankSoalCount = 0;
        $soalCount = 0;

        // Create bank soal for each mapel
        foreach ($mapelIds as $mapelId) {
            $mapel = Mapel::find($mapelId);
            $tingkatArray = explode(',', $mapel->tingkat);

            foreach ($tingkatArray as $tingkat) {
                $kodeBank = 'BS' . str_pad($bankSoalCount + 1, 3, '0', STR_PAD_LEFT);

                // Create a bank soal
                $bankSoal = new BankSoal([
                    'kode_bank' => $kodeBank,
                    'judul' => $mapel->nama_mapel . ' ' . $tingkat . ' - Bank Soal ' . ($bankSoalCount + 1),
                    'deskripsi' => 'Kumpulan soal untuk ' . $mapel->nama_mapel . ' kelas ' . $tingkat,
                    'mapel_id' => $mapelId,
                    'tingkat' => $tingkat,
                    'jenis_soal' => 'pilihan_ganda', // Multiple choice
                    'created_by' => $user->id,
                    'status' => 'published'
                ]);

                $bankSoal->save();
                $bankSoalCount++;

                // Create 5 questions for each bank soal (smaller number to avoid timeouts)
                for ($i = 1; $i <= 5; $i++) {
                    $soal = new Soal([
                        'pertanyaan' => 'Soal ' . $i . ' untuk ' . $mapel->nama_mapel . ' ' . $tingkat,
                        'pilihan' => json_encode([
                            'A' => 'Pilihan A untuk soal ' . $i,
                            'B' => 'Pilihan B untuk soal ' . $i,
                            'C' => 'Pilihan C untuk soal ' . $i,
                            'D' => 'Pilihan D untuk soal ' . $i,
                            'E' => 'Pilihan E untuk soal ' . $i,
                        ]),
                        'kunci_jawaban' => ['A', 'B', 'C', 'D', 'E'][array_rand(['A', 'B', 'C', 'D', 'E'])],
                        'pembahasan' => 'Pembahasan untuk soal ' . $i,
                        'status' => 'aktif',
                        'created_by' => $user->id
                    ]);

                    $bankSoal->soals()->save($soal);
                    $soalCount++;
                }
            }
        }

        echo "✅ {$bankSoalCount} bank soal with {$soalCount} soal seeded successfully!\n";
    }
}
