<?php

use Illuminate\Support\Facades\Route;
use App\Models\JadwalUjian;
use App\Models\SesiRuangan;
use Carbon\Carbon;

// Debug route for date assignments
Route::get('/debug-dates', function () {
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();
    $tomorrow = Carbon::tomorrow();

    echo "<h1>Debug Date Assignments</h1>";
    echo "<p>Today: " . $today->format('Y-m-d') . "</p>";
    echo "<p>Yesterday: " . $yesterday->format('Y-m-d') . "</p>";
    echo "<p>Tomorrow: " . $tomorrow->format('Y-m-d') . "</p>";

    // Today's jadwal
    $todayJadwal = JadwalUjian::whereDate('tanggal', $today)->get();
    echo "<h2>Today's Jadwal: " . $todayJadwal->count() . "</h2>";
    foreach ($todayJadwal as $jadwal) {
        echo "<p>" . $jadwal->id . ": " . $jadwal->tanggal . " - " . $jadwal->judul . "</p>";
    }

    // Yesterday's jadwal
    $yesterdayJadwal = JadwalUjian::whereDate('tanggal', $yesterday)->get();
    echo "<h2>Yesterday's Jadwal: " . $yesterdayJadwal->count() . "</h2>";
    foreach ($yesterdayJadwal as $jadwal) {
        echo "<p>" . $jadwal->id . ": " . $jadwal->tanggal . " - " . $jadwal->judul . "</p>";
    }

    // Tomorrow's jadwal
    $tomorrowJadwal = JadwalUjian::whereDate('tanggal', $tomorrow)->get();
    echo "<h2>Tomorrow's Jadwal: " . $tomorrowJadwal->count() . "</h2>";
    foreach ($tomorrowJadwal as $jadwal) {
        echo "<p>" . $jadwal->id . ": " . $jadwal->tanggal . " - " . $jadwal->judul . "</p>";
    }

    // Future jadwal
    $futureJadwal = JadwalUjian::whereDate('tanggal', '>', $today)->orderBy('tanggal')->get();
    echo "<h2>Future Jadwal: " . $futureJadwal->count() . "</h2>";
    foreach ($futureJadwal as $jadwal) {
        echo "<p>" . $jadwal->id . ": " . $jadwal->tanggal . " - " . $jadwal->judul . "</p>";
    }

    // Past jadwal
    $pastJadwal = JadwalUjian::whereDate('tanggal', '<', $today)->orderBy('tanggal', 'desc')->take(5)->get();
    echo "<h2>Past Jadwal: " . $pastJadwal->count() . " (showing 5)</h2>";
    foreach ($pastJadwal as $jadwal) {
        echo "<p>" . $jadwal->id . ": " . $jadwal->tanggal . " - " . $jadwal->judul . "</p>";
    }

    // Check the SQL query that is being executed for future jadwal
    $futureQueryBuilder = JadwalUjian::whereDate('tanggal', '>', $today);
    $futureQuery = $futureQueryBuilder->toSql();
    $futureBindings = $futureQueryBuilder->getBindings();

    echo "<h2>Future Jadwal SQL:</h2>";
    echo "<pre>" . $futureQuery . "</pre>";
    echo "<p>Bindings: " . json_encode($futureBindings) . "</p>";

    return "Complete";
})->middleware('auth:web');

// Register this route in web.php
