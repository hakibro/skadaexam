<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$soal = App\Models\SoalUjian::first();
if ($soal) {
    print_r($soal->toArray());
}
?>
