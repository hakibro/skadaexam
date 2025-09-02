<?php
// filepath: database\seeders\MapelSeeder.php

namespace Database\Seeders;

use App\Models\Mapel;
use Illuminate\Database\Seeder;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        echo "📚 Seeding Mapel data...\n";

        $mapelList = [
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA,IPS'],
            ['kode_mapel' => 'FIS', 'nama_mapel' => 'Fisika', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA'],
            ['kode_mapel' => 'KIM', 'nama_mapel' => 'Kimia', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA'],
            ['kode_mapel' => 'BIO', 'nama_mapel' => 'Biologi', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA'],
            ['kode_mapel' => 'EKO', 'nama_mapel' => 'Ekonomi', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPS'],
            ['kode_mapel' => 'GEO', 'nama_mapel' => 'Geografi', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPS'],
            ['kode_mapel' => 'SEJ', 'nama_mapel' => 'Sejarah', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA,IPS'],
            ['kode_mapel' => 'BIN', 'nama_mapel' => 'Bahasa Indonesia', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA,IPS'],
            ['kode_mapel' => 'BIG', 'nama_mapel' => 'Bahasa Inggris', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA,IPS'],
            ['kode_mapel' => 'PKN', 'nama_mapel' => 'Pendidikan Kewarganegaraan', 'tingkat' => 'X,XI,XII', 'jurusan' => 'IPA,IPS'],
        ];

        $count = 0;
        foreach ($mapelList as $mapel) {
            Mapel::updateOrCreate(
                ['kode_mapel' => $mapel['kode_mapel']],
                [
                    'nama_mapel' => $mapel['nama_mapel'],
                    'tingkat' => $mapel['tingkat'],
                    'jurusan' => $mapel['jurusan'],
                    'status' => 'aktif',
                    'deskripsi' => 'Mata pelajaran ' . $mapel['nama_mapel']
                ]
            );
            $count++;
        }

        echo "✅ {$count} mapel seeded successfully!\n";
    }
}
