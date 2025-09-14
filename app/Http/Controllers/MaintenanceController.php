<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MaintenanceController extends Controller
{
    public function showFixJawabanSiswa()
    {
        return view('maintenance.fix-jawaban-siswa');
    }

    public function runFix()
    {
        try {
            // Run the migration to fix jawaban_siswa table
            Artisan::call('migrate', [
                '--path' => 'database/migrations/2023_10_30_000001_fix_jawaban_siswa_foreign_key.php',
                '--force' => true
            ]);

            // Verify tables exist
            $hasSoal = Schema::hasTable('soal');
            $hasJawabanSiswa = Schema::hasTable('jawaban_siswa');
            $hasJawabanSiswas = Schema::hasTable('jawaban_siswas');

            $message = "Perbaikan berhasil dijalankan.\n";
            $message .= "Status tabel: soal (" . ($hasSoal ? "ada" : "tidak ada") . "), ";
            $message .= "jawaban_siswa (" . ($hasJawabanSiswa ? "ada" : "tidak ada") . "), ";
            $message .= "jawaban_siswas (" . ($hasJawabanSiswas ? "ada" : "tidak ada") . ")";

            return back()->with('message', $message)->with('success', true);
        } catch (\Exception $e) {
            return back()->with('message', 'Error: ' . $e->getMessage())->with('success', false);
        }
    }
}
