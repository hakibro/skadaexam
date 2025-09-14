<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

$columns = DB::select("SHOW COLUMNS FROM hasil_ujian");
foreach($columns as $column) {
    echo $column->Field . "\n";
}
?>
