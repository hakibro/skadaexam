<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Guru;

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PENGAWAS ACCESS DEBUG ===" . PHP_EOL;

// Find users who can access pengawas dashboard
$adminUsers = User::where('role', 'admin')->get();
$pengawasUsers = User::where('role', 'pengawas')->get();

echo "Admin users: " . $adminUsers->count() . PHP_EOL;
foreach ($adminUsers as $user) {
    echo "- {$user->username} (ID: {$user->id}) - Guru ID: {$user->guru_id}" . PHP_EOL;
}

echo PHP_EOL;
echo "Pengawas users: " . $pengawasUsers->count() . PHP_EOL;
foreach ($pengawasUsers as $user) {
    echo "- {$user->username} (ID: {$user->id}) - Guru ID: {$user->guru_id}" . PHP_EOL;
}

echo PHP_EOL;

// Check all users with guru_id
$usersWithGuru = User::whereNotNull('guru_id')->with('guru')->get();
echo "Users with guru profiles: " . $usersWithGuru->count() . PHP_EOL;
foreach ($usersWithGuru as $user) {
    echo "- {$user->username} (Role: {$user->role}) - Guru: " . ($user->guru ? $user->guru->nama : 'N/A') . PHP_EOL;

    // Test methods
    if (method_exists($user, 'canSupervise')) {
        echo "  Can supervise: " . ($user->canSupervise() ? 'Yes' : 'No') . PHP_EOL;
    }
    if (method_exists($user, 'isAdmin')) {
        echo "  Is admin: " . ($user->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
    }
}

echo PHP_EOL;

// Check today's pengawas assignments
$today = \Carbon\Carbon::today();
$assignments = \App\Models\JadwalUjianSesiRuangan::with(['pengawas', 'jadwalUjian.mapel'])
    ->whereHas('jadwalUjian', function ($q) use ($today) {
        $q->whereDate('tanggal', $today);
    })
    ->get();

echo "Today's pengawas assignments: " . $assignments->count() . PHP_EOL;
foreach ($assignments as $assignment) {
    $pengawasName = $assignment->pengawas ? $assignment->pengawas->nama : 'No pengawas';
    $pengawasId = $assignment->pengawas_id;
    $mapelName = $assignment->jadwalUjian && $assignment->jadwalUjian->mapel ? $assignment->jadwalUjian->mapel->nama_mapel : 'No mapel';

    echo "- Session {$assignment->sesi_ruangan_id}: {$pengawasName} (ID: {$pengawasId})" . PHP_EOL;
    echo "  Mapel: {$mapelName}" . PHP_EOL;

    // Check if pengawas has a user account
    if ($assignment->pengawas) {
        $pengawasUser = User::where('guru_id', $assignment->pengawas->id)->first();
        if ($pengawasUser) {
            echo "  User account: {$pengawasUser->username} (Role: {$pengawasUser->role})" . PHP_EOL;
        } else {
            echo "  No user account found for this pengawas" . PHP_EOL;
        }
    }
    echo PHP_EOL;
}

echo "=== DEBUG COMPLETE ===" . PHP_EOL;
