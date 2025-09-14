<?php
require 'vendor/autoload.php';
 = require_once 'bootstrap/app.php';
->make('Illuminate\\Contracts\\Console\\Kernel')->bootstrap();

// Get schema information for hasil_ujian table
 = Schema::getColumnListing('hasil_ujian');
print_r();

// Check a sample record
 = DB::table('hasil_ujian')->first();
if () {
    print_r();
}
?>
