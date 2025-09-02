<?php
// filepath: database\seeders\RoleSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        echo "🔐 Creating Multi-Guard Role System...\n\n";

        // ===== WEB GUARD ROLES (Tabel Users) =====
        $webRoles = [
            'admin' => 'Administrator - Manage System & Guru'
        ];

        echo "🌐 Creating WEB guard roles (users table)...\n";
        foreach ($webRoles as $roleName => $description) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
            echo "   ✅ Web Role: {$roleName} - {$description}\n";
        }

        // ===== GURU GUARD ROLES (Tabel Guru) =====
        $guruRoles = [
            'data' => 'Data Management - Manage Student & Teacher Data',
            'naskah' => 'Naskah Management - Manage Exam Papers & Questions',
            'ruangan' => 'Ruangan Management - Room & Facility Management',
            'pengawas' => 'Pengawas - Exam Supervision & Monitoring',
            'koordinator' => 'Koordinator - Exam Coordination & Scheduling',
            'guru' => 'Guru Default - Basic Teacher Access'
        ];

        echo "\n👨‍🏫 Creating GURU guard roles (guru table)...\n";
        foreach ($guruRoles as $roleName => $description) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'guru'
            ]);
            echo "   ✅ Guru Role: {$roleName} - {$description}\n";
        }

        // ===== SISWA GUARD ROLES (Tabel Siswa) =====
        $siswaRoles = [
            'siswa' => 'Siswa - Student Access'
        ];

        echo "\n👨‍🎓 Creating SISWA guard roles (siswa table)...\n";
        foreach ($siswaRoles as $roleName => $description) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'siswa'
            ]);
            echo "   ✅ Siswa Role: {$roleName} - {$description}\n";
        }

        echo "\n✨ Multi-Guard Role System created successfully!\n";
        echo "🎯 Web Guard: Admin manages guru\n";
        echo "🎯 Guru Guard: Different guru roles for different features\n";
        echo "🎯 Siswa Guard: Student access\n\n";
    }
}
