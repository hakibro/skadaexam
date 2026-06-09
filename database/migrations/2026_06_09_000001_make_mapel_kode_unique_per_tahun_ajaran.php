<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            if ($this->indexExists('mapel', 'mapel_kode_mapel_unique')) {
                $table->dropUnique('mapel_kode_mapel_unique');
            }

            if (!$this->indexExists('mapel', 'mapel_tahun_kode_unique')) {
                $table->unique(['tahun_ajaran_id', 'kode_mapel'], 'mapel_tahun_kode_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mapel', function (Blueprint $table) {
            if ($this->indexExists('mapel', 'mapel_tahun_kode_unique')) {
                $table->dropUnique('mapel_tahun_kode_unique');
            }

            if (!$this->indexExists('mapel', 'mapel_kode_mapel_unique')) {
                $table->unique('kode_mapel', 'mapel_kode_mapel_unique');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]))->isNotEmpty();
    }
};
