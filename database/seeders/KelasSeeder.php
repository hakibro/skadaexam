<?php
// filepath: database\seeders\KelasSeeder.php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        echo "🎓 Seeding Kelas data...\n";

        // Define list of kelas
        $kelasList = [
            // X (10th grade)
            ['nama_kelas' => 'X IPA 1', 'tingkat' => 'X', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPA 2', 'tingkat' => 'X', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPA 3', 'tingkat' => 'X', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'X IPS 1', 'tingkat' => 'X', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'X IPS 2', 'tingkat' => 'X', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'X IPS 3', 'tingkat' => 'X', 'jurusan' => 'IPS'],

            // XI (11th grade)
            ['nama_kelas' => 'XI IPA 1', 'tingkat' => 'XI', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XI IPA 2', 'tingkat' => 'XI', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XI IPA 3', 'tingkat' => 'XI', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XI IPS 1', 'tingkat' => 'XI', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XI IPS 2', 'tingkat' => 'XI', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XI IPS 3', 'tingkat' => 'XI', 'jurusan' => 'IPS'],

            // XII (12th grade)
            ['nama_kelas' => 'XII IPA 1', 'tingkat' => 'XII', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPA 2', 'tingkat' => 'XII', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPA 3', 'tingkat' => 'XII', 'jurusan' => 'IPA'],
            ['nama_kelas' => 'XII IPS 1', 'tingkat' => 'XII', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XII IPS 2', 'tingkat' => 'XII', 'jurusan' => 'IPS'],
            ['nama_kelas' => 'XII IPS 3', 'tingkat' => 'XII', 'jurusan' => 'IPS'],
        ];

        $count = 0;
        foreach ($kelasList as $kelas) {
            Kelas::updateOrCreate(
                ['nama_kelas' => $kelas['nama_kelas']],
                [
                    'tingkat' => $kelas['tingkat'],
                    'jurusan' => $kelas['jurusan'],
                    'deskripsi' => 'Kelas ' . $kelas['nama_kelas']
                ]
            );
            $count++;
        }

        echo "✅ {$count} kelas seeded successfully!\n";
    }
}
