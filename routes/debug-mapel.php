<?php

use Illuminate\Support\Facades\Route;
use App\Models\JadwalUjian;
use App\Models\Mapel;
use App\Models\SesiRuangan;

// Debug route for mapel relationships
Route::get('/debug-mapel', function () {
    $jadwals = JadwalUjian::with('mapel')->take(5)->get();

    $result = [];
    foreach ($jadwals as $jadwal) {
        $result[] = [
            'jadwal_id' => $jadwal->id,
            'judul' => $jadwal->judul,
            'mapel_id' => $jadwal->mapel_id,
            'mapel' => $jadwal->mapel ? [
                'id' => $jadwal->mapel->id,
                'nama' => $jadwal->mapel->nama ?? null,
                'nama_mapel' => $jadwal->mapel->nama_mapel ?? null,
                'kode_mapel' => $jadwal->mapel->kode_mapel ?? null,
                'attributes' => $jadwal->mapel->getAttributes()
            ] : null
        ];
    }

    // Check SesiRuangan with jadwalUjians and mapel
    $sesiRuangans = SesiRuangan::with(['jadwalUjians', 'jadwalUjians.mapel'])->take(3)->get();

    $sesiResults = [];
    foreach ($sesiRuangans as $sesi) {
        $jadwalData = [];
        foreach ($sesi->jadwalUjians as $jadwal) {
            $jadwalData[] = [
                'jadwal_id' => $jadwal->id,
                'judul' => $jadwal->judul,
                'mapel_id' => $jadwal->mapel_id,
                'mapel' => $jadwal->mapel ? [
                    'id' => $jadwal->mapel->id,
                    'nama' => $jadwal->mapel->nama ?? null,
                    'nama_mapel' => $jadwal->mapel->nama_mapel ?? null,
                    'kode_mapel' => $jadwal->mapel->kode_mapel ?? null
                ] : null
            ];
        }

        $sesiResults[] = [
            'sesi_id' => $sesi->id,
            'nama_sesi' => $sesi->nama_sesi,
            'jadwal_count' => $sesi->jadwalUjians->count(),
            'jadwals' => $jadwalData
        ];
    }

    return response()->json([
        'jadwal_ujian_sample' => $result,
        'sesi_ruangan_sample' => $sesiResults
    ]);
})->middleware('auth:web');

// Register this route in web.php
