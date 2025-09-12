<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SesiRuangan;
use Illuminate\Support\Facades\DB;

echo "=== CHECKING SESI RUANGAN TOKEN STRUCTURE ===\n";

// Check sesi_ruangan table structure
$columns = DB::select("DESCRIBE sesi_ruangan");
foreach ($columns as $column) {
    if (strpos($column->Field, 'token') !== false) {
        echo $column->Field . " - " . $column->Type . "\n";
    }
}

echo "\n=== SAMPLE SESI RUANGAN TOKENS ===\n";
$sesiSample = SesiRuangan::limit(5)->get();
foreach ($sesiSample as $sesi) {
    echo "ID: " . $sesi->id . " | ";
    echo "Nama: " . $sesi->nama_sesi . " | ";
    echo "Token: " . ($sesi->token_ujian ?? 'NULL') . " | ";
    echo "Expired: " . ($sesi->token_expired_at ? $sesi->token_expired_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
}

echo "\n=== CHECK TOKEN USAGE ===\n";
// Check if any sesi ruangan has tokens
$withTokens = SesiRuangan::whereNotNull('token_ujian')->count();
$withoutTokens = SesiRuangan::whereNull('token_ujian')->count();

echo "Sesi with tokens: " . $withTokens . "\n";
echo "Sesi without tokens: " . $withoutTokens . "\n";
