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
        echo "👨‍🏫 Starting Guru Profile seeding...\n\n";

        // Note: Roles should already be created by UserSeeder->RoleSeeder

        // ======================
        // Buat Guru Profiles dengan berbagai role
        // ======================

        $guruList = [
            [
                'nama' => 'Pak Budi Santoso',
                'nip' => '198765001',
                'email' => 'budi.admin@guru.skada.test',
                'role' => 'koordinator', // Changed from 'admin' to 'koordinator' for guru guard
                'description' => 'Koordinator Ujian - Full Access'
            ],
            [
                'nama' => 'Bu Siti Nurhaliza',
                'nip' => '198765002',
                'email' => 'siti.data@guru.skada.test',
                'role' => 'data',
                'description' => 'Data Management Specialist'
            ],
            [
                'nama' => 'Pak Andi Wijaya',
                'nip' => '198765003',
                'email' => 'andi.pengawas@guru.skada.test',
                'role' => 'pengawas',
                'description' => 'Pengawas Ujian'
            ],
            [
                'nama' => 'Bu Rina Puspitasari',
                'nip' => '198765004',
                'email' => 'rina.naskah@guru.skada.test',
                'role' => 'naskah',
                'description' => 'Pengelola Naskah Soal'
            ],
            [
                'nama' => 'Pak Dedi Setiawan',
                'nip' => '198765005',
                'email' => 'dedi.koordinator@guru.skada.test',
                'role' => 'koordinator',
                'description' => 'Koordinator Ujian'
            ],
            [
                'nama' => 'Bu Maya Indira',
                'nip' => '198765006',
                'email' => 'maya.ruangan@guru.skada.test',
                'role' => 'ruangan',
                'description' => 'Pengelola Ruangan'
            ]
        ];

        foreach ($guruList as $guruData) {
            // Create or update guru (remove the 'role' field from database creation)
            $guru = Guru::updateOrCreate(
                ['email' => $guruData['email']],
                [
                    'nama' => $guruData['nama'],
                    'nip' => $guruData['nip'],
                    'password' => Hash::make('password123'),
                ]
            );

            // Assign Spatie role dengan guru guard
            if (!$guru->hasRole($guruData['role'])) {
                $guru->assignRole($guruData['role']);
            }

            echo "   ✅ Guru Profile: {$guru->nama}\n";
            echo "      📧 Email: {$guru->email}\n";
            echo "      🆔 NIP: {$guru->nip}\n";
            echo "      🔐 Spatie Roles: " . $guru->roles->pluck('name')->implode(', ') . "\n";
            echo "      📝 Description: {$guruData['description']}\n";
            echo "      🛡️  Guard: guru\n\n";
        }

        echo "✨ Guru profiles created successfully!\n";
        echo "🔗 Login URL: /login/guru\n";
        echo "🔑 All passwords: password123\n\n";
    }
}
