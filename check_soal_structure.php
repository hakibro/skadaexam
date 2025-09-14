<?php
require 'vendor/autoload.php';
 = require_once 'bootstrap/app.php';
->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

 = DB::select('SHOW COLUMNS FROM soal');
print_r();

