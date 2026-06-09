<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SOAL TABLE STRUCTURE ===\n";
$columns = DB::select('SHOW COLUMNS FROM soal');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n=== SAMPLE SOAL DATA ===\n";
$soals = DB::table('soal')->limit(3)->get();
foreach ($soals as $soal) {
    echo "ID: {$soal->id}\n";
    foreach ($soal as $key => $value) {
        if ($key !== 'soal') { // Skip long text
            echo "  {$key}: {$value}\n";
        }
    }
    echo "---\n";
}

echo "\n=== JADWAL_UJIAN TABLE STRUCTURE ===\n";
$columns = DB::select('SHOW COLUMNS FROM jadwal_ujian');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}
