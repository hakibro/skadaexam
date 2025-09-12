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
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            $table->dropColumn([
                'token_login',
                'token_dibuat_pada',
                'token_digunakan_pada'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            $table->string('token_login', 20)->nullable()->after('status_enrollment');
            $table->timestamp('token_dibuat_pada')->nullable()->after('token_login');
            $table->timestamp('token_digunakan_pada')->nullable()->after('token_dibuat_pada');
        });
    }
};
