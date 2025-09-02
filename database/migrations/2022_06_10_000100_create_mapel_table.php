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
        Schema::create('mapel', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel');
            $table->string('tingkat')->nullable(); // kelas 10, 11, 12
            $table->string('jurusan')->nullable(); // ipa, ips, dll
            $table->text('deskripsi')->nullable();
            $table->string('status', 20)->default('aktif'); // aktif, tidak_aktif
            $table->timestamps();

            $table->index(['tingkat', 'jurusan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};
