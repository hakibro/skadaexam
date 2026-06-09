<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

echo "Checking users with guru profiles and their roles:" . PHP_EOL;
$users = App\Models\User::with('guru')->whereHas('guru')->limit(5)->get();
foreach ($users as $user) {
    echo "User: " . $user->name . ", Database Role: '" . $user->role . "'" . PHP_EOL;
    echo "  - Has guru profile: " . ($user->guru ? 'Yes (' . $user->guru->nama . ')' : 'No') . PHP_EOL;
    echo "  - Spatie roles: " . $user->roles->pluck('name')->join(', ') . PHP_EOL;
    echo "  - isAdmin(): " . ($user->isAdmin() ? 'Yes' : 'No') . PHP_EOL;
    echo "  - canSupervise(): " . ($user->canSupervise() ? 'Yes' : 'No') . PHP_EOL;
    echo "  - canManageNaskah(): " . ($user->canManageNaskah() ? 'Yes' : 'No') . PHP_EOL;
    echo "  - canManageData(): " . ($user->canManageData() ? 'Yes' : 'No') . PHP_EOL;
    echo PHP_EOL;
}

echo "Checking available roles in system:" . PHP_EOL;
$roles = Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- " . $role->name . PHP_EOL;
}
