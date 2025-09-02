<?php
// filepath: app\Http\Middleware\GuruRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class GuruRole
{
    /**
     * Handle an incoming request for Laravel 12 - Guru Guard.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if guru is authenticated with guru guard
        if (!Auth::guard('guru')->check()) {
            return redirect()->route('login.guru')->with('error', 'Please login as guru first');
        }

        $guru = Auth::guard('guru')->user();

        if (!$guru) {
            return redirect()->route('login.guru')->with('error', 'Guru session invalid');
        }

        // Check Spatie roles with guru guard
        foreach ($roles as $role) {
            if ($guru->hasRole($role, 'guru')) {
                return $next($request);
            }
        }

        // Access denied
        $currentRoles = $guru->roles->pluck('name')->implode(', ') ?: 'No roles assigned';

        abort(403, 'Access denied. Required guru roles: ' . implode(', ', $roles) .
            '. Your roles: ' . $currentRoles);
    }
}
