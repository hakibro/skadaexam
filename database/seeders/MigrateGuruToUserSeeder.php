<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MigrateGuruToUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Make sure all required roles exist in web guard
        $roles = ['admin', 'data', 'naskah', 'ruangan', 'pengawas', 'koordinator', 'guru'];

        foreach ($roles as $role) {
            Role::findOrCreate($role, 'web');
        }

        // Get all guru from the guru table
        $gurus = Guru::all();

        foreach ($gurus as $guru) {
            // Create a new user for each guru
            $user = User::create([
                'name' => $guru->nama,
                'email' => $guru->email,
                'password' => $guru->password, // Copy hashed password directly
            ]);

            // Get guru roles from previous guard
            $guruRoles = \Spatie\Permission\Models\Role::query()
                ->join('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('model_has_roles.model_id', $guru->id)
                ->where('model_has_roles.model_type', get_class($guru))
                ->where('roles.guard_name', 'guru')
                ->pluck('roles.name')
                ->toArray();

            if (empty($guruRoles)) {
                // If no specific role, assign default guru role
                $user->assignRole('guru');
            } else {
                // Assign all roles to new user
                $user->assignRole($guruRoles);
            }

            // Link guru to user
            $guru->user_id = $user->id;
            $guru->save();

            $this->command->info("Migrated guru {$guru->nama} with email {$guru->email} to users table with ID {$user->id}");
        }

        $this->command->info("Migration completed: {$gurus->count()} guru accounts migrated to users table.");
    }
}
