<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek di semua guard yang aktif (web & guru)
        $user = Auth::user() ?? Auth::guard('guru')->user();

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login first');
        }

        // Jika user adalah admin → akses full
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Jika cocok salah satu role yang diminta → lanjut
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Log untuk debugging
        Log::warning('Role access denied', [
            'user_id' => $user->id,
            'required_roles' => implode(',', $roles),
            'user_roles' => $user->roles->pluck('name')->implode(','),
            'url' => $request->fullUrl(),
        ]);

        // Response untuk akses ditolak
        if ($request->ajax() || $request->wantsJson()) {
            abort(403, 'Access denied. Required roles: ' . implode(', ', $roles) .
                '. Your roles: ' . $user->roles->pluck('name')->implode(', '));
        }

        // Redirect ke dashboard sesuai role pertama user
        $userRole = $user->roles->pluck('name')->first() ?? 'guest';
        return redirect()->route($userRole . '.dashboard')
            ->with('warning', 'Anda tidak memiliki akses ke fitur yang diminta. Dibutuhkan role: ' . implode(', ', $roles));
    }
}
