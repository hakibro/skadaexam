<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ruangan')->insert([
            [
                'kode_ruangan' => 'R001',
                'nama_ruangan' => 'Kelas A',
                'lokasi' => 'Gedung 1 Lantai 2',
                'kapasitas' => 30,
                'fasilitas' => json_encode(['AC', 'Proyektor', 'Whiteboard']),
                'status' => 'aktif',
                'keterangan' => 'Kelas reguler untuk IPA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'R002',
                'nama_ruangan' => 'Lab Komputer 1',
                'lokasi' => 'Gedung 2 Lantai 1',
                'kapasitas' => 20,
                'fasilitas' => json_encode(['Komputer', 'Proyektor', 'AC']),
                'status' => 'aktif',
                'keterangan' => 'Lab komputer untuk praktikum DKV',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_ruangan' => 'R003',
                'nama_ruangan' => 'Aula',
                'lokasi' => 'Gedung Utama Lantai 1',
                'kapasitas' => 100,
                'fasilitas' => json_encode(['Panggung', 'Sound System', 'AC']),
                'status' => 'aktif',
                'keterangan' => 'Digunakan untuk acara besar dan ujian serentak',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
