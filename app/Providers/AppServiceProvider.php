<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use App\Services\EnrollmentService;
use App\Services\UjianService;
use App\Services\SoalImageService;
use App\Models\SesiRuangan;
use App\Observers\SesiRuanganObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services
        $this->app->singleton(SoalImageService::class, function ($app) {
            return new SoalImageService();
        });

        $this->app->singleton(EnrollmentService::class, function ($app) {
            return new EnrollmentService();
        });

        $this->app->singleton(UjianService::class, function ($app) {
            return new UjianService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);



        $dirs = [
            'soal/pertanyaan',
            'soal/pilihan',
            'soal/pembahasan',
            'bank-soal/sources'
        ];



        foreach ($dirs as $dir) {
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
        }

        // Load the ujian.php routes file
        Route::middleware('web')
            ->group(base_path('routes/ujian.php'));

        // === Tambahkan observer disini ===
        SesiRuangan::observe(SesiRuanganObserver::class);
    }
}
