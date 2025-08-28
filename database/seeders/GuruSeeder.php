<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Guru;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class GuruSeeder extends Seeder
{
    public function run()
    {
        // ======================
        // 1. Buat role jika belum ada
        // ======================


        $roles = ['admin', 'data', 'ruangan', 'pengawas', 'koordinator', 'naskah'];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role, 'guard_name' => 'guru'] // pastikan guard_name 'guru'
            );
        }


        // ======================
        // 2. Buat data guru
        // ======================

        $guruList = [
            [
                'nama' => 'Pak Budi',
                'nip' => '1987654321',
                'email' => 'budi@guru.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ],
            [
                'nama' => 'Bu Siti',
                'nip' => '1987654322',
                'email' => 'siti@guru.com',
                'password' => Hash::make('password123'),
                'role' => 'data',
            ],
            [
                'nama' => 'Pak Andi',
                'nip' => '1987654323',
                'email' => 'andi@guru.com',
                'password' => Hash::make('password123'),
                'role' => 'pengawas',
            ],
            [
                'nama' => 'Bu Rina',
                'nip' => '1987654324',
                'email' => 'rina@guru.com',
                'password' => Hash::make('password123'),
                'role' => 'naskah',
            ],
        ];

        foreach ($guruList as $guruData) {
            $guru = Guru::firstOrCreate(
                ['email' => $guruData['email']],
                [
                    'nama' => $guruData['nama'],
                    'nip' => $guruData['nip'],
                    'password' => $guruData['password'],
                ]
            );

            // Assign role
            $guru->assignRole($guruData['role']);
        }
    }
}
