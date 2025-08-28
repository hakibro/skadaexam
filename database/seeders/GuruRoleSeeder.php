<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class GuruRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'data', 'ruangan', 'pengawas', 'koordinator', 'naskah'];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web', // penting!
            ]);
        }
    }
}
