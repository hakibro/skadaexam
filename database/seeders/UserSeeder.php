<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        echo "🚀 Starting Pure Spatie Role System seeding (Laravel 12)...\n\n";

        // Pastikan roles sudah ada
        $this->call(RoleSeeder::class);

        // ===== ADMIN USERS (Web Guard - Tabel Users) =====
        echo "👨‍💼 Creating Admin Users (web guard - users table)...\n";

        $admin = User::updateOrCreate(
            ['email' => 'admin@skadaexam.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                // NO native 'role' field
            ]
        );

        // ONLY Spatie role dengan web guard
        $admin->assignRole('admin');

        echo "   ✅ Admin created: {$admin->email}\n";
        echo "      👤 Name: {$admin->name}\n";
        echo "      🔐 Spatie Role: " . $admin->roles->pluck('name')->implode(', ') . "\n";
        echo "      🛡️  Guard: web\n\n";

        // ===== GURU PROFILES (Guru Guard - Tabel Guru) =====
        echo "👨‍🏫 Creating Guru Profiles (guru guard - guru table)...\n";

        $guruList = [
            [
                'email' => 'data.guru@skadaexam.test',
                'nama' => 'Bu Data Management',
                'nip' => '1001',
                'spatie_role' => 'data'
            ],
            [
                'email' => 'naskah.guru@skadaexam.test',
                'nama' => 'Pak Naskah Management',
                'nip' => '1002',
                'spatie_role' => 'naskah'
            ],
            [
                'email' => 'ruangan.guru@skadaexam.test',
                'nama' => 'Bu Ruangan Management',
                'nip' => '1003',
                'spatie_role' => 'ruangan'
            ],
            [
                'email' => 'pengawas.guru@skadaexam.test',
                'nama' => 'Pak Pengawas Ujian',
                'nip' => '1004',
                'spatie_role' => 'pengawas'
            ],
            [
                'email' => 'koordinator.guru@skadaexam.test',
                'nama' => 'Bu Koordinator Ujian',
                'nip' => '1005',
                'spatie_role' => 'koordinator'
            ],
            [
                'email' => 'guru.default@skadaexam.test',
                'nama' => 'Pak Guru Biasa',
                'nip' => '1006',
                'spatie_role' => 'guru'
            ]
        ];

        foreach ($guruList as $guruData) {
            $guru = Guru::updateOrCreate(
                ['email' => $guruData['email']],
                [
                    'nama' => $guruData['nama'],
                    'nip' => $guruData['nip'],
                    'password' => Hash::make('password123'),
                    // NO native 'role' field
                ]
            );

            // ONLY Spatie role dengan guru guard
            $guru->assignRole($guruData['spatie_role']);

            echo "   ✅ Guru created: {$guru->email}\n";
            echo "      👤 Nama: {$guru->nama} (NIP: {$guru->nip})\n";
            echo "      🔐 Spatie Role: " . $guru->roles->pluck('name')->implode(', ') . "\n";
            echo "      🛡️  Guard: guru\n\n";
        }

        // ===== SISWA PROFILES (Siswa Guard - Tabel Siswa) =====
        echo "👨‍🎓 Creating Siswa Profiles (siswa guard - siswa table)...\n";

        // Get kelas IDs from database
        $kelasXIIIPA1 = \App\Models\Kelas::where('nama_kelas', 'XII IPA 1')->first();
        $kelasXIIIPS1 = \App\Models\Kelas::where('nama_kelas', 'XII IPS 1')->first();
        $kelasXIIIPA2 = \App\Models\Kelas::where('nama_kelas', 'XII IPA 2')->first();

        $siswaList = [
            [
                'email' => 'siswa1@skadaexam.test',
                'idyayasan' => 'SMA001',
                'nama' => 'Ahmad Siswa Pertama',
                'kelas_id' => $kelasXIIIPA1 ? $kelasXIIIPA1->id : null,
                'status_pembayaran' => 'Lunas'
            ],
            [
                'email' => 'siswa2@skadaexam.test',
                'idyayasan' => 'SMA002',
                'nama' => 'Siti Siswi Kedua',
                'kelas_id' => $kelasXIIIPS1 ? $kelasXIIIPS1->id : null,
                'status_pembayaran' => 'Belum Lunas'
            ],
            [
                'email' => 'siswa3@skadaexam.test',
                'idyayasan' => 'SMA003',
                'nama' => 'Budi Siswa Ketiga',
                'kelas_id' => $kelasXIIIPA2 ? $kelasXIIIPA2->id : null,
                'status_pembayaran' => 'Lunas'
            ]
        ];

        foreach ($siswaList as $siswaData) {
            $siswa = Siswa::updateOrCreate(
                ['email' => $siswaData['email']],
                [
                    'idyayasan' => $siswaData['idyayasan'],
                    'nama' => $siswaData['nama'],
                    'kelas_id' => $siswaData['kelas_id'],
                    'status_pembayaran' => $siswaData['status_pembayaran'],
                    'password' => Hash::make('password123'),
                ]
            );

            // ONLY Spatie role dengan siswa guard
            $siswa->assignRole('siswa');

            echo "   ✅ Siswa created: {$siswa->email}\n";
            echo "      👤 Nama: {$siswa->nama} (ID: {$siswa->idyayasan})\n";
            echo "      📚 Kelas: " . ($siswa->kelas ? $siswa->kelas->nama_kelas : 'Tanpa Kelas') . "\n";
            echo "      💰 Status: {$siswa->status_pembayaran}\n";
            echo "      🔐 Spatie Role: " . $siswa->roles->pluck('name')->implode(', ') . "\n";
            echo "      🛡️  Guard: siswa\n\n";
        }

        echo "🎉 Pure Spatie Role System seeding completed!\n\n";
        $this->showLoginCredentials();
    }

    private function showLoginCredentials()
    {
        echo "📋 LOGIN CREDENTIALS (Pure Spatie - Laravel 12):\n";
        echo "══════════════════════════════════════════════════\n\n";

        echo "🌐 ADMIN LOGIN (/login - web guard):\n";
        echo "   👑 admin@skadaexam.test → Admin Panel (manage guru)\n";
        echo "   📍 Dashboard: /admin\n\n";

        echo "👨‍🏫 GURU LOGIN (/login/guru - guru guard):\n";
        echo "   📊 data.guru@skadaexam.test → Data Management\n";
        echo "   📝 naskah.guru@skadaexam.test → Naskah Management\n";
        echo "   🏢 ruangan.guru@skadaexam.test → Ruangan Management\n";
        echo "   👮 pengawas.guru@skadaexam.test → Pengawas Dashboard\n";
        echo "   🎯 koordinator.guru@skadaexam.test → Koordinator Dashboard\n";
        echo "   👨‍🏫 guru.default@skadaexam.test → Basic Guru Dashboard\n\n";

        echo "👨‍🎓 SISWA LOGIN (/login/siswa - siswa guard):\n";
        echo "   📚 siswa1@skadaexam.test → Student Dashboard\n\n";

        echo "🔑 All passwords: password123\n\n";

        echo "🎯 PURE SPATIE SYSTEM:\n";
        echo "   ✅ No native role fields\n";
        echo "   ✅ Only Spatie Permission roles\n";
        echo "   ✅ Multi-guard authentication\n";
        echo "   ✅ Laravel 12 compatible middleware\n\n";
    }
}
