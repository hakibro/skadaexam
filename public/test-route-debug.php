<?php

// Show the artisan route list output
echo '<h1>Route List</h1>';
echo '<pre>';
echo shell_exec('cd .. && php artisan route:list --name=jadwal.batch-update-kelas-target');
echo '</pre>';

// Show the route path construction
echo '<h1>Route Path Construction</h1>';
echo '<p>Expected full URL: http://skadaexam.test/naskah/jadwal/batch-update-kelas-target</p>';
echo '<p>Make sure you are accessing the correct URL with the "naskah" prefix.</p>';

// Show the request details
echo '<h1>Current Request Details</h1>';
echo '<pre>';
echo 'REQUEST_URI: ' . $_SERVER['REQUEST_URI'] . "\n";
echo 'PATH_INFO: ' . ($_SERVER['PATH_INFO'] ?? 'Not set') . "\n";
echo 'QUERY_STRING: ' . ($_SERVER['QUERY_STRING'] ?? 'Not set') . "\n";
echo '</pre>';
