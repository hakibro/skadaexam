<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\JadwalUjian;
use App\Models\Mapel;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdateJadwalTargetKelasController extends Controller
{
    public function index()
    {
        // Debug output
        Log::info('UpdateJadwalTargetKelasController::index() called');

        $jadwalUjianCount = JadwalUjian::count();
        $jadwalWithEmptyKelasTarget = JadwalUjian::whereJsonLength('kelas_target', 0)
            ->orWhereNull('kelas_target')
            ->count();

        Log::info('Counts', ['totalJadwal' => $jadwalUjianCount, 'emptyTargetCount' => $jadwalWithEmptyKelasTarget]);

        return view('features.naskah.jadwal.batch-update-kelas-target', [
            'totalJadwal' => $jadwalUjianCount,
            'emptyTargetCount' => $jadwalWithEmptyKelasTarget
        ]);
    }

    public function update(Request $request)
    {
        // Add detailed logging of the incoming request
        Log::info('UpdateJadwalTargetKelasController::update() called', [
            'request_content_type' => $request->header('Content-Type'),
            'request_data' => $request->all(),
            'request_method' => $request->method()
        ]);

        // Parse limit and dry_run correctly depending on how they're sent
        $limit = $request->input('limit', 100);
        if (is_string($limit)) {
            $limit = (int)$limit;
        }

        $dryRun = $request->boolean('dry_run', true);

        // Log the processed parameters
        Log::info('Parameters after processing', [
            'limit' => $limit,
            'dryRun' => $dryRun
        ]);

        // Get jadwal ujian with empty kelas_target
        $query = JadwalUjian::with('mapel')
            ->where(function ($q) {
                $q->whereJsonLength('kelas_target', 0)
                    ->orWhereNull('kelas_target');
            })
            ->limit($limit);

        $jadwals = $query->get();

        $updated = 0;
        $failed = 0;
        $log = [];

        foreach ($jadwals as $jadwal) {
            // Skip if no mapel
            if (!$jadwal->mapel) {
                $failed++;
                $log[] = "SKIPPED: Jadwal ID {$jadwal->id} ({$jadwal->judul}) - No mapel found";
                continue;
            }

            $mapel = $jadwal->mapel;
            $query = Kelas::query();

            // Filter by tingkat if mapel has it
            if ($mapel->tingkat) {
                $query->where('tingkat', $mapel->tingkat);
            }

            // Filter by jurusan if mapel has it, or include UMUM jurusan
            if ($mapel->jurusan) {
                $query->where(function ($q) use ($mapel) {
                    $q->where('jurusan', $mapel->jurusan)
                        ->orWhere('jurusan', 'UMUM');
                });
            }

            $matchingKelas = $query->get();
            $kelasTarget = $matchingKelas->pluck('id')->toArray();

            $log[] = "Processing: Jadwal ID {$jadwal->id} ({$jadwal->judul}) - Found " . count($kelasTarget) . " matching kelas";

            if (!$dryRun) {
                $jadwal->update(['kelas_target' => $kelasTarget]);
                $log[] = "UPDATED: Jadwal ID {$jadwal->id} with " . count($kelasTarget) . " kelas";
            } else {
                $log[] = "DRY RUN: Would update Jadwal ID {$jadwal->id} with " . count($kelasTarget) . " kelas";
            }

            $updated++;
        }

        $response = [
            'success' => true,
            'message' => $dryRun ? 'Dry run completed' : 'Update completed',
            'stats' => [
                'processed' => $jadwals->count(),
                'updated' => $updated,
                'failed' => $failed,
                'dry_run' => $dryRun
            ],
            'log' => $log
        ];

        // Log the response
        Log::info('UpdateJadwalTargetKelasController::update() completed', [
            'processed' => $jadwals->count(),
            'updated' => $updated,
            'failed' => $failed,
            'dry_run' => $dryRun
        ]);

        return response()->json($response);
    }
}
