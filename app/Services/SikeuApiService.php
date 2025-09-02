<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SikeuApiService
{
    private $baseUrl;
    private $timeout;

    public function __construct()
    {
        $this->baseUrl = 'https://api.daruttaqwa.or.id/sikeu/pagu/v1/uts1/c98803b227e70b47c3c26a736d61b44c/20252026';
        $this->timeout = 30;
    }

    /**
     * Test API connection with detailed info
     */
    public function testConnection()
    {
        try {
            Log::info('Testing SIKEU API connection', ['url' => $this->baseUrl]);

            $startTime = microtime(true);

            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'SKADA-Exam-System/1.0'
                ])
                ->get($this->baseUrl);

            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('API connection successful', [
                    'status' => $response->status(),
                    'response_time' => $responseTime,
                    'has_data' => isset($data['data']),
                    'data_count' => is_array($data['data'] ?? null) ? count($data['data']) : 0
                ]);

                return [
                    'success' => true,
                    'status_code' => $response->status(),
                    'response_time' => $responseTime,
                    'url' => $this->baseUrl,
                    'data_available' => isset($data['data']) && is_array($data['data']),
                    'record_count' => is_array($data['data'] ?? null) ? count($data['data']) : 0
                ];
            } else {
                Log::error('API connection failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);

                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}: {$response->body()}",
                    'status_code' => $response->status(),
                    'url' => $this->baseUrl
                ];
            }
        } catch (\Exception $e) {
            Log::error('API connection exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'url' => $this->baseUrl
            ];
        }
    }

    /**
     * Fetch siswa data with detailed logging
     */
    public function fetchSiswaData()
    {
        try {
            Log::info('SIKEU API: Starting comprehensive data fetch', [
                'url' => $this->baseUrl,
                'timestamp' => now(),
                'memory_usage' => memory_get_usage(true)
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'SKADA-Exam-System/1.0',
                    'Connection' => 'keep-alive'
                ])
                ->get($this->baseUrl);

            if ($response->successful()) {
                $rawData = $response->json();

                Log::info('SIKEU API: Raw response received', [
                    'status' => $response->status(),
                    'response_size' => strlen($response->body()),
                    'has_data_key' => isset($rawData['data']),
                    'data_type' => gettype($rawData['data'] ?? null)
                ]);

                // Validate response structure
                if (!isset($rawData['data']) || !is_array($rawData['data'])) {
                    Log::error('SIKEU API: Invalid response structure', [
                        'response_keys' => array_keys($rawData),
                        'data_content' => $rawData['data'] ?? null
                    ]);

                    throw new \Exception('Invalid API response structure: missing or invalid data array');
                }

                // Transform data
                $transformedData = $this->transformApiData($rawData['data']);

                Log::info('SIKEU API: Data transformation completed', [
                    'original_count' => count($rawData['data']),
                    'transformed_count' => count($transformedData),
                    'memory_after' => memory_get_usage(true)
                ]);

                return [
                    'success' => true,
                    'data' => $transformedData,
                    'total_records' => count($transformedData),
                    'source' => 'SIKEU API',
                    'raw_count' => count($rawData['data'])
                ];
            } else {
                $error = "HTTP {$response->status()}: {$response->body()}";

                Log::error('SIKEU API: HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                    'status_code' => $response->status(),
                    'raw_response' => $response->body()
                ];
            }
        } catch (\Exception $e) {
            Log::error('SIKEU API: Critical exception', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
    }

    /**
     * Transform API data to our database format with validation
     */
    private function transformApiData($apiData)
    {
        $transformed = [];
        $skipped = 0;

        foreach ($apiData as $index => $item) {
            // Enhanced validation
            if (!isset($item['idperson']) || empty($item['idperson'])) {
                Log::warning('SIKEU API: Skipping record with missing idperson', [
                    'index' => $index,
                    'item_keys' => array_keys($item),
                    'item' => $item
                ]);
                $skipped++;
                continue;
            }

            try {
                $transformedItem = [
                    'idyayasan' => (string) $item['idperson'],
                    'nama' => isset($item['nama']) ? (string) $item['nama'] : null,
                    'kelas' => isset($item['kelas']) ? (string) $item['kelas'] : null,
                    'status_pembayaran' => $this->transformPaymentStatus($item['lunas'] ?? 0),
                    'email' => $this->generateEmail($item['idperson']),
                    'password' => 'password',
                    'rekomendasi' => 'tidak',
                    'source' => 'sikeu_api',
                    'api_data' => json_encode($item)
                ];

                $transformed[] = $transformedItem;
            } catch (\Exception $e) {
                Log::error('SIKEU API: Error transforming item', [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'item' => $item
                ]);
                $skipped++;
            }
        }

        Log::info('SIKEU API: Transformation summary', [
            'original_count' => count($apiData),
            'transformed_count' => count($transformed),
            'skipped_count' => $skipped
        ]);

        return $transformed;
    }

    /**
     * Transform payment status from API format to our format
     */
    private function transformPaymentStatus($lunas)
    {
        // Handle various possible values
        if (is_bool($lunas)) {
            return $lunas ? 'Lunas' : 'Belum Lunas';
        }

        if (is_numeric($lunas)) {
            return ((int) $lunas === 1) ? 'Lunas' : 'Belum Lunas';
        }

        if (is_string($lunas)) {
            $normalized = strtolower(trim($lunas));
            return in_array($normalized, ['1', 'true', 'lunas', 'paid']) ? 'Lunas' : 'Belum Lunas';
        }

        // Default fallback
        return 'Belum Lunas';
    }

    /**
     * Generate email from idyayasan
     */
    private function generateEmail($idyayasan)
    {
        return (string) $idyayasan . '@smkdata.sch.id';
    }

    /**
     * Test fetch single student data for debugging
     */
    public function testFetchSingleStudent()
    {
        try {
            Log::info('Testing single student fetch from SIKEU API');

            $response = Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'SKADA-Exam-System/1.0'
                ])
                ->get($this->baseUrl);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
                    $sampleStudent = $data['data'][0];

                    return [
                        'success' => true,
                        'total_students' => count($data['data']),
                        'sample_student' => $sampleStudent,
                        'required_fields' => [
                            'idperson' => isset($sampleStudent['idperson']),
                            'nama' => isset($sampleStudent['nama']),
                            'kelas' => isset($sampleStudent['kelas']),
                            'lunas' => isset($sampleStudent['lunas'])
                        ],
                        'api_structure_valid' => true
                    ];
                } else {
                    return [
                        'success' => false,
                        'error' => 'No student data found in API response',
                        'response_structure' => array_keys($data)
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => "HTTP {$response->status()}: {$response->body()}",
                    'status_code' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e)
            ];
        }
    }
}
