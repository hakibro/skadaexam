<?php

require_once 'vendor/autoload.php';

// Load the Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking berita_acara_ujian table schema\n";
echo "=======================================\n";

try {
    $columns = DB::select("DESCRIBE berita_acara_ujian");

    foreach ($columns as $column) {
        if ($column->Field === 'status_pelaksanaan') {
            echo "Column: {$column->Field}\n";
            echo "Type: {$column->Type}\n";
            echo "Null: {$column->Null}\n";
            echo "Key: {$column->Key}\n";
            echo "Default: {$column->Default}\n";
            echo "Extra: {$column->Extra}\n";
            break;
        }
    }

    echo "\nTesting value length:\n";
    echo "selesai_normal: " . strlen('selesai_normal') . " characters\n";
    echo "selesai_terganggu: " . strlen('selesai_terganggu') . " characters\n";
    echo "dibatalkan: " . strlen('dibatalkan') . " characters\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
