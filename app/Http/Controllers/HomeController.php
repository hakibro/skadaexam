<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Jika user sudah login, redirect ke dashboard mereka
        if (auth()->check()) {
            $user = auth()->user();

            // Pastikan tidak ada redirect loop
            if ($user && $user->role) {
                switch ($user->role) {
                    case 'admin':
                        return redirect()->route('admin.dashboard');
                    case 'data':
                        return redirect()->route('data.dashboard');
                    case 'naskah':
                        return redirect()->route('naskah.dashboard');
                    case 'pengawas':
                        return redirect()->route('pengawas.dashboard');
                    case 'koordinator':
                        return redirect()->route('koordinator.dashboard');
                    case 'guru':
                        return redirect()->route('guru.dashboard');
                    case 'siswa':
                        return redirect()->route('siswa.dashboard');
                    default:
                        // Logout jika role tidak valid
                        auth()->logout();
                        break;
                }
            }
        }

        // Tampilkan halaman home untuk user yang belum login
        return view('homesplit');
    }
}
