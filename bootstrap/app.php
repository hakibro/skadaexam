<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
