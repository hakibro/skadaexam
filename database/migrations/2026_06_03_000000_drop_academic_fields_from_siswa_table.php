<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('siswa')) {
            return;
        }

        $hasLegacyColumns = Schema::hasColumn('siswa', 'kelas_id')
            || Schema::hasColumn('siswa', 'status_pembayaran')
            || Schema::hasColumn('siswa', 'rekomendasi')
            || Schema::hasColumn('siswa', 'catatan_rekomendasi');

        if ($hasLegacyColumns && Schema::hasTable('siswa_tahun_ajaran')) {
            $tahunAjaranId = DB::table('tahun_ajaran')->where('is_active', true)->value('id')
                ?: DB::table('tahun_ajaran')->orderByDesc('id')->value('id');

            if ($tahunAjaranId) {
                DB::table('siswa')
                    ->select('id', 'kelas_id', 'status_pembayaran', 'rekomendasi', 'catatan_rekomendasi')
                    ->orderBy('id')
                    ->chunkById(500, function ($students) use ($tahunAjaranId) {
                        foreach ($students as $student) {
                            DB::table('siswa_tahun_ajaran')->updateOrInsert(
                                [
                                    'siswa_id' => $student->id,
                                    'tahun_ajaran_id' => $tahunAjaranId,
                                ],
                                [
                                    'kelas_id' => $student->kelas_id,
                                    'status_siswa' => 'aktif',
                                    'status_pembayaran' => $student->status_pembayaran ?: 'Belum Lunas',
                                    'rekomendasi' => $student->rekomendasi ?: 'tidak',
                                    'catatan' => $student->catatan_rekomendasi,
                                    'updated_at' => now(),
                                    'created_at' => now(),
                                ]
                            );
                        }
                    });
            }
        }

        $this->dropIndexIfExists('siswa', 'idx_siswa_kelas_deleted');
        $this->dropForeignIfExists('siswa', 'siswa_kelas_id_foreign');

        Schema::table('siswa', function (Blueprint $table) {
            foreach (['kelas_id', 'status_pembayaran', 'rekomendasi', 'catatan_rekomendasi'] as $column) {
                if (Schema::hasColumn('siswa', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('siswa')) {
            return;
        }

        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'kelas_id')) {
                $table->foreignId('kelas_id')->nullable()->after('password')->constrained('kelas')->nullOnDelete();
            }

            if (!Schema::hasColumn('siswa', 'status_pembayaran')) {
                $table->enum('status_pembayaran', ['Lunas', 'Belum Lunas'])->default('Belum Lunas')->after('kelas_id');
            }

            if (!Schema::hasColumn('siswa', 'rekomendasi')) {
                $table->enum('rekomendasi', ['ya', 'tidak'])->default('tidak')->after('status_pembayaran');
            }

            if (!Schema::hasColumn('siswa', 'catatan_rekomendasi')) {
                $table->text('catatan_rekomendasi')->nullable()->after('rekomendasi');
            }
        });

        if (Schema::hasTable('siswa_tahun_ajaran')) {
            DB::table('siswa')
                ->select('id')
                ->orderBy('id')
                ->chunkById(500, function ($students) {
                    foreach ($students as $student) {
                        $record = DB::table('siswa_tahun_ajaran')
                            ->where('siswa_id', $student->id)
                            ->orderByDesc('tahun_ajaran_id')
                            ->first();

                        if ($record) {
                            DB::table('siswa')->where('id', $student->id)->update([
                                'kelas_id' => $record->kelas_id,
                                'status_pembayaran' => $record->status_pembayaran ?: 'Belum Lunas',
                                'rekomendasi' => $record->rekomendasi ?: 'tidak',
                                'catatan_rekomendasi' => $record->catatan,
                            ]);
                        }
                    }
                });
        }

        $this->dropIndexIfExists('siswa', 'idx_siswa_kelas_deleted');
        if (Schema::hasColumn('siswa', 'kelas_id') && Schema::hasColumn('siswa', 'deleted_at')) {
            Schema::table('siswa', function (Blueprint $table) {
                $table->index(['kelas_id', 'deleted_at'], 'idx_siswa_kelas_deleted');
            });
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();

        if ($exists) {
            DB::statement("DROP INDEX {$index} ON {$table}");
        }
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        $exists = DB::table('information_schema.table_constraints')
            ->whereRaw('constraint_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('constraint_name', $foreign)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$foreign}");
        }
    }
};
