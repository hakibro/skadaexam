<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaTahunAjaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SiswaQuickSyncService
{
    public function __construct(
        private SikeuApiService $sikeuApiService,
        private TahunAjaranService $tahunAjaranService
    ) {
    }

    public function sync(?callable $progress = null): array
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $this->report($progress, ['progress' => 0, 'status' => 'starting', 'message' => 'Starting sync process...']);

        $activeYear = $this->tahunAjaranService->ensureActive();
        Log::info('Starting SIKEU API sync process');

        $this->report($progress, ['message' => 'Fetching latest data from SIKEU API...', 'progress' => 10]);
        $apiResult = $this->sikeuApiService->fetchSiswaData();

        if (!$apiResult['success']) {
            $message = 'API Error: ' . ($apiResult['error'] ?? 'Unknown error');
            $this->report($progress, ['status' => 'error', 'message' => $message]);

            return [
                'success' => false,
                'error' => $message,
                'data' => [],
            ];
        }

        $apiData = $apiResult['data'] ?? [];

        if (empty($apiData)) {
            $message = 'No data received from API';
            $this->report($progress, ['status' => 'warning', 'message' => $message]);

            return [
                'success' => false,
                'warning' => $message,
                'data' => [],
            ];
        }

        $results = [
            'total_api_records' => count($apiData),
            'total_db_records' => Siswa::count(),
            'created_kelas' => 0,
            'updated_kelas' => 0,
            'updated_siswa' => 0,
            'created_siswa' => 0,
            'restored_siswa' => 0,
            'deleted_siswa' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            $this->report($progress, ['message' => 'Extracting unique class data...', 'progress' => 20]);
            $uniqueKelas = $this->extractUniqueKelasFromApiData($apiData);
            Log::info('Extracted unique kelas for sync', ['count' => count($uniqueKelas)]);

            $this->report($progress, ['message' => 'Processing class data...', 'progress' => 25]);
            $existingKelas = Kelas::where('tahun_ajaran_id', $activeYear->id)
                ->get()
                ->keyBy('nama_kelas');

            $kelasToCreate = [];
            $kelasToUpdate = [];

            foreach ($uniqueKelas as $kelasName => $kelasData) {
                if ($existingKelas->has($kelasName)) {
                    $existing = $existingKelas->get($kelasName);
                    if ($existing->tingkat !== $kelasData['tingkat'] || $existing->jurusan !== $kelasData['jurusan']) {
                        $kelasToUpdate[$existing->id] = [
                            'tingkat' => $kelasData['tingkat'],
                            'jurusan' => $kelasData['jurusan'],
                            'updated_at' => now(),
                        ];
                    }
                } else {
                    $kelasToCreate[] = array_merge($kelasData, [
                        'tahun_ajaran_id' => $activeYear->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            if (!empty($kelasToCreate)) {
                Kelas::insert($kelasToCreate);
                $results['created_kelas'] = count($kelasToCreate);
            }

            foreach ($kelasToUpdate as $kelasId => $updateData) {
                Kelas::where('id', $kelasId)->update($updateData);
            }
            $results['updated_kelas'] = count($kelasToUpdate);

            $allKelas = Kelas::where('tahun_ajaran_id', $activeYear->id)
                ->pluck('id', 'nama_kelas')
                ->toArray();

            $this->report($progress, ['message' => 'Analyzing student data differences...', 'progress' => 40]);
            $apiStudents = collect($apiData)
                ->filter(fn($student) => !empty($student['idyayasan']))
                ->keyBy('idyayasan');

            $this->report($progress, ['message' => 'Synchronizing student data...', 'progress' => 45]);

            $chunkSize = 200;
            $totalChunks = (int) ceil(count($apiData) / $chunkSize);
            $progressStep = 45 / max($totalChunks, 1);
            $currentProgress = 45;

            $existingSiswa = Siswa::withTrashed()
                ->get(['id', 'idyayasan', 'nama', 'deleted_at'])
                ->keyBy('idyayasan');

            $hashedPassword = bcrypt('password');
            $siswaResults = ['created' => 0, 'updated' => 0, 'restored' => 0, 'deleted' => 0, 'skipped' => 0, 'errors' => []];
            $chunkIndex = 0;

            foreach (array_chunk($apiData, $chunkSize) as $chunk) {
                $chunkIndex++;
                $currentProgress += $progressStep;
                $this->report($progress, [
                    'progress' => min(88, (int) $currentProgress),
                    'message' => "Syncing students... Batch {$chunkIndex}/{$totalChunks}",
                ]);

                $siswaToCreate = [];
                $siswaToUpdate = [];
                $siswaToRestore = [];
                $pivotDataByChunk = [];
                $chunkStudentDataMap = [];

                foreach ($chunk as $studentData) {
                    try {
                        if (empty($studentData['idyayasan'])) {
                            $siswaResults['errors'][] = 'Missing idyayasan';
                            $siswaResults['skipped']++;
                            continue;
                        }

                        $idyayasan = (string) $studentData['idyayasan'];
                        $chunkStudentDataMap[$idyayasan] = $studentData;
                        $kelasId = $this->kelasIdForStudent($studentData, $allKelas);

                        if ($existingSiswa->has($idyayasan)) {
                            $siswa = $existingSiswa->get($idyayasan);

                            if (!empty($siswa->deleted_at)) {
                                $siswaToRestore[] = $siswa->id;
                                $siswaResults['restored']++;
                            }

                            if ($siswa->nama !== ($studentData['nama'] ?? null)) {
                                $siswaToUpdate[$siswa->id] = [
                                    'nama' => $studentData['nama'] ?? null,
                                    'updated_at' => now(),
                                ];
                                $siswaResults['updated']++;
                            }

                            $pivotDataByChunk[$siswa->id] = [
                                'siswa_id' => $siswa->id,
                                'tahun_ajaran_id' => $activeYear->id,
                                'kelas_id' => $kelasId,
                                'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                            ];
                        } else {
                            $siswaToCreate[] = [
                                'idyayasan' => $idyayasan,
                                'nama' => $studentData['nama'] ?? null,
                                'email' => $studentData['email'] ?? $this->generateEmail($idyayasan),
                                'password' => $hashedPassword,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $siswaResults['created']++;
                        }
                    } catch (\Throwable $e) {
                        $siswaResults['errors'][] = 'Error processing ' . ($studentData['idyayasan'] ?? 'unknown') . ': ' . $e->getMessage();
                        $siswaResults['skipped']++;
                    }
                }

                if (!empty($siswaToRestore)) {
                    Siswa::withTrashed()->whereIn('id', $siswaToRestore)->restore();
                }

                foreach ($siswaToCreate as $siswaData) {
                    $newSiswa = Siswa::create($siswaData);
                    $existingSiswa->put($newSiswa->idyayasan, $newSiswa);
                    $studentData = $chunkStudentDataMap[$siswaData['idyayasan']] ?? null;

                    if ($studentData) {
                        $pivotDataByChunk[$newSiswa->id] = [
                            'siswa_id' => $newSiswa->id,
                            'tahun_ajaran_id' => $activeYear->id,
                            'kelas_id' => $this->kelasIdForStudent($studentData, $allKelas),
                            'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                        ];
                    }
                }

                foreach ($siswaToUpdate as $siswaId => $data) {
                    Siswa::where('id', $siswaId)->update($data);
                }

                foreach ($pivotDataByChunk as $pivotData) {
                    SiswaTahunAjaran::updateOrCreate(
                        [
                            'siswa_id' => $pivotData['siswa_id'],
                            'tahun_ajaran_id' => $pivotData['tahun_ajaran_id'],
                        ],
                        array_merge($pivotData, [
                            'status_siswa' => 'aktif',
                            'updated_at' => now(),
                        ])
                    );
                }
            }

            $apiIdyayasan = $apiStudents->keys()->all();
            if (!empty($apiIdyayasan)) {
                $siswaResults['deleted'] = Siswa::whereNotNull('idyayasan')
                    ->whereNotIn('idyayasan', $apiIdyayasan)
                    ->delete();
            }

            $results['updated_siswa'] = $siswaResults['updated'];
            $results['created_siswa'] = $siswaResults['created'];
            $results['restored_siswa'] = $siswaResults['restored'];
            $results['deleted_siswa'] = $siswaResults['deleted'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            $this->report($progress, ['progress' => 95, 'message' => 'Finalizing sync...']);
            DB::commit();

            $message = sprintf(
                'Sync completed! Kelas: %d created, %d updated | Students: %d created, %d updated, %d restored, %d deleted, %d skipped',
                $results['created_kelas'],
                $results['updated_kelas'],
                $siswaResults['created'],
                $siswaResults['updated'],
                $siswaResults['restored'],
                $siswaResults['deleted'],
                $siswaResults['skipped']
            );

            $this->report($progress, ['progress' => 100, 'status' => 'completed', 'message' => $message]);
            Log::info('SIKEU API sync completed', $results);

            return [
                'success' => true,
                'data' => $results,
                'message' => $message,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();

            $message = 'Sync failed: ' . $e->getMessage();
            $this->report($progress, ['status' => 'error', 'message' => $message]);
            Log::error('SIKEU API sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $message,
                'data' => $results,
            ];
        }
    }

    private function report(?callable $progress, array $payload): void
    {
        if ($progress) {
            $progress($payload);
        }
    }

    private function kelasIdForStudent(array $studentData, array $allKelas): ?int
    {
        if (empty($studentData['kelas'])) {
            return null;
        }

        return $allKelas[trim($studentData['kelas'])] ?? null;
    }

    private function extractUniqueKelasFromApiData(array $apiData): array
    {
        $uniqueKelas = [];

        foreach ($apiData as $studentData) {
            if (!empty($studentData['kelas'])) {
                $kelasName = trim($studentData['kelas']);
                $uniqueKelas[$kelasName] = [
                    'nama_kelas' => $kelasName,
                    'tingkat' => $this->extractTingkatFromKelas($kelasName),
                    'jurusan' => $this->extractJurusanFromKelas($kelasName),
                ];
            }
        }

        return $uniqueKelas;
    }

    private function extractTingkatFromKelas(string $kelasName): ?string
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        if (strpos($upperKelas, 'XII ') === 0) {
            return 'XII';
        }
        if (strpos($upperKelas, 'XI ') === 0) {
            return 'XI';
        }
        if (strpos($upperKelas, 'X ') === 0) {
            return 'X';
        }
        if (preg_match('/^(X|XI|XII)\s*-/i', $kelasName, $matches)) {
            return strtoupper($matches[1]);
        }
        if (in_array($upperKelas, ['XII', 'XI', 'X'], true)) {
            return $upperKelas;
        }
        if (in_array($upperKelas, ['XIIPA1', 'XIIPS1', 'XIIPS2', 'XIIPA2', 'XIIPA3', 'XIIPS3'], true)) {
            return 'XI';
        }
        if (substr($upperKelas, 0, 3) === 'XII') {
            return 'XII';
        }
        if (substr($upperKelas, 0, 2) === 'XI') {
            return 'XI';
        }
        if (substr($upperKelas, 0, 1) === 'X') {
            return 'X';
        }

        return null;
    }

    private function extractJurusanFromKelas(string $kelasName): string
    {
        $kelasName = trim($kelasName);
        $jurusanList = ['BD', 'DKV', 'DPIB', 'TKJ', 'TKR', 'TSM'];

        if (preg_match('/^(X|XI|XII)\s*-\s*([A-Z]+)/i', $kelasName, $matches)) {
            $jurusan = strtoupper($matches[2]);
            return in_array($jurusan, $jurusanList, true) ? $jurusan : 'UMUM';
        }

        if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', $kelasName, $matches)) {
            $jurusan = strtoupper($matches[2]);
            return in_array($jurusan, $jurusanList, true) ? $jurusan : 'UMUM';
        }

        $jurusanPattern = implode('|', $jurusanList);
        if (preg_match('/(X|XI|XII)(' . $jurusanPattern . ')/i', $kelasName, $matches)) {
            return strtoupper($matches[2]);
        }

        return 'UMUM';
    }

    private function generateEmail(string $idyayasan): string
    {
        return $idyayasan . '@smkdata.sch.id';
    }
}
