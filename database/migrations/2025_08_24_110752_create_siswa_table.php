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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('idyayasan')->unique();    // id yayasan siswa
            $table->string('first_name');             // nama depan
            $table->string('last_name');              // nama belakang
            $table->string('email')->unique();        // email siswa
            $table->string('kelas')->nullable();      // kelas
            $table->enum('pembayaran', ['lunas', 'belum lunas', 'rekomendasi'])->default('belum lunas'); // status pembayaran
            $table->string('password');               // password siswa
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
