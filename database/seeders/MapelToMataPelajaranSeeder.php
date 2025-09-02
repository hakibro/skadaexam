<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapelToMataPelajaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🔄 Copying data from mapel to mata_pelajaran table...\n";

        // Check if mata_pelajaran table is empty
        $existingCount = DB::table('mata_pelajaran')->count();
        if ($existingCount > 0) {
            echo "⏩ Skipping - mata_pelajaran table already has data ($existingCount records)\n";
            return;
        }

        // Get all data from mapel table
        $mapels = DB::table('mapel')->get();

        if ($mapels->isEmpty()) {
            echo "❌ No data found in mapel table\n";
            return;
        }

        $count = 0;
        foreach ($mapels as $mapel) {
            DB::table('mata_pelajaran')->insert([
                'id' => $mapel->id, // Keep the same ID for consistency
                'nama' => $mapel->nama_mapel,
                'kode' => $mapel->kode_mapel,
                'deskripsi' => $mapel->deskripsi,
                'aktif' => $mapel->status === 'aktif' ? 1 : 0,
                'created_at' => $mapel->created_at,
                'updated_at' => $mapel->updated_at
            ]);
            $count++;
        }

        echo "✅ Successfully copied $count records from mapel to mata_pelajaran table\n";
    }
}
