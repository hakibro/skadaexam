<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING ENROLLMENT_UJIAN TABLE STRUCTURE ===\n";
$columns = DB::select("DESCRIBE enrollment_ujian");
foreach ($columns as $column) {
    echo $column->Field . " - " . $column->Type . "\n";
}
