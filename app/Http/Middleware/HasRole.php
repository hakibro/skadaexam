<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Cek user dari guard siswa atau web
        $user = Auth::guard('siswa')->user() ?? Auth::guard('web')->user();

        if (!$user) {
            if (!Auth::guard('siswa')->check()) {
                return redirect()->route('login.siswa');
            }
            if (!Auth::guard('web')->check()) {
                return redirect()->route('login');
            }
            return redirect('/');
        }

        // Ambil role pertama user
        $userRole = $user->roles->pluck('name')->first();

        // ✅ Admin bypass: akses semua route
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        Log::info('Middleware check', [
            'user_id' => $user->id,
            'user_role' => $userRole,
            'all_roles' => $user->roles->pluck('name')->toArray(),
            'route' => $request->route()->getName(),
        ]);

        // // ✅ Koordinator boleh akses semua route pengawas
        // if ($userRole === 'koordinator' && $request->routeIs('pengawas.*')) {
        //     return $next($request);
        // }

        // Cek apakah user punya salah satu role yang diminta
        if (!empty($roles)) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return $next($request);
                }
            }
        }

        // Log untuk debugging
        Log::warning('Role access denied', [
            'user_id'        => $user->id,
            'required_roles' => implode(',', $roles),
            'user_roles'     => $user->roles->pluck('name')->implode(', '),
            'url'            => $request->fullUrl(),
        ]);

        // Jika request AJAX/JSON → abort 403
        if ($request->ajax() || $request->wantsJson()) {
            abort(403, 'Access denied. Dibutuhkan role: ' . implode(', ', $roles) .
                '. Role Anda: ' . $user->roles->pluck('name')->implode(', '));
        }

        // Jika tidak boleh akses → redirect ke dashboard sesuai role pertama user
        return redirect()->route($userRole . '.dashboard')
            ->with('warning', 'Anda tidak memiliki akses ke fitur ini. Dibutuhkan role: ' . implode(', ', $roles));
    }
}
