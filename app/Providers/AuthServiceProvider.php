<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot()
    {
        $this->registerPolicies();
        // Gate: akses dashboard pengawas
        // Gate::define('accessPengawas', function ($user) {
        //     return $user->canSupervise() || $user->canCoordinate();
        // });
    }
}
