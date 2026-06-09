<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "Testing sidebar access for guru users:" . PHP_EOL;
$guruUser = App\Models\User::with('guru')->whereHas('guru')->first();

if ($guruUser) {
    echo "Testing user: " . $guruUser->name . PHP_EOL;

    // Simulate the logic from admin layout
    $user = $guruUser;
    $isAdmin = $user->isAdmin();
    $isGuru = $user->isGuru();

    $hasDataAccess = $isAdmin || $user->canManageData() || $isGuru;
    $hasNaskahAccess = $isAdmin || $user->canManageNaskah() || $isGuru;
    $hasPengawasAccess = $isAdmin || $user->canSupervise() || ($user->guru && $user->guru->count() > 0);
    $hasKoordinatorAccess = $isAdmin || $user->canCoordinate();
    $hasRuanganAccess = $isAdmin || $user->canManageRuangan() || $user->canCoordinate();

    echo "Access permissions:" . PHP_EOL;
    echo "- isAdmin: " . ($isAdmin ? 'Yes' : 'No') . PHP_EOL;
    echo "- isGuru: " . ($isGuru ? 'Yes' : 'No') . PHP_EOL;
    echo "- hasDataAccess: " . ($hasDataAccess ? 'Yes' : 'No') . PHP_EOL;
    echo "- hasNaskahAccess: " . ($hasNaskahAccess ? 'Yes' : 'No') . PHP_EOL;
    echo "- hasPengawasAccess: " . ($hasPengawasAccess ? 'Yes' : 'No') . PHP_EOL;
    echo "- hasKoordinatorAccess: " . ($hasKoordinatorAccess ? 'Yes' : 'No') . PHP_EOL;
    echo "- hasRuanganAccess: " . ($hasRuanganAccess ? 'Yes' : 'No') . PHP_EOL;

    // Test role display
    $displayRole = $user->role;
    if (empty($displayRole) && $user->isGuru()) {
        $displayRole = 'guru';
    } elseif (empty($displayRole)) {
        $displayRole = $user->roles->first()?->name ?? 'user';
    }
    echo "Display role: " . $displayRole . PHP_EOL;
} else {
    echo "No guru users found!" . PHP_EOL;
}
