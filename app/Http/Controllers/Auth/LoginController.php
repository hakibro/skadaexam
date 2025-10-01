<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\SesiRuangan;

class LoginController extends Controller
{
    // COMMENT middleware untuk testing
    public function __construct()
    {
        $this->middleware('guest:siswa')->except('logout');
    }

    public function showLoginForm()
    {
        // Ganti dari testing ke view yang sesungguhnya
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();


            // Simple redirect ke admin dashboard
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function directTokenLogin($token)
    {
        $sesi = SesiRuangan::where('token_ujian', $token)->first();

        if (! $sesi) {
            abort(404, 'Token tidak valid.');
        }

        // misalnya redirect ke halaman login ujian
        return redirect()->route('ujian.login', ['sesiRuangan' => $sesi->id]);
    }


    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
