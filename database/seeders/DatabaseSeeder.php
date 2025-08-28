<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Buat semua role dulu
        $this->call(GuruRoleSeeder::class);

        // 2️⃣ Buat user default (admin, guru, siswa)
        $this->call(UserSeeder::class);

        // 3️⃣ Buat guru profil spesifik
        $this->call(GuruSeeder::class);

        // 3️⃣ Buat guru profil spesifik
        $this->call(GuruSeeder::class);
    }
}
