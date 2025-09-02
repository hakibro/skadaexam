<?php
// filepath: app\Http\Middleware\SiswaRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SiswaRole
{
    /**
     * Handle an incoming request for Laravel 12 - Siswa Guard.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if siswa is authenticated with siswa guard
        if (!Auth::guard('siswa')->check()) {
            return redirect()->route('login.siswa')->with('error', 'Please login as siswa first');
        }

        $siswa = Auth::guard('siswa')->user();

        if (!$siswa) {
            return redirect()->route('login.siswa')->with('error', 'Siswa session invalid');
        }

        // Check Spatie roles with siswa guard
        foreach ($roles as $role) {
            if ($siswa->hasRole($role, 'siswa')) {
                return $next($request);
            }
        }

        // Access denied
        $currentRoles = $siswa->roles->pluck('name')->implode(', ') ?: 'No roles assigned';

        abort(403, 'Access denied. Required siswa roles: ' . implode(', ', $roles) .
            '. Your roles: ' . $currentRoles);
    }
}
