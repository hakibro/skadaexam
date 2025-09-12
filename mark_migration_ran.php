<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::table('migrations')->insert([
    'migration' => '2025_09_12_221158_create_jawaban_siswas_table',
    'batch' => 20
]);

echo 'Migration marked as ran' . PHP_EOL;
