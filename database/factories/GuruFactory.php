<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Guru;
use Spatie\Permission\Models\Role;

class GuruFactory extends Factory
{
    protected $model = Guru::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->name(),
            'nip' => $this->faker->unique()->numerify('10###'),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
        ];
    }

    // assign random role
    public function withRandomRole()
    {
        return $this->afterCreating(function (Guru $guru) {
            $roles = Role::pluck('name')->toArray(); // ambil semua role yang ada
            $randomRole = $this->faker->randomElement($roles);
            $guru->assignRole($randomRole);
        });
    }
}
