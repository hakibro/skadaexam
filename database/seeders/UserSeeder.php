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
        echo "🚀 Starting seeding process...\n\n";

        // ===== BUAT ADMIN (Tabel Users - Hanya Role Admin) =====
        $admin = User::updateOrCreate(
            ['email' => 'admin@skadaexam.test'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
        echo "✅ Admin created: {$admin->email} (Role: {$admin->role})\n";

        // ===== BUAT GURU dengan Berbagai Role =====
        $guruRoles = [
            [
                'email' => 'data@skadaexam.test',
                'nama' => 'Admin Data',
                'nip' => '1001',
                'role' => 'data'
            ],
            [
                'email' => 'ruangan@skadaexam.test',
                'nama' => 'Admin Ruangan',
                'nip' => '1002',
                'role' => 'ruangan'
            ],
            [
                'email' => 'pengawas@skadaexam.test',
                'nama' => 'Guru Pengawas',
                'nip' => '1003',
                'role' => 'pengawas'
            ],
            [
                'email' => 'koordinator@skadaexam.test',
                'nama' => 'Koordinator Ujian',
                'nip' => '1004',
                'role' => 'koordinator'
            ],
            [
                'email' => 'naskah@skadaexam.test',
                'nama' => 'Admin Naskah',
                'nip' => '1005',
                'role' => 'naskah'
            ],
            [
                'email' => 'guru@skadaexam.test',
                'nama' => 'Guru Biasa',
                'nip' => '1006',
                'role' => 'guru'
            ],
        ];

        foreach ($guruRoles as $guruData) {
            $guru = Guru::updateOrCreate(
                ['email' => $guruData['email']],
                [
                    'nama' => $guruData['nama'],
                    'nip' => $guruData['nip'],
                    'password' => Hash::make('password123'),
                    'role' => $guruData['role'],
                ]
            );
            echo "✅ Guru created: {$guru->email} (Role: {$guru->role})\n";
        }

        // ===== BUAT SISWA (Tanpa Role) =====
        $siswaData = [
            [
                'email' => 'siswa1@skadaexam.test',
                'idyayasan' => 'SMA001',
                'first_name' => 'Ahmad',
                'last_name' => 'Siswa',
                'kelas' => '12-IPA-1',
                'pembayaran' => 'lunas'
            ],
            [
                'email' => 'siswa2@skadaexam.test',
                'idyayasan' => 'SMA002',
                'first_name' => 'Siti',
                'last_name' => 'Siswi',
                'kelas' => '12-IPS-1',
                'pembayaran' => 'belum lunas'
            ],
        ];

        foreach ($siswaData as $siswaInfo) {
            $siswa = Siswa::updateOrCreate(
                ['email' => $siswaInfo['email']],
                [
                    'idyayasan' => $siswaInfo['idyayasan'],
                    'first_name' => $siswaInfo['first_name'],
                    'last_name' => $siswaInfo['last_name'],
                    'kelas' => $siswaInfo['kelas'],
                    'pembayaran' => $siswaInfo['pembayaran'],
                    'password' => Hash::make('password123'),
                ]
            );
            echo "✅ Siswa created: {$siswa->email} ({$siswa->full_name})\n";
        }

        echo "\n🎉 Seeding completed successfully!\n\n";

        echo "📋 LOGIN CREDENTIALS:\n";
        echo "══════════════════════════════════════════════════\n";
        echo "👨‍💼 ADMIN (Login: /login)\n";
        echo "   📧 admin@skadaexam.test (password: password123)\n\n";

        echo "👨‍🏫 GURU (Login: /login/guru)\n";
        echo "   📊 data@skadaexam.test → /guru/data/dashboard\n";
        echo "   🏢 ruangan@skadaexam.test → /guru/ruangan/dashboard\n";
        echo "   👮 pengawas@skadaexam.test → /guru/pengawas/dashboard\n";
        echo "   🎯 koordinator@skadaexam.test → /guru/koordinator/dashboard\n";
        echo "   📝 naskah@skadaexam.test → /guru/naskah/dashboard\n";
        echo "   👨‍🏫 guru@skadaexam.test → /guru/dashboard\n";
        echo "   (All password: password123)\n\n";

        echo "👨‍🎓 SISWA (Login: /login/siswa)\n";
        echo "   📧 siswa1@skadaexam.test (password: password123)\n";
        echo "   📧 siswa2@skadaexam.test (password: password123)\n\n";
    }
}
