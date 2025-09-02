<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom pengawas_id di sesi_ruangan
        // Schema::table('sesi_ruangan', function (Blueprint $table) {
        //     if (!Schema::hasColumn('sesi_ruangan', 'pengawas_id')) {
        //         $table->unsignedBigInteger('pengawas_id')->nullable()->after('ruangan_id');

        //         $table->foreign('pengawas_id')
        //             ->references('id')
        //             ->on('guru')
        //             ->onDelete('set null');
        //     }
        // });

        // Tambah kolom sesi_ruangan_id di enrollment_ujian
        Schema::table('enrollment_ujian', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollment_ujian', 'sesi_ruangan_id')) {
                $table->unsignedBigInteger('sesi_ruangan_id')->nullable()->after('id');

                $table->foreign('sesi_ruangan_id')
                    ->references('id')
                    ->on('sesi_ruangan')
                    ->onDelete('cascade');
            }

            // Optional: kalau ruangan_id lama tidak dipakai, bisa di-drop
            if (Schema::hasColumn('enrollment_ujian', 'ruangan_id')) {
                $table->dropForeign(['ruangan_id']); // pastikan constraint name cocok
                $table->dropColumn('ruangan_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sesi_ruangan', function (Blueprint $table) {
            if (Schema::hasColumn('sesi_ruangan', 'pengawas_id')) {
                $table->dropForeign(['pengawas_id']);
                $table->dropColumn('pengawas_id');
            }
        });

        Schema::table('enrollment_ujian', function (Blueprint $table) {
            if (Schema::hasColumn('enrollment_ujian', 'sesi_ruangan_id')) {
                $table->dropForeign(['sesi_ruangan_id']);
                $table->dropColumn('sesi_ruangan_id');
            }

            // restore ruangan_id kalau rollback
            if (!Schema::hasColumn('enrollment_ujian', 'ruangan_id')) {
                $table->unsignedBigInteger('ruangan_id')->nullable()->after('sesi_ujian_id');
                $table->foreign('ruangan_id')
                    ->references('id')
                    ->on('ruangan')
                    ->onDelete('set null');
            }
        });
    }
};
