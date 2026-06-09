<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\SesiRuangan;
use App\Models\JadwalUjian;
use App\Models\EnrollmentUjian;
use Illuminate\Support\Facades\DB;

echo "======================================\n";
echo "TESTING JADWAL UJIAN QUERY FIX\n";
echo "======================================\n\n";

// Test parameters
$sesiRuanganId = 116; // From the error message
$jadwalId = 37; // From the error message

echo "Testing with SesiRuangan ID: $sesiRuanganId and JadwalUjian ID: $jadwalId\n\n";

try {
    // 1. Test the problematic query directly using query builder
    echo "1. Testing raw query that was failing:\n";
    $result = DB::table('jadwal_ujian')
        ->join('jadwal_ujian_sesi_ruangan', 'jadwal_ujian.id', '=', 'jadwal_ujian_sesi_ruangan.jadwal_ujian_id')
        ->where('jadwal_ujian_sesi_ruangan.sesi_ruangan_id', $sesiRuanganId)
        ->where('jadwal_ujian.id', $jadwalId) // Fixed query with table name specified
        ->first();

    if ($result) {
        echo "✅ Raw query successful! Found jadwal ujian with id {$result->id}\n";
    } else {
        echo "❌ Raw query returned no results\n";
    }
} catch (\Exception $e) {
    echo "❌ Raw query failed: " . $e->getMessage() . "\n";
}

try {
    // 2. Test using the relationship method with the fix
    echo "\n2. Testing relationship method with fix:\n";
    $sesiRuangan = SesiRuangan::find($sesiRuanganId);

    if (!$sesiRuangan) {
        echo "❌ SesiRuangan with ID $sesiRuanganId not found!\n";
    } else {
        $jadwalUjian = $sesiRuangan->jadwalUjians()
            ->where('jadwal_ujian.id', $jadwalId)
            ->first();

        if ($jadwalUjian) {
            echo "✅ Relationship query successful! Found jadwal ujian with id {$jadwalUjian->id}\n";
            echo "   Jadwal info: {$jadwalUjian->kode_ujian} - {$jadwalUjian->tanggal}\n";
        } else {
            echo "❌ Relationship query returned no results\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Relationship query failed: " . $e->getMessage() . "\n";
}

try {
    // 3. Test enrollment scenario
    echo "\n3. Testing with enrollment scenario:\n";
    // Find an enrollment for this sesi ruangan
    $enrollment = EnrollmentUjian::where('sesi_ruangan_id', $sesiRuanganId)->first();

    if (!$enrollment) {
        echo "❌ No enrollment found for SesiRuangan with ID $sesiRuanganId\n";
    } else {
        echo "   Found enrollment ID: {$enrollment->id} for siswa ID: {$enrollment->siswa_id}\n";

        $jadwalUjian = $enrollment->sesiRuangan->jadwalUjians()
            ->where('jadwal_ujian.id', $jadwalId)
            ->first();

        if ($jadwalUjian) {
            echo "✅ Enrollment scenario successful! Found jadwal ujian with id {$jadwalUjian->id}\n";
        } else {
            echo "❌ Enrollment scenario returned no results\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Enrollment scenario failed: " . $e->getMessage() . "\n";
}

echo "\n======================================\n";
echo "TEST SUMMARY:\n";

echo "The ambiguity issue has been fixed by specifying the table name in the where clause:\n";
echo "BEFORE: ->where('id', \$jadwalId) // Ambiguous column 'id'\n";
echo "AFTER:  ->where('jadwal_ujian.id', \$jadwalId) // Explicitly specify the table\n";

echo "\nRecommendation: Always qualify column names in queries involving joins to avoid ambiguity.\n";
echo "======================================\n";
