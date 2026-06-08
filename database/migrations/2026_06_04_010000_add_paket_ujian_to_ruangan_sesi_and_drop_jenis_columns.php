<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addNullablePaketUjianId('ruangan');
        $this->addNullablePaketUjianId('sesi_ruangan');

        if (Schema::hasColumn('jadwal_ujian', 'jenis_ujian')) {
            Schema::table('jadwal_ujian', function (Blueprint $table) {
                $table->dropColumn('jenis_ujian');
            });
        }

        if (Schema::hasColumn('bank_soal', 'jenis_soal')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->dropColumn('jenis_soal');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('jadwal_ujian', 'jenis_ujian')) {
            Schema::table('jadwal_ujian', function (Blueprint $table) {
                $table->string('jenis_ujian', 50)->nullable()->after('judul');
            });
        }

        if (!Schema::hasColumn('bank_soal', 'jenis_soal')) {
            Schema::table('bank_soal', function (Blueprint $table) {
                $table->string('jenis_soal', 20)->nullable()->after('tingkat');
            });
        }

        $this->dropPaketUjianId('sesi_ruangan');
        $this->dropPaketUjianId('ruangan');
    }

    private function addNullablePaketUjianId(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'paket_ujian_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->foreignId('paket_ujian_id')
                ->nullable()
                ->after('tahun_ajaran_id')
                ->constrained('paket_ujian')
                ->nullOnDelete();
        });
    }

    private function dropPaketUjianId(string $tableName): void
    {
        if (!Schema::hasColumn($tableName, 'paket_ujian_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropConstrainedForeignId('paket_ujian_id');
        });
    }
};
