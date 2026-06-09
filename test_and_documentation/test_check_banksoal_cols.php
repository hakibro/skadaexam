<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

$cols = Illuminate\Support\Facades\Schema::getColumnListing('bank_soal');
echo "bank_soal columns: " . implode(', ', $cols) . "\n";

$cols2 = Illuminate\Support\Facades\Schema::getColumnListing('soal');
echo "soal columns: " . implode(', ', $cols2) . "\n";

$cols3 = Illuminate\Support\Facades\Schema::getColumnListing('jadwal_ujian');
echo "jadwal_ujian columns: " . implode(', ', $cols3) . "\n";