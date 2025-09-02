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
        Schema::create('enrollment_ujian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id'); // Foreign key will be added in a later migration
            $table->enum('status_enrollment', ['enrolled', 'completed', 'cancelled'])->default('enrolled');
            $table->string('token_login')->nullable(); // Token sekali pakai untuk login
            $table->timestamp('token_dibuat_pada')->nullable();
            $table->timestamp('token_digunakan_pada')->nullable();
            $table->timestamp('waktu_mulai_ujian')->nullable(); // Kapan siswa memulai ujian
            $table->timestamp('waktu_selesai_ujian')->nullable(); // Kapan siswa menyelesaikan ujian
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Unique constraint will be added after foreign keys are established
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_ujian');
    }
};
