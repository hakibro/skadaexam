<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        echo "🚀 Starting Admin seeding...\n\n";

        // Pastikan roles sudah ada
        $this->call(RoleSeeder::class);

        // ===== ADMIN USER (Web Guard - Tabel Users) =====
        echo "👨‍💼 Creating Admin User (web guard - users table)...\n";

        $admin = User::updateOrCreate(
            ['email' => 'admin@skadaexam.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Assign hanya role admin
        $admin->syncRoles(['admin']);

        echo "   ✅ Admin created: {$admin->email}\n";
        echo "      👤 Name: {$admin->name}\n";
        echo "      🔐 Spatie Role: " . $admin->roles->pluck('name')->implode(', ') . "\n";
        echo "      🛡️  Guard: web\n\n";

        echo "🎉 Admin seeding completed!\n\n";

        $this->showLoginCredentials();
    }

    private function showLoginCredentials()
    {
        echo "📋 LOGIN CREDENTIALS:\n";
        echo "══════════════════════════════════════════════════\n\n";

        echo "🌐 ADMIN LOGIN (/login - web guard):\n";
        echo "   👑 admin@skadaexam.test\n";
        echo "   🔑 password123\n\n";
    }
}
