<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "Total violations: " . App\Models\PelanggaranUjian::count() . PHP_EOL;

if (App\Models\PelanggaranUjian::count() > 0) {
    echo "Recent violations:" . PHP_EOL;
    $violations = App\Models\PelanggaranUjian::latest()->limit(5)->get();
    foreach ($violations as $v) {
        echo "ID: " . $v->id . ", Type: " . $v->jenis_pelanggaran . ", Time: " . $v->waktu_pelanggaran . ", Siswa ID: " . $v->siswa_id . PHP_EOL;
    }
} else {
    echo "No violations found." . PHP_EOL;
}
