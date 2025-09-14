<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\EnrollmentUjian;
use App\Models\JadwalUjian;
use Carbon\Carbon;

echo "Testing active mapel functionality\n";
echo "--------------------------------\n\n";

// Get an active enrollment
$enrollment = EnrollmentUjian::with([
    'sesiRuangan.ruangan',
    'sesiRuangan.jadwalUjians.mapel'
])
    ->whereHas('sesiRuangan', function ($query) {
        $query->whereIn('status', ['berlangsung', 'belum_mulai'])
            ->where('token_expired_at', '>', now());
    })
    ->latest()
    ->first();

if ($enrollment) {
    echo "Found enrollment ID: " . $enrollment->id . "\n";

    if ($enrollment->sesiRuangan) {
        $sesi = $enrollment->sesiRuangan;
        echo "Sesi: " . $sesi->nama_sesi . "\n";
        echo "Date: " . Carbon::parse($sesi->tanggal)->format('Y-m-d') . "\n";
        echo "Today: " . Carbon::today()->format('Y-m-d') . "\n";
        echo "Is Today: " . (Carbon::parse($sesi->tanggal)->isToday() ? 'Yes' : 'No') . "\n\n";

        echo "All Jadwal for this sesi:\n";
        foreach ($sesi->jadwalUjians as $jadwal) {
            echo "- " . $jadwal->mapel->nama_mapel . " (ID: {$jadwal->id})\n";
            echo "  Status: " . $jadwal->status . "\n";
            echo "  Is Active: " . ($jadwal->isActive() ? 'Yes' : 'No') . "\n";

            try {
                $tanggal = $jadwal->tanggal->format('Y-m-d');
                $waktuMulai = Carbon::parse($tanggal . ' ' . $sesi->waktu_mulai);
                $waktuSelesai = Carbon::parse($tanggal . ' ' . $sesi->waktu_selesai);

                echo "  Start: " . $waktuMulai->format('Y-m-d H:i:s') . "\n";
                echo "  End: " . $waktuSelesai->format('Y-m-d H:i:s') . "\n";
                echo "  Now: " . now()->format('Y-m-d H:i:s') . "\n";
                echo "  Is Between: " . (now()->between($waktuMulai, $waktuSelesai) ? 'Yes' : 'No') . "\n\n";
            } catch (Exception $e) {
                echo "  Error parsing dates: " . $e->getMessage() . "\n\n";
            }
        }

        // Filter active mapels for today
        $activeMapels = $sesi->jadwalUjians->filter(function ($jadwal) use ($sesi) {
            try {
                $tanggal = $jadwal->tanggal->format('Y-m-d');
                $waktuMulai = Carbon::parse($tanggal . ' ' . $sesi->waktu_mulai);
                $waktuSelesai = Carbon::parse($tanggal . ' ' . $sesi->waktu_selesai);

                return now()->between($waktuMulai, $waktuSelesai);
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
                return false;
            }
        })->map(function ($jadwal) {
            return [
                'id' => $jadwal->id,
                'nama_mapel' => $jadwal->mapel->nama_mapel ?? 'Mapel tidak tersedia',
                'durasi' => $jadwal->durasi_menit,
                'kode' => $jadwal->kode_ujian ?? '-'
            ];
        });

        echo "\nActive mapels for today:\n";
        if ($activeMapels->count() > 0) {
            foreach ($activeMapels as $mapel) {
                echo "- " . $mapel['nama_mapel'] . " (ID: {$mapel['id']})\n";
                echo "  Durasi: " . $mapel['durasi'] . " menit\n";
                echo "  Kode: " . $mapel['kode'] . "\n";
            }
        } else {
            echo "No active mapels found for today.\n";
        }
    } else {
        echo "No sesi ruangan found for this enrollment.\n";
    }
} else {
    echo "No active enrollment found.\n";
}
