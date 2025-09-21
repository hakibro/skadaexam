<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING PENGAWAS VIOLATIONS ACCESS ===" . PHP_EOL;

// Find pengawas user ID 25 (AZAH LAILATURROSIDAH)
$pengawasUser = \App\Models\User::find(25);

if (!$pengawasUser) {
    echo "Pengawas user ID 25 not found." . PHP_EOL;
    exit(1);
}

echo "Testing with user: {$pengawasUser->name} (ID: {$pengawasUser->id})" . PHP_EOL;
echo "Can supervise: " . ($pengawasUser->canSupervise() ? 'Yes' : 'No') . PHP_EOL;
echo "Is admin: " . ($pengawasUser->isAdmin() ? 'Yes' : 'No') . PHP_EOL;

// Check guru relationship
$guru = $pengawasUser->guru;
if ($guru) {
    echo "Guru profile: {$guru->nama} (ID: {$guru->id})" . PHP_EOL;
} else {
    echo "No guru profile found for this user." . PHP_EOL;
}

echo PHP_EOL;

// Manually simulate the controller logic
try {
    // Simulate authentication
    \Illuminate\Support\Facades\Auth::login($pengawasUser);

    echo "Simulating PelanggaranController->getViolations() for session 2:" . PHP_EOL;

    // Base query to get violations (from controller)
    $query = \App\Models\PelanggaranUjian::with([
        'siswa',
        'hasilUjian',
        'jadwalUjian.mapel',
        'sesiRuangan.ruangan'
    ])->orderBy('waktu_pelanggaran', 'desc');

    // Filter by specific session (sesi_ruangan_id = 2)
    $query->where('sesi_ruangan_id', 2);

    $violations = $query->get();

    echo "Found violations: " . $violations->count() . PHP_EOL;

    // Transform to response format
    $responseData = [
        'success' => true,
        'violations' => $violations->map(function ($violation) {
            return [
                'id' => $violation->id,
                'siswa' => [
                    'nama' => $violation->siswa ? $violation->siswa->nama : 'N/A',
                    'idyayasan' => $violation->siswa ? $violation->siswa->idyayasan : null
                ],
                'jadwal_ujian' => [
                    'mapel' => [
                        'nama_mapel' => $violation->jadwalUjian && $violation->jadwalUjian->mapel
                            ? $violation->jadwalUjian->mapel->nama_mapel
                            : 'Tidak ada mapel'
                    ]
                ],
                'sesi_ruangan' => [
                    'ruangan' => [
                        'nama_ruangan' => $violation->sesiRuangan && $violation->sesiRuangan->ruangan
                            ? $violation->sesiRuangan->ruangan->nama_ruangan
                            : 'Tidak ada ruangan'
                    ],
                    'nama_sesi' => $violation->sesiRuangan ? $violation->sesiRuangan->nama_sesi : null
                ],
                'jenis_pelanggaran' => $violation->jenis_pelanggaran,
                'deskripsi' => $violation->deskripsi,
                'waktu_pelanggaran' => $violation->waktu_pelanggaran,
                'is_dismissed' => $violation->is_dismissed,
                'is_finalized' => $violation->is_finalized,
                'tindakan' => $violation->tindakan
            ];
        })
    ];

    echo "Response data structure:" . PHP_EOL;
    echo json_encode($responseData, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL;
echo "=== TEST COMPLETE ===" . PHP_EOL;
