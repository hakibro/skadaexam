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
        // Create sesi_template_ruangan table for default room assignments per template
        Schema::create('sesi_template_ruangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_template_id')->constrained('sesi_templates')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangan')->cascadeOnDelete();
            $table->integer('kapasitas_override')->nullable(); // override default room capacity
            $table->json('pengaturan_ruangan')->nullable(); // room-specific settings
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['sesi_template_id', 'ruangan_id']);
        });

        // Create sesi_template_siswa table for default student assignments per template-room
        Schema::create('sesi_template_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesi_template_id')->constrained('sesi_templates')->cascadeOnDelete();
            $table->foreignId('ruangan_id')->constrained('ruangan')->cascadeOnDelete();
            $table->foreignId('siswa_id')->nullable()->constrained('siswa')->cascadeOnDelete(); // null = auto-assign
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->cascadeOnDelete(); // for kelas-based assignment
            $table->integer('priority')->default(0); // assignment priority (higher = preferred)
            $table->enum('assignment_type', ['fixed', 'flexible', 'auto'])->default('auto');
            $table->json('assignment_rules')->nullable(); // rules for auto assignment
            $table->timestamps();

            $table->index(['sesi_template_id', 'ruangan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_template_siswa');
        Schema::dropIfExists('sesi_template_ruangan');
    }
};
