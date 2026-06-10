@php
    $status = $status ?? 500;
    $title = $title ?? 'Terjadi Kesalahan';
    $message = $message ?? 'Sistem tidak dapat memproses permintaan saat ini.';
    $hint = $hint ?? 'Silakan coba lagi beberapa saat lagi atau kembali ke halaman utama.';
    $tone = $tone ?? 'red';

    $dashboardRoute = null;
    $loginRoute = null;

    if (auth('siswa')->check()) {
        $dashboardRoute = \Illuminate\Support\Facades\Route::has('siswa.dashboard') ? route('siswa.dashboard') : url('/');
        $loginRoute = \Illuminate\Support\Facades\Route::has('login.siswa') ? route('login.siswa') : url('/');
    } elseif (auth('web')->check()) {
        $user = auth('web')->user();
        $routeName = method_exists($user, 'getRedirectRoute') ? $user->getRedirectRoute() : 'dashboard';
        $dashboardRoute = \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : route('dashboard');
        $loginRoute = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/');
    } else {
        $loginRoute = \Illuminate\Support\Facades\Route::has('login') ? route('login') : url('/');
    }

    $toneClasses = [
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'blue' => 'bg-blue-50 text-blue-700 border-blue-200',
        'slate' => 'bg-slate-50 text-slate-700 border-slate-200',
        'red' => 'bg-red-50 text-red-700 border-red-200',
    ][$tone] ?? 'bg-red-50 text-red-700 border-red-200';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-meta')

    <title>{{ $status }} - {{ $title }} | {{ config('app.name', 'SKADA Exam System') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <section class="w-full max-w-2xl bg-white border border-gray-200 shadow-sm rounded-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border {{ $toneClasses }}">
                        <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-500">Kode {{ $status }}</p>
                        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $title }}</h1>
                        <p class="mt-3 text-base leading-7 text-gray-700">{{ $message }}</p>
                    </div>
                </div>

                <div class="mt-6 rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm leading-6 text-gray-700">
                    {{ $hint }}
                </div>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    @if ($dashboardRoute)
                        <a href="{{ $dashboardRoute }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fa-solid fa-house"></i>
                            Kembali ke Dashboard
                        </a>
                    @else
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fa-solid fa-house"></i>
                            Ke Halaman Awal
                        </a>
                    @endif

                    @if ($status === 419)
                        <button type="button" onclick="window.location.reload()"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fa-solid fa-rotate-right"></i>
                            Muat Ulang
                        </button>
                    @else
                        <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ url('/') }}')"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fa-solid fa-arrow-left"></i>
                            Kembali
                        </button>
                    @endif

                    @if (! $dashboardRoute && $loginRoute)
                        <a href="{{ $loginRoute }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Login
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </main>
</body>

</html>
