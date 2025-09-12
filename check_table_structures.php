<?php

echo 'Checking sesi_ruangan table structure...' . PHP_EOL;

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select('DESCRIBE sesi_ruangan');
foreach ($columns as $column) {
    echo $column->Field . ' - ' . $column->Type . ' - ' . ($column->Null === 'YES' ? 'nullable' : 'not null') . PHP_EOL;
}

echo PHP_EOL . 'Checking berita_acara_ujian table structure...' . PHP_EOL;
$columns2 = DB::select('DESCRIBE berita_acara_ujian');
foreach ($columns2 as $column) {
    echo $column->Field . ' - ' . $column->Type . ' - ' . ($column->Null === 'YES' ? 'nullable' : 'not null') . PHP_EOL;
}
