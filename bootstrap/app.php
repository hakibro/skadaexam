<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register route middleware
        $middleware->alias([
            // 'role' => \Spatie\Permission\Middleware\RoleMiddleware::class, // ✅ balik ke Spatie
            // 'hasrole' => \App\Http\Middleware\HasRole::class,
            'role' => \App\Http\Middleware\HasRole::class,
            'siswa.role' => \App\Http\Middleware\SiswaRole::class,
            'siswa.force_logout' => \App\Http\Middleware\ForceLogoutSiswa::class,
            'ujian.active' => \App\Http\Middleware\UjianActive::class,
            'api.bearer' => \App\Http\Middleware\ApiBearerToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            $status = match (true) {
                $e instanceof TokenMismatchException => 419,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            $messages = [
                400 => 'Permintaan tidak valid.',
                401 => 'Anda perlu login untuk melanjutkan.',
                403 => 'Anda tidak memiliki izin untuk mengakses halaman ini.',
                404 => 'Alamat yang diminta tidak ditemukan.',
                405 => 'Metode request tidak diizinkan.',
                419 => 'Sesi telah kedaluwarsa. Muat ulang halaman lalu coba lagi.',
                429 => 'Terlalu banyak percobaan. Silakan tunggu sebentar.',
                500 => 'Terjadi gangguan pada server.',
                503 => 'Layanan sedang tidak tersedia.',
            ];

            return response()->json([
                'message' => $messages[$status] ?? 'Terjadi kesalahan.',
                'status' => $status,
            ], $status);
        });
    })->create();
