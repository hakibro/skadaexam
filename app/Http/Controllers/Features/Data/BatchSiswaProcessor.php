<?php

namespace App\Http\Controllers\Features\Data;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Processor class to handle batch processing for student data
 */
class BatchSiswaProcessor
{
    /**
     * Process a batch of student data for import
     *
     * @param array $apiData The full API data
     * @param int $batchSize The size of each batch
     * @param int $startIndex The starting index for this batch
     * @return array Results of the batch processing
     */
    public static function processBatchImport(array $apiData, int $batchSize, int $startIndex)
    {
        $endIndex = min($startIndex + $batchSize, count($apiData));
        $batchData = array_slice($apiData, $startIndex, $batchSize);

        $results = [
            'batch_start' => $startIndex,
            'batch_end' => $endIndex - 1,
            'total_records' => count($apiData),
            'processed_in_batch' => count($batchData),
            'created_kelas' => 0,
            'updated_kelas' => 0,
            'created_siswa' => 0,
            'updated_siswa' => 0,
            'skipped' => 0,
            'errors' => [],
            'is_last_batch' => ($endIndex >= count($apiData))
        ];

        if (empty($batchData)) {
            return $results; // Early return if batch is empty
        }

        try {
            // Start a database transaction for this batch
            DB::beginTransaction();

            // Step 1: Extract and process unique kelas data
            $uniqueKelas = self::extractUniqueKelasFromApiData($batchData);
            $kelasResults = self::processKelasData($uniqueKelas);

            $results['created_kelas'] = $kelasResults['created'];
            $results['updated_kelas'] = $kelasResults['updated'];
            $results['errors'] = array_merge($results['errors'], $kelasResults['errors']);

            // Get all kelas for siswa assignment
            $allKelas = Kelas::pluck('id', 'nama_kelas')->toArray();

            // Step 2: Process student data in this batch
            $siswaResults = self::processSiswaData($batchData, $allKelas);

            $results['created_siswa'] = $siswaResults['created'];
            $results['updated_siswa'] = $siswaResults['updated'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            // Commit the transaction for this batch
            DB::commit();

            return $results;
        } catch (\Exception $e) {
            // If any error occurs, rollback the entire batch
            DB::rollBack();

            Log::error('Batch processing failed', [
                'batch_start' => $startIndex,
                'batch_size' => $batchSize,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $results['errors'][] = [
                'batch' => "Batch {$startIndex}-{$endIndex}",
                'error' => $e->getMessage()
            ];

            return $results;
        }
    }

    /**
     * Process a batch of students for syncing
     *
     * @param array $apiData The full API data
     * @param int $batchSize The size of each batch
     * @param int $startIndex The starting index for this batch
     * @return array Results of the batch processing
     */
    public static function processBatchSync(array $apiData, int $batchSize, int $startIndex)
    {
        $endIndex = min($startIndex + $batchSize, count($apiData));
        $batchData = array_slice($apiData, $startIndex, $batchSize);

        $results = [
            'batch_start' => $startIndex,
            'batch_end' => $endIndex - 1,
            'total_records' => count($apiData),
            'processed_in_batch' => count($batchData),
            'created_kelas' => 0,
            'updated_kelas' => 0,
            'created_siswa' => 0,
            'updated_siswa' => 0,
            'skipped' => 0,
            'errors' => [],
            'is_last_batch' => ($endIndex >= count($apiData))
        ];

        if (empty($batchData)) {
            return $results; // Early return if batch is empty
        }

        try {
            // Start a database transaction for this batch
            DB::beginTransaction();

            // Log the start of batch processing
            Log::info('Starting batch sync', [
                'batch_start' => $startIndex,
                'batch_end' => $endIndex - 1,
                'records_in_batch' => count($batchData)
            ]);

            // Process class data first
            $uniqueKelas = self::extractUniqueKelasFromApiData($batchData);
            $kelasResults = self::processKelasData($uniqueKelas);

            $results['created_kelas'] = $kelasResults['created'];
            $results['updated_kelas'] = $kelasResults['updated'];
            $results['errors'] = array_merge($results['errors'], $kelasResults['errors']);

            // Get all kelas for siswa assignment - get fresh data
            $allKelas = Kelas::pluck('id', 'nama_kelas')->toArray();

            // For sync, we prioritize updating over creating
            $siswaResults = self::processSiswaDataForSync($batchData, $allKelas);

            $results['created_siswa'] = $siswaResults['created'];
            $results['updated_siswa'] = $siswaResults['updated'];
            $results['skipped'] = $siswaResults['skipped'];
            $results['errors'] = array_merge($results['errors'], $siswaResults['errors']);

            // Commit the transaction for this batch
            DB::commit();

            // Log successful batch processing
            Log::info('Completed batch sync', [
                'batch_start' => $startIndex,
                'created_siswa' => $results['created_siswa'],
                'updated_siswa' => $results['updated_siswa']
            ]);

            return $results;
        } catch (\Exception $e) {
            // If any error occurs, rollback the entire batch
            DB::rollBack();

            Log::error('Batch sync processing failed', [
                'batch_start' => $startIndex,
                'batch_size' => $batchSize,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $results['errors'][] = [
                'batch' => "Batch {$startIndex}-{$endIndex}",
                'error' => $e->getMessage()
            ];

            return $results;
        }
    }

    /**
     * Extract unique kelas data from API data
     *
     * @param array $apiData The API data array
     * @return array An array of unique kelas data
     */
    private static function extractUniqueKelasFromApiData(array $apiData): array
    {
        $uniqueKelas = [];

        foreach ($apiData as $studentData) {
            if (!empty($studentData['kelas'])) {
                $kelasName = trim($studentData['kelas']);
                $uniqueKelas[$kelasName] = [
                    'nama_kelas' => $kelasName,
                    'tingkat' => self::extractTingkatFromKelas($kelasName),
                    'jurusan' => self::extractJurusanFromKelas($kelasName)
                ];
            }
        }

        return $uniqueKelas;
    }

    /**
     * Process and save kelas data
     *
     * @param array $uniqueKelas Array of unique kelas data
     * @return array Results of processing
     */
    private static function processKelasData(array $uniqueKelas): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        foreach ($uniqueKelas as $kelasName => $kelasData) {
            try {
                $existingKelas = Kelas::where('nama_kelas', $kelasName)->first();

                if ($existingKelas) {
                    // Update existing kelas if needed
                    if (
                        $existingKelas->tingkat !== $kelasData['tingkat'] ||
                        $existingKelas->jurusan !== $kelasData['jurusan']
                    ) {
                        $existingKelas->update([
                            'tingkat' => $kelasData['tingkat'],
                            'jurusan' => $kelasData['jurusan']
                        ]);

                        $results['updated']++;
                    }
                } else {
                    // Create new kelas
                    Kelas::create($kelasData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                Log::error("Error processing kelas {$kelasName}", [
                    'error' => $e->getMessage(),
                ]);
                $results['errors'][] = [
                    'kelas' => $kelasName,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Process and save student data for import
     *
     * @param array $apiData The API data array
     * @param array $allKelas Lookup array of kelas ID by name
     * @return array Results of processing
     */
    private static function processSiswaData(array $apiData, array $allKelas): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($apiData as $index => $studentData) {
            try {
                // Validate required data
                if (empty($studentData['idyayasan'])) {
                    $results['errors'][] = [
                        'index' => $index,
                        'error' => 'Missing idyayasan'
                    ];
                    $results['skipped']++;
                    continue;
                }

                // Get kelas_id
                $kelasId = null;
                if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                    $kelasId = $allKelas[trim($studentData['kelas'])];
                }

                // Check if siswa already exists
                $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                if ($existingSiswa) {
                    // Update existing siswa (preserving rekomendasi and catatan_rekomendasi)
                    $updateData = [
                        'nama' => $studentData['nama'] ?? $existingSiswa->nama,
                        'kelas_id' => $kelasId ?? $existingSiswa->kelas_id,
                        'status_pembayaran' => $studentData['status_pembayaran'] ?? $existingSiswa->status_pembayaran,
                        // Preserve existing recommendation data
                    ];

                    $existingSiswa->update($updateData);
                    $results['updated']++;
                } else {
                    // Create new siswa
                    Siswa::create([
                        'idyayasan' => $studentData['idyayasan'],
                        'nama' => $studentData['nama'] ?? null,
                        'kelas_id' => $kelasId,
                        'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                        'email' => $studentData['email'] ?? self::generateEmail($studentData['idyayasan']),
                        'password' => bcrypt('password'),
                        'rekomendasi' => 'tidak', // Default value
                        'catatan_rekomendasi' => null,
                    ]);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                Log::error("Error processing student {$index}", [
                    'error' => $e->getMessage(),
                    'student_data' => $studentData
                ]);

                $results['errors'][] = [
                    'idyayasan' => $studentData['idyayasan'] ?? "Unknown (index: {$index})",
                    'error' => $e->getMessage()
                ];
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Process and save student data for sync (with more careful update checks)
     *
     * @param array $apiData The API data array
     * @param array $allKelas Lookup array of kelas ID by name
     * @return array Results of processing
     */
    private static function processSiswaDataForSync(array $apiData, array $allKelas): array
    {
        $results = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        // Get all existing siswa IDs for more efficient checking
        $existingSiswaIds = Siswa::pluck('idyayasan')->toArray();
        $existingSiswaMap = array_flip($existingSiswaIds);

        foreach ($apiData as $index => $studentData) {
            try {
                if (empty($studentData['idyayasan'])) {
                    $results['errors'][] = [
                        'index' => $index,
                        'error' => 'Missing idyayasan'
                    ];
                    $results['skipped']++;
                    continue;
                }

                // Get kelas_id from lookup array
                $kelasId = null;
                if (!empty($studentData['kelas']) && isset($allKelas[trim($studentData['kelas'])])) {
                    $kelasId = $allKelas[trim($studentData['kelas'])];
                }

                // Check if student exists using our pre-loaded map (much more efficient)
                $studentExists = isset($existingSiswaMap[$studentData['idyayasan']]);

                if ($studentExists) {
                    // Fetch the existing record only when needed
                    $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();

                    if ($existingSiswa) {
                        // Check if update is needed
                        $needsUpdate = false;
                        $updateData = [];

                        if ($existingSiswa->nama !== ($studentData['nama'] ?? null)) {
                            $updateData['nama'] = $studentData['nama'];
                            $needsUpdate = true;
                        }

                        if ($existingSiswa->kelas_id !== $kelasId && $kelasId !== null) {
                            $updateData['kelas_id'] = $kelasId;
                            $needsUpdate = true;
                        }

                        if ($existingSiswa->status_pembayaran !== ($studentData['status_pembayaran'] ?? 'Belum Lunas')) {
                            $updateData['status_pembayaran'] = $studentData['status_pembayaran'];
                            $needsUpdate = true;
                        }

                        if ($needsUpdate) {
                            $existingSiswa->update($updateData);
                            $results['updated']++;
                        }
                    } else {
                        // This shouldn't happen, but just in case our map is incorrect
                        $results['skipped']++;
                        $results['errors'][] = [
                            'idyayasan' => $studentData['idyayasan'],
                            'error' => 'Student ID in map but not found in database'
                        ];
                    }
                } else {
                    // Create new siswa
                    try {
                        Siswa::create([
                            'idyayasan' => $studentData['idyayasan'],
                            'nama' => $studentData['nama'] ?? null,
                            'kelas_id' => $kelasId,
                            'status_pembayaran' => $studentData['status_pembayaran'] ?? 'Belum Lunas',
                            'email' => self::generateEmail($studentData['idyayasan']),
                            'password' => bcrypt('password'),
                            'rekomendasi' => 'tidak',
                            'catatan_rekomendasi' => null,
                        ]);
                        $results['created']++;

                        // Add to our tracking map to avoid duplicate attempts in this batch
                        $existingSiswaMap[$studentData['idyayasan']] = true;
                    } catch (\Illuminate\Database\QueryException $qe) {
                        // Handle duplicate key errors specifically
                        if (strpos($qe->getMessage(), 'Duplicate entry') !== false) {
                            // The student was probably created by another batch, try to update instead
                            $existingSiswa = Siswa::where('idyayasan', $studentData['idyayasan'])->first();
                            if ($existingSiswa) {
                                $updateData = [
                                    'nama' => $studentData['nama'] ?? $existingSiswa->nama,
                                    'kelas_id' => $kelasId ?? $existingSiswa->kelas_id,
                                    'status_pembayaran' => $studentData['status_pembayaran'] ?? $existingSiswa->status_pembayaran,
                                ];
                                $existingSiswa->update($updateData);
                                $results['updated']++;
                                $existingSiswaMap[$studentData['idyayasan']] = true;
                            } else {
                                throw $qe; // Re-throw if we can't find the record
                            }
                        } else {
                            throw $qe; // Re-throw for other database errors
                        }
                    }
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'idyayasan' => $studentData['idyayasan'] ?? "Unknown (index: {$index})",
                    'error' => $e->getMessage()
                ];
                $results['skipped']++;
            }
        }

        return $results;
    }

    /**
     * Extract tingkat (level) from kelas name
     */
    protected static function extractTingkatFromKelas($kelasName)
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        // Check format with space first (most common in API data like "X DPIB -", "X DKV 2")
        if (strpos($upperKelas, 'XII ') === 0) {
            return 'XII';
        } elseif (strpos($upperKelas, 'XI ') === 0) {
            return 'XI';
        } elseif (strpos($upperKelas, 'X ') === 0) {
            return 'X';
        }

        // Handle special case for "X - TEI" format (with dash)
        if (preg_match('/^(X|XI|XII)\s*-/i', $kelasName, $matches)) {
            return strtoupper($matches[1]);
        }

        // Check exact matches
        if ($upperKelas === 'XII') {
            return 'XII';
        } elseif ($upperKelas === 'XI') {
            return 'XI';
        } elseif ($upperKelas === 'X') {
            return 'X';
        }

        // Handle special cases for strings without spaces
        if (in_array($upperKelas, ['XIIPA1', 'XIIPS1', 'XIIPS2', 'XIIPA2', 'XIIPA3', 'XIIPS3'])) {
            return 'XI';
        }

        // For other formats without spaces, check prefixes carefully
        if (substr($upperKelas, 0, 3) === 'XII') {
            return 'XII';
        } elseif (substr($upperKelas, 0, 2) === 'XI') {
            return 'XI';
        } elseif (substr($upperKelas, 0, 1) === 'X') {
            return 'X';
        }

        // If no valid tingkat pattern is found, return null
        return null;
    }

    /**
     * Extract jurusan from kelas name
     */
    protected static function extractJurusanFromKelas($kelasName)
    {
        $kelasName = trim($kelasName);
        $upperKelas = strtoupper($kelasName);

        // Handle special case for "X - TEI" format (with dash)
        if (preg_match('/^(X|XI|XII)\s*-\s*([A-Z]+)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]); // Return the part after dash (TEI)
        }

        // For new format like "X DPIB -", "X DKV 2", "X BD 2"
        if (preg_match('/^(X|XI|XII)\s+([A-Z]+)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]); // Return the jurusan part
        }

        // Traditional patterns for IPA, IPS, etc.
        $patterns = [
            '/IPA\s*\d+/i' => 'IPA',
            '/IPS\s*\d+/i' => 'IPS',
            '/MIPA\s*\d+/i' => 'MIPA',
            '/BAHASA\s*\d+/i' => 'BAHASA',
            '/AGAMA\s*\d+/i' => 'AGAMA'
        ];

        foreach ($patterns as $pattern => $jurusan) {
            if (preg_match($pattern, $kelasName)) {
                return $jurusan;
            }
        }

        // Handle pattern without number (e.g. "XII IPA")
        $jurusanPatterns = [
            '/IPA(?!\w)/i' => 'IPA',
            '/IPS(?!\w)/i' => 'IPS',
            '/MIPA(?!\w)/i' => 'MIPA',
            '/BAHASA(?!\w)/i' => 'BAHASA',
            '/AGAMA(?!\w)/i' => 'AGAMA'
        ];

        foreach ($jurusanPatterns as $pattern => $jurusan) {
            if (preg_match($pattern, $kelasName)) {
                return $jurusan;
            }
        }

        // For combined strings without spaces like "XIIPA1"
        if (preg_match('/(X|XI|XII)(IPA|IPS|MIPA|BAHASA|AGAMA)/i', $kelasName, $matches)) {
            return strtoupper($matches[2]);
        }

        // Default jurusan if not found
        return 'UMUM';
    }

    /**
     * Generate email for siswa
     */
    private static function generateEmail($idyayasan)
    {
        return $idyayasan . '@smkdata.sch.id';
    }
}
