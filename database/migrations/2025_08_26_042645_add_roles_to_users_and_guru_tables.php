<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah role ke tabel users (hanya untuk admin)
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 50)->default('admin')->after('email');
        });

        // Tambah role ke tabel guru (untuk berbagai role guru)
        Schema::table('guru', function (Blueprint $table) {
            $table->enum('role', ['data', 'ruangan', 'pengawas', 'koordinator', 'naskah', 'guru'])->default('guru')->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
