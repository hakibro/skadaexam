<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== JAWABAN_SISWAS TABLE STRUCTURE ===\n";
$columns = DB::select('SHOW COLUMNS FROM jawaban_siswas');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}
