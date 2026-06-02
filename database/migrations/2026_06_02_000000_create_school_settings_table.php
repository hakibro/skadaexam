<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('school_settings')->insert([
            ['key' => 'nama_sekolah', 'value' => 'SMK Daruttaqwa', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'alamat', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'npsn', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'nss', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'kode_pos', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'telepon', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'email', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'website', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'kepala_sekolah', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'info_lain', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'logo_path', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
