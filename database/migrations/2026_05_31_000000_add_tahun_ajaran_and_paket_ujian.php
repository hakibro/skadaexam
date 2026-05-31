<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tahun_ajaran')) {
            Schema::create('tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->enum('status', ['draft', 'aktif', 'arsip'])->default('draft')->index();
                $table->boolean('is_active')->default(false)->index();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('paket_ujian')) {
            Schema::create('paket_ujian', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
                $table->string('nama');
                $table->date('tanggal_mulai')->nullable();
                $table->date('tanggal_selesai')->nullable();
                $table->enum('status', ['draft', 'aktif', 'arsip'])->default('draft')->index();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['tahun_ajaran_id', 'status']);
            });
        }

        $this->addNullableForeignId('kelas', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('mapel', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('bank_soal', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('ruangan', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('sesi_ruangan', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('jadwal_ujian', 'tahun_ajaran_id', 'tahun_ajaran');
        $this->addNullableForeignId('jadwal_ujian', 'paket_ujian_id', 'paket_ujian');

        if (!Schema::hasTable('siswa_tahun_ajaran')) {
            Schema::create('siswa_tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
                $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
                $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
                $table->string('status_siswa', 50)->default('aktif');
                $table->string('status_pembayaran', 50)->nullable();
                $table->string('rekomendasi', 20)->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->unique(['siswa_id', 'tahun_ajaran_id'], 'siswa_tahun_ajaran_unique');
                $table->index(['tahun_ajaran_id', 'kelas_id'], 'siswa_ta_kelas_index');
            });
        }

        $arsipYearId = DB::table('tahun_ajaran')->where('kode', 'ARSIP-LAMA')->value('id');
        if (!$arsipYearId) {
            $arsipYearId = DB::table('tahun_ajaran')->insertGetId([
                'kode' => 'ARSIP-LAMA',
                'nama' => 'Arsip Lama',
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
                'status' => 'arsip',
                'is_active' => false,
                'keterangan' => 'Migrasi otomatis data sebelum fitur tahun ajaran.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $arsipPaketId = DB::table('paket_ujian')
            ->where('tahun_ajaran_id', $arsipYearId)
            ->where('nama', 'Arsip Lama')
            ->value('id');

        if (!$arsipPaketId) {
            $arsipPaketId = DB::table('paket_ujian')->insertGetId([
                'tahun_ajaran_id' => $arsipYearId,
                'nama' => 'Arsip Lama',
                'tanggal_mulai' => null,
                'tanggal_selesai' => null,
                'status' => 'arsip',
                'keterangan' => 'Paket arsip otomatis untuk data sebelum fitur paket ujian.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['kelas', 'mapel', 'bank_soal', 'ruangan', 'sesi_ruangan', 'jadwal_ujian'] as $table) {
            if (Schema::hasColumn($table, 'tahun_ajaran_id')) {
                DB::table($table)->whereNull('tahun_ajaran_id')->update(['tahun_ajaran_id' => $arsipYearId]);
            }
        }

        if (Schema::hasColumn('jadwal_ujian', 'paket_ujian_id')) {
            DB::table('jadwal_ujian')->whereNull('paket_ujian_id')->update(['paket_ujian_id' => $arsipPaketId]);
        }

        if (Schema::hasTable('siswa_tahun_ajaran')) {
            $students = DB::table('siswa')->select('id', 'kelas_id', 'status_pembayaran', 'rekomendasi', 'catatan_rekomendasi')->get();

            foreach ($students as $student) {
                DB::table('siswa_tahun_ajaran')->updateOrInsert(
                    [
                        'siswa_id' => $student->id,
                        'tahun_ajaran_id' => $arsipYearId,
                    ],
                    [
                        'kelas_id' => $student->kelas_id,
                        'status_siswa' => 'aktif',
                        'status_pembayaran' => $student->status_pembayaran,
                        'rekomendasi' => $student->rekomendasi,
                        'catatan' => $student->catatan_rekomendasi,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        $this->dropForeignIdIfExists('jadwal_ujian', 'paket_ujian_id');
        $this->dropForeignIdIfExists('jadwal_ujian', 'tahun_ajaran_id');
        $this->dropForeignIdIfExists('sesi_ruangan', 'tahun_ajaran_id');
        $this->dropForeignIdIfExists('ruangan', 'tahun_ajaran_id');
        $this->dropForeignIdIfExists('bank_soal', 'tahun_ajaran_id');
        $this->dropForeignIdIfExists('mapel', 'tahun_ajaran_id');
        $this->dropForeignIdIfExists('kelas', 'tahun_ajaran_id');

        Schema::dropIfExists('siswa_tahun_ajaran');
        Schema::dropIfExists('paket_ujian');
        Schema::dropIfExists('tahun_ajaran');
    }

    private function addNullableForeignId(string $tableName, string $column, string $foreignTable): void
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column, $foreignTable) {
            $table->foreignId($column)->nullable()->after('id')->constrained($foreignTable)->nullOnDelete();
            $table->index($column);
        });
    }

    private function dropForeignIdIfExists(string $tableName, string $column): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->dropConstrainedForeignId($column);
        });
    }
};
