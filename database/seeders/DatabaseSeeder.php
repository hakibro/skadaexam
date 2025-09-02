<?php
// filepath: database\seeders\DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        echo "🌱 Starting SKADA Exam Database Seeding...\n\n";

        // Run the essential seeders in proper order
        $this->call([
            KelasSeeder::class,           // Seed kelas first for foreign key references
            UserSeeder::class,            // This will call RoleSeeder internally
            GuruSeeder::class,            // Create teachers for supervision
            SiswaSeeder::class,           // Create students
            MapelSeeder::class,           // Create subjects
            BankSoalSeeder::class,        // Create question banks (references users & mapel)
            JadwalUjianSeeder::class,     // Create exam schedules (references users, mapel, bank_soal)
            RuanganSeeder::class,         // Create exam rooms
            SesiTemplateSeeder::class,    // Create session templates
            SesiRuanganSeeder::class,     // Create session rooms (references ruangan, guru, jadwal_ujian)
            // SesiRuanganSiswaSeeder::class, // Create attendance records for session rooms
            EnrollmentUjianSeeder::class, // Create student enrollments & attendance (references sesi_ruangan, siswa)
            HasilUjianSeeder::class,      // Create exam results (references enrollment, jadwal_ujian)
            BeritaAcaraUjianSeeder::class, // Create exam reports (references sesi_ruangan, guru)
        ]);

        echo "\n🎉 Database seeding completed!\n";
        echo "🎯 SKADA Exam system is ready with test data.\n\n";
        echo "📊 Data Summary:\n";
        echo "   - Users (Admin): " . \App\Models\User::count() . "\n";
        echo "   - Guru (Teachers): " . \App\Models\Guru::count() . "\n";
        echo "   - Siswa (Students): " . \App\Models\Siswa::count() . "\n";
        echo "   - Kelas (Classes): " . \App\Models\Kelas::count() . "\n";
        echo "   - Mapel (Subjects): " . \App\Models\Mapel::count() . "\n";
        echo "   - Bank Soal: " . \App\Models\BankSoal::count() . "\n";
        echo "   - Jadwal Ujian: " . \App\Models\JadwalUjian::count() . "\n";
        echo "   - Ruangan: " . \App\Models\Ruangan::count() . "\n";
        echo "   - Sesi Ruangan: " . \App\Models\SesiRuangan::count() . "\n";
        echo "   - Enrollment Ujian: " . \App\Models\EnrollmentUjian::count() . "\n";
        echo "   - Sesi Ruangan Siswa: " . \App\Models\SesiRuanganSiswa::count() . "\n";
        echo "   - Hasil Ujian: " . \App\Models\HasilUjian::count() . "\n";
        echo "   - Berita Acara: " . \App\Models\BeritaAcaraUjian::count() . "\n\n";
    }
}
