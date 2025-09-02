<?php
// filepath: database\seeders\BankSoalSeeder.php

namespace Database\Seeders;

use App\Models\BankSoal;
use App\Models\Mapel;
use App\Models\User;
use App\Models\Soal;
use Illuminate\Database\Seeder;

class BankSoalSeeder extends Seeder
{
    public function run(): void
    {
        echo "📝 Seeding Bank Soal data...\n";

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

        $bankSoalCount = 0;
        $soalCount = 0;

        // Create 10 bank soal
        foreach ($mapelIds as $mapelId) {
            $mapel = Mapel::find($mapelId);
            $tingkatArray = explode(',', $mapel->tingkat);

            foreach ($tingkatArray as $tingkat) {
                $kodeBank = 'BS' . str_pad($bankSoalCount + 1, 3, '0', STR_PAD_LEFT);

                // Create a bank soal
                $bankSoal = BankSoal::updateOrCreate(
                    ['kode_bank' => $kodeBank],
                    [
                        'judul' => $mapel->nama_mapel . ' ' . $tingkat . ' - Bank Soal ' . ($bankSoalCount + 1),
                        'deskripsi' => 'Kumpulan soal untuk ' . $mapel->nama_mapel . ' kelas ' . $tingkat,
                        'mapel_id' => $mapelId,
                        'tingkat' => $tingkat,
                        'jenis_soal' => 'pilihan_ganda', // Multiple choice
                        'created_by' => $user->id,
                        'status' => 'published'
                    ]
                );

                $bankSoalCount++;

                // Create 20 questions for each bank soal
                for ($i = 1; $i <= 20; $i++) {
                    $soal = new Soal([
                        'bank_soal_id'      => $bankSoal->id,
                        'nomor_soal'        => $i,
                        'pertanyaan'        => 'Soal ' . $i . ' untuk ' . $mapel->nama_mapel . ' ' . $tingkat,
                        'gambar_pertanyaan' => null,
                        'tipe_pertanyaan'   => 'teks',
                        'tipe_soal'         => 'pilihan_ganda',

                        // Pilihan A–E
                        'pilihan_a_teks'    => 'Pilihan A untuk soal ' . $i,
                        'pilihan_a_gambar'  => null,
                        'pilihan_a_tipe'    => 'teks',

                        'pilihan_b_teks'    => 'Pilihan B untuk soal ' . $i,
                        'pilihan_b_gambar'  => null,
                        'pilihan_b_tipe'    => 'teks',

                        'pilihan_c_teks'    => 'Pilihan C untuk soal ' . $i,
                        'pilihan_c_gambar'  => null,
                        'pilihan_c_tipe'    => 'teks',

                        'pilihan_d_teks'    => 'Pilihan D untuk soal ' . $i,
                        'pilihan_d_gambar'  => null,
                        'pilihan_d_tipe'    => 'teks',

                        'pilihan_e_teks'    => 'Pilihan E untuk soal ' . $i,
                        'pilihan_e_gambar'  => null,
                        'pilihan_e_tipe'    => 'teks',

                        // Random kunci jawaban
                        'kunci_jawaban'     => collect(['A', 'B', 'C', 'D', 'E'])->random(),

                        // Pembahasan
                        'pembahasan_teks'   => 'Pembahasan untuk soal ' . $i,
                        'pembahasan_gambar' => null,
                        'pembahasan_tipe'   => 'teks',

                        // Tambahan default
                        'bobot'             => 1.00,
                        'kategori'          => 'umum',
                        'display_settings'  => json_encode([]),
                    ]);

                    $bankSoal->soals()->save($soal);
                }
            }
        }

        echo "✅ {$bankSoalCount} bank soal with {$soalCount} soal seeded successfully!\n";
    }
}
