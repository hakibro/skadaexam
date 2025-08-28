<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Jika user sudah login, redirect ke dashboard
        if (Auth::check()) {
            $user = Auth::user();

            // Cek apakah user model punya method hasRole (Spatie Permission)
            if (method_exists($user, 'hasRole')) {
                // Jika ada Spatie Permission system
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                if ($user->hasRole('guru')) {
                    return redirect()->route('guru.dashboard');
                }
                if ($user->hasRole('siswa')) {
                    return redirect()->route('siswa.dashboard');
                }

                // Jika punya role khusus
                if (
                    method_exists($user, 'hasAnyRole') &&
                    $user->hasAnyRole(['data', 'naskah', 'pengawas', 'koordinator', 'ruangan'])
                ) {

                    $roles = $user->roles->pluck('name')->toArray();

                    if (in_array('data', $roles)) {
                        return redirect()->route('data.dashboard');
                    }
                    if (in_array('naskah', $roles)) {
                        return redirect()->route('naskah.dashboard');
                    }
                    if (in_array('pengawas', $roles)) {
                        return redirect()->route('pengawas.dashboard');
                    }
                    if (in_array('koordinator', $roles)) {
                        return redirect()->route('koordinator.dashboard');
                    }
                    if (in_array('ruangan', $roles)) {
                        return redirect()->route('ruangan.dashboard');
                    }
                }
            } else {
                // Jika tidak ada role system, cek berdasarkan email atau field lain
                if (
                    $user->email === 'admin@test.com' ||
                    str_contains($user->email, 'admin') ||
                    isset($user->role) && $user->role === 'admin'
                ) {
                    return redirect()->route('admin.dashboard');
                }

                // Cek jika ada field role di database
                if (isset($user->role)) {
                    switch ($user->role) {
                        case 'guru':
                            return redirect()->route('guru.dashboard');
                        case 'siswa':
                            return redirect()->route('siswa.dashboard');
                        case 'data':
                            return redirect()->route('data.dashboard');
                        case 'naskah':
                            return redirect()->route('naskah.dashboard');
                        case 'pengawas':
                            return redirect()->route('pengawas.dashboard');
                        case 'koordinator':
                            return redirect()->route('koordinator.dashboard');
                        case 'ruangan':
                            return redirect()->route('ruangan.dashboard');
                    }
                }
            }

            // Default redirect - semua user yang login ke admin dashboard
            return redirect()->route('admin.dashboard');
        }

        // Jika belum login, tampilkan homepage
        return view('homesplit');
    }
}
