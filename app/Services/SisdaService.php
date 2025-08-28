<?php
// filepath: app\Services\SisdaService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SisdaService
{
    private $baseUrl;
    private $timeout;
    private $retryTimes;
    private $retryDelay;
    private $userAgent;

    public function __construct()
    {
        $this->baseUrl = config('services.sisda.base_url', env('SISDA_API_BASE_URL', 'https://api.daruttaqwa.or.id/sisda/v1'));
        $this->timeout = config('services.sisda.timeout', env('SISDA_API_TIMEOUT', 15));
        $this->retryTimes = config('services.sisda.retry_times', env('SISDA_API_RETRY_TIMES', 2));
        $this->retryDelay = config('services.sisda.retry_delay', env('SISDA_API_RETRY_DELAY', 1000));
        $this->userAgent = config('services.sisda.user_agent', env('SISDA_USER_AGENT', 'SKADA-Exam-System/1.0'));
    }

    /**
     * Get student payment status from SISDA API
     */
    public function getStudentPayment($idyayasan)
    {
        try {
            $startTime = microtime(true);

            Log::info('SISDA API payment request started', [
                'idyayasan' => $idyayasan,
                'endpoint' => $this->baseUrl . '/payment/' . $idyayasan
            ]);

            // Check cache first (cache for 5 minutes to prevent API abuse)
            $cacheKey = "sisda_payment_{$idyayasan}";
            if (Cache::has($cacheKey)) {
                Log::info('Payment data served from cache', ['idyayasan' => $idyayasan]);
                return Cache::get($cacheKey);
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->retry($this->retryTimes, $this->retryDelay)
                ->get($this->baseUrl . '/payment/' . $idyayasan);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('SISDA API payment response received', [
                    'idyayasan' => $idyayasan,
                    'response_code' => $data['responseCode'] ?? 'unknown',
                    'response_message' => $data['responseMessage'] ?? 'unknown',
                    'duration_ms' => $duration,
                    'has_payment_summary' => isset($data['data']['payment_summary'])
                ]);

                // Validate response structure
                if (isset($data['responseCode']) && $data['responseCode'] == 200) {
                    if (isset($data['data']) && isset($data['data']['payment_summary'])) {
                        $result = [
                            'success' => true,
                            'data' => $data['data'],
                            'payment_status' => $data['data']['payment_summary']['status'] ?? 'Unknown',
                            'payment_summary' => $data['data']['payment_summary'],
                            'duration' => $duration,
                            'source' => 'api'
                        ];

                        // Cache successful response for 5 minutes
                        Cache::put($cacheKey, $result, 300);

                        return $result;
                    } else {
                        Log::warning('SISDA API payment data structure invalid', [
                            'idyayasan' => $idyayasan,
                            'response' => $data
                        ]);

                        return [
                            'success' => false,
                            'message' => 'Invalid payment data structure from API',
                            'error_code' => 'INVALID_STRUCTURE',
                            'duration' => $duration
                        ];
                    }
                } else {
                    Log::warning('SISDA API payment request failed', [
                        'idyayasan' => $idyayasan,
                        'response_code' => $data['responseCode'] ?? 'unknown',
                        'response_message' => $data['responseMessage'] ?? 'unknown'
                    ]);

                    return [
                        'success' => false,
                        'message' => $data['responseMessage'] ?? 'API request failed',
                        'error_code' => $data['responseCode'] ?? 'UNKNOWN_ERROR',
                        'duration' => $duration
                    ];
                }
            } else {
                $statusCode = $response->status();
                $errorMessage = "HTTP {$statusCode}";

                if ($response->json()) {
                    $errorData = $response->json();
                    $errorMessage = $errorData['responseMessage'] ?? $errorMessage;
                }

                Log::error('SISDA API payment HTTP error', [
                    'idyayasan' => $idyayasan,
                    'status_code' => $statusCode,
                    'error_message' => $errorMessage,
                    'response_body' => $response->body(),
                    'duration' => $duration
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'error_code' => "HTTP_{$statusCode}",
                    'duration' => $duration
                ];
            }
        } catch (\Exception $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('SISDA API payment exception', [
                'idyayasan' => $idyayasan,
                'error' => $e->getMessage(),
                'duration' => $duration,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'API connection error: ' . $e->getMessage(),
                'error_code' => 'CONNECTION_ERROR',
                'duration' => $duration
            ];
        }
    }

    /**
     * Test API connection
     */
    public function testConnection()
    {
        try {
            $startTime = microtime(true);

            // Use a known test ID or just test the base endpoint
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => $this->userAgent,
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/payment/test');

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'duration' => $duration,
                'base_url' => $this->baseUrl
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'duration' => 0,
                'base_url' => $this->baseUrl
            ];
        }
    }

    /**
     * Get cached payment data if available
     */
    public function getCachedPayment($idyayasan)
    {
        $cacheKey = "sisda_payment_{$idyayasan}";
        return Cache::get($cacheKey);
    }

    /**
     * Clear payment cache for specific student
     */
    public function clearPaymentCache($idyayasan)
    {
        $cacheKey = "sisda_payment_{$idyayasan}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all payment cache
     */
    public function clearAllPaymentCache()
    {
        // This is a simple approach, in production you might want to use cache tags
        $keys = Cache::get('sisda_payment_keys', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        Cache::forget('sisda_payment_keys');
    }
}
