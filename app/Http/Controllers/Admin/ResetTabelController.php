<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetTabelController extends Controller
{
    private const PRESETS = [
        'data_ujian' => [
            'label' => 'Data Ujian',
            'tables' => [
                'pelanggaran_ujian',
                'jawaban_siswa',
                'jawaban_siswas',
                'hasil_ujian',
                'enrollment_ujian',
                'jadwal_ujian_sesi_ruangan',
                'berita_acara_ujian',
                'jadwal_ujian',
            ],
        ],
        'ruangan' => [
            'label' => 'Ruangan',
            'tables' => [
                'sesi_ruangan_siswa',
                'sesi_ruangan',
                'ruangan',
            ],
        ],
        'naskah' => [
            'label' => 'Naskah',
            'tables' => [
                'soal',
                'bank_soal',
                'mapel',
            ],
        ],
        'data_siswa' => [
            'label' => 'Data Siswa',
            'tables' => [
                'siswa_tahun_ajaran',
                'siswa',
                'kelas',
            ],
        ],
        'paket_ujian' => [
            'label' => 'Paket Ujian',
            'tables' => [
                'paket_ujian',
                'tahun_ajaran',
            ],
        ],
    ];

    private const PROTECTED_TABLES = [
        'users',
        'roles',
        'permissions',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'school_settings',
        'migrations',
        'sessions',
        'password_reset_tokens',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'personal_access_tokens',
    ];

    private const RESET_ORDER = [
        'pelanggaran_ujian',
        'jawaban_siswa',
        'jawaban_siswas',
        'hasil_ujian',
        'enrollment_ujian',
        'jadwal_ujian_sesi_ruangan',
        'berita_acara_ujian',
        'jadwal_ujian',
        'sesi_ruangan_siswa',
        'sesi_template_siswa',
        'sesi_template_ruangan',
        'sesi_templates',
        'sesi_ruangan',
        'ruangan',
        'soal',
        'soal_ujians',
        'bank_soal',
        'mapel',
        'siswa_tahun_ajaran',
        'siswa',
        'kelas',
        'paket_ujian',
        'tahun_ajaran',
        'guru',
    ];

    public function index()
    {
        $availableTables = $this->availableTables();
        $presets = $this->existingPresets($availableTables);
        $tableCounts = collect($availableTables)
            ->mapWithKeys(fn(string $table) => [$table => DB::table($table)->count()])
            ->all();

        return view('admin.reset-tabel.index', [
            'availableTables' => $availableTables,
            'presets' => $presets,
            'tableCounts' => $tableCounts,
            'protectedTables' => self::PROTECTED_TABLES,
            'databaseName' => $this->currentDatabaseName(),
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'selected_tables' => 'required|array|min:1',
            'selected_tables.*' => 'required|string',
            'confirmation' => 'required|string|in:RESET',
        ], [
            'selected_tables.required' => 'Pilih minimal satu tabel yang akan direset.',
            'confirmation.in' => 'Ketik RESET untuk mengonfirmasi reset tabel.',
        ]);

        $availableTables = $this->availableTables();
        $selectedTables = collect($validated['selected_tables'])
            ->map(fn($table) => trim((string) $table))
            ->unique()
            ->values();

        $skipped = $selectedTables
            ->filter(fn($table) => !in_array($table, $availableTables, true))
            ->values()
            ->all();

        $tablesToReset = $this->sortForReset(
            $selectedTables
                ->filter(fn($table) => in_array($table, $availableTables, true))
                ->values()
                ->all()
        );

        if (empty($tablesToReset)) {
            return back()->with('error', 'Tidak ada tabel valid yang dapat direset.');
        }

        $summary = [];

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($tablesToReset, &$summary) {
                foreach ($tablesToReset as $table) {
                    $count = DB::table($table)->count();
                    DB::table($table)->delete();
                    $summary[$table] = $count;
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return redirect()
            ->route('admin.reset-tabel.index')
            ->with('success', 'Reset tabel berhasil dijalankan.')
            ->with('reset_summary', $summary)
            ->with('reset_skipped', $skipped);
    }

    public function resetDuplicateSesiRuangan(Request $request)
    {
        $request->validate([
            'duplicate_confirmation' => 'required|string|in:RESET',
        ], [
            'duplicate_confirmation.in' => 'Ketik RESET untuk mengonfirmasi hapus sesi duplikat.',
        ]);

        if (!Schema::hasTable('sesi_ruangan') || !Schema::hasColumn('sesi_ruangan', 'sumber')) {
            return back()->with('error', 'Tabel sesi_ruangan atau kolom sumber tidak tersedia.');
        }

        $duplicateIds = DB::table('sesi_ruangan')
            ->whereNotNull('sumber')
            ->where('sumber', '<>', 'sumber')
            ->pluck('id')
            ->all();

        if (empty($duplicateIds)) {
            return back()->with('success', 'Tidak ada sesi ruangan duplikat yang perlu dihapus.');
        }

        $summary = [];

        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($duplicateIds, &$summary) {
                $this->deleteWhereInIfTableExists($summary, 'pelanggaran_ujian', 'sesi_ruangan_id', $duplicateIds);
                $this->deleteWhereInIfTableExists($summary, 'berita_acara_ujian', 'sesi_ruangan_id', $duplicateIds);
                $this->deleteWhereInIfTableExists($summary, 'jadwal_ujian_sesi_ruangan', 'sesi_ruangan_id', $duplicateIds);
                $this->deleteWhereInIfTableExists($summary, 'sesi_ruangan_siswa', 'sesi_ruangan_id', $duplicateIds);

                if (Schema::hasTable('enrollment_ujian') && Schema::hasColumn('enrollment_ujian', 'sesi_ruangan_id')) {
                    $count = DB::table('enrollment_ujian')->whereIn('sesi_ruangan_id', $duplicateIds)->count();
                    DB::table('enrollment_ujian')->whereIn('sesi_ruangan_id', $duplicateIds)->update(['sesi_ruangan_id' => null]);
                    $summary['enrollment_ujian.sesi_ruangan_id_null'] = $count;
                }

                $summary['sesi_ruangan'] = DB::table('sesi_ruangan')->whereIn('id', $duplicateIds)->count();
                DB::table('sesi_ruangan')->whereIn('id', $duplicateIds)->delete();
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return redirect()
            ->route('admin.reset-tabel.index')
            ->with('success', 'Sesi ruangan duplikat berhasil dihapus.')
            ->with('reset_summary', $summary);
    }

    private function availableTables(): array
    {
        return collect($this->projectTables())
            ->reject(fn($table) => in_array($table, self::PROTECTED_TABLES, true))
            ->sortBy(fn($table) => $this->tableOrder($table))
            ->values()
            ->all();
    }

    private function projectTables(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            return collect($connection->select(
                'SELECT TABLE_NAME AS table_name
                 FROM INFORMATION_SCHEMA.TABLES
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_TYPE = ?',
                ['BASE TABLE']
            ))
                ->pluck('table_name')
                ->map(fn($table) => (string) $table)
                ->values()
                ->all();
        }

        return Schema::getTableListing();
    }

    private function currentDatabaseName(): string
    {
        $connection = DB::connection();

        if ($connection->getDriverName() === 'mysql') {
            return (string) $connection->selectOne('SELECT DATABASE() AS database_name')->database_name;
        }

        return (string) $connection->getDatabaseName();
    }

    private function existingPresets(array $availableTables): array
    {
        return collect(self::PRESETS)
            ->map(function (array $preset) use ($availableTables) {
                $preset['tables'] = collect($preset['tables'])
                    ->filter(fn($table) => in_array($table, $availableTables, true))
                    ->values()
                    ->all();

                return $preset;
            })
            ->filter(fn($preset) => !empty($preset['tables']))
            ->all();
    }

    private function sortForReset(array $tables): array
    {
        return collect($tables)
            ->sortBy(fn($table) => $this->tableOrder($table))
            ->values()
            ->all();
    }

    private function deleteWhereInIfTableExists(array &$summary, string $table, string $column, array $ids): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }

        $count = DB::table($table)->whereIn($column, $ids)->count();
        DB::table($table)->whereIn($column, $ids)->delete();
        $summary[$table] = $count;
    }

    private function tableOrder(string $table): int
    {
        $position = array_search($table, self::RESET_ORDER, true);

        return $position === false ? 500 + crc32($table) % 1000 : $position;
    }
}
