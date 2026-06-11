<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-meta')

    <title>Login Siswa - {{ config('app.name', 'SkadaExam') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
    <main class="relative min-h-screen overflow-hidden">
        <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.34),_transparent_32%),radial-gradient(circle_at_75%_20%,_rgba(16,185,129,0.22),_transparent_30%),linear-gradient(135deg,_#0f172a_0%,_#111827_48%,_#1e1b4b_100%)]">
        </div>
        <div class="absolute -left-20 top-24 h-72 w-72 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute -right-24 bottom-16 h-80 w-80 rounded-full bg-emerald-400/20 blur-3xl"></div>

        <div class="relative z-10 flex min-h-screen flex-col px-4 py-5 sm:px-6 lg:px-10">
            <header class="mx-auto flex w-full max-w-6xl items-center justify-between">
                <a href="/" class="inline-flex items-center gap-3 text-white">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white shadow-lg shadow-blue-950/20">
                        <img src="{{ asset('assets/logo-compressed.png') }}" alt="SkadaExam"
                            class="h-8 w-8 object-contain">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold tracking-tight">SkadaExam</span>
                        <span class="block text-xs font-medium text-blue-100">Portal Ujian Siswa</span>
                    </span>
                </a>

                <button type="button" id="installPwaButton"
                    class="hidden items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur hover:bg-white/15">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                    </svg>
                    Install App
                </button>
            </header>

            <section
                class="mx-auto grid w-full max-w-6xl flex-1 items-center gap-8 py-8 lg:grid-cols-[1.05fr_0.95fr] lg:py-10">
                <div class="hidden text-white lg:block">
                    <div class="max-w-xl">
                        <div
                            class="mb-5 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-blue-50 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Ujian aman, ringan, dan siap di perangkat siswa
                        </div>
                        <h1 class="text-5xl font-extrabold leading-tight tracking-tight">
                            Masuk ujian dengan token sesi.
                        </h1>
                        <p class="mt-5 max-w-lg text-lg leading-8 text-slate-200">
                            Gunakan ID Yayasan dan token dari pengawas. Pastikan perangkat tersambung internet dan buka
                            ujian dari aplikasi PWA jika diminta.
                        </p>

                        <div class="mt-8 grid max-w-lg grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <div
                                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-400/20 text-blue-100">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Zm10-10V7a4 4 0 0 0-8 0v4" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold">Token sesi</p>
                                <p class="mt-1 text-xs leading-5 text-slate-300">Validasi langsung ke ruangan ujian.</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <div
                                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-400/20 text-emerald-100">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold">Kehadiran</p>
                                <p class="mt-1 text-xs leading-5 text-slate-300">Otomatis tercatat saat login sukses.
                                </p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                                <div
                                    class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-violet-400/20 text-violet-100">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6l4 2m6-2A10 10 0 1 1 2 12a10 10 0 0 1 20 0Z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-bold">Waktu ujian</p>
                                <p class="mt-1 text-xs leading-5 text-slate-300">Dikunci sesuai jadwal sesi aktif.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-md">
                    <div
                        class="overflow-hidden rounded-[2rem] border border-white/20 bg-white shadow-2xl shadow-slate-950/30">
                        <div
                            class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 px-6 py-6 text-white sm:px-8">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-semibold text-blue-100">Selamat datang</p>
                                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight">Login Siswa</h2>
                                </div>
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-4 text-sm leading-6 text-blue-50">
                                Masukkan ID Yayasan dan token 6 karakter dari pengawas untuk membuka dashboard ujian.
                            </p>
                        </div>

                        <div class="px-6 py-6 sm:px-8">
                            @if (session('status'))
                                <div
                                    class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if (session('success'))
                                <div
                                    class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div
                                    class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                                    {{ session('error') }}
                                </div>
                            @endif

                            <div id="loginPwaPrompt"
                                class="mb-5 hidden rounded-2xl border border-indigo-100 bg-indigo-50 px-4 py-4 text-sm text-indigo-950">
                                <div class="flex gap-3">
                                    <span
                                        class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 18h.01M8 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-extrabold">Install aplikasi sebelum login</p>
                                        <p class="mt-1 leading-5 text-indigo-800">
                                            Agar tidak perlu login ulang saat masuk ujian, install SkadaExam dari
                                            halaman ini, buka dari ikon aplikasi, lalu masukkan ID Yayasan dan token.
                                        </p>
                                        <div class="mt-3 flex flex-wrap items-center gap-2">
                                            <button type="button" data-install-pwa
                                                class="inline-flex h-10 items-center justify-center rounded-xl bg-indigo-600 px-4 text-xs font-extrabold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                                                Install Aplikasi
                                            </button>
                                            <span id="loginPwaHint" class="text-xs font-semibold text-indigo-700">
                                                Jika tombol belum aktif, gunakan menu browser: Install App/Add to Home
                                                Screen.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('login.siswa.submit') }}" class="space-y-5">
                                @csrf

                                <div>
                                    <label for="idyayasan" class="mb-2 block text-sm font-bold text-slate-700">ID
                                        Yayasan</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                                            </svg>
                                        </span>
                                        <input id="idyayasan" name="idyayasan" type="text"
                                            value="{{ old('idyayasan') }}" required autofocus autocomplete="username"
                                            inputmode="text"
                                            class="block h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-center font-semibold text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                            placeholder="CONTOH: 123456">
                                    </div>
                                    @error('idyayasan')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="token" class="mb-2 block text-sm font-bold text-slate-700">Token
                                        Ujian</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 5.25a3 3 0 0 1 3 3m1.5 0a4.5 4.5 0 0 1-4.5 4.5H14l-2.25 2.25H9.75L7.5 17.25H5.25L3 19.5v-2.25l9.75-9.75a4.5 4.5 0 0 1 7.5-2.25Z" />
                                            </svg>
                                        </span>
                                        <input id="token" name="token" type="text" required maxlength="6"
                                            autocomplete="one-time-code" inputmode="text"
                                            class="block h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-center font-mono text-xl font-extrabold uppercase tracking-[0.35em] text-slate-900 outline-none transition placeholder:tracking-normal placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100"
                                            placeholder="CONTOH: ABC123">
                                    </div>
                                    @error('token')
                                        <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                    class="group flex h-14 w-full items-center justify-center gap-3 rounded-2xl bg-blue-600 px-5 text-base font-extrabold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 active:scale-[0.99]">
                                    Masuk ke Ujian
                                    <svg class="h-5 w-5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </button>
                            </form>

                            <div class="mt-5 rounded-2xl bg-slate-50 px-4 py-3 text-xs leading-5 text-slate-500">
                                <span class="font-bold text-slate-700">Catatan:</span>
                                token hanya berlaku untuk sesi ruangan yang sedang aktif. Jika token ditolak, hubungi
                                pengawas di ruangan.
                            </div>
                        </div>
                    </div>

                    <p class="mt-5 text-center text-xs font-medium leading-5 text-slate-300">
                        Gunakan Chrome/Edge terbaru. Untuk ujian PWA, buka dari ikon aplikasi setelah install.
                    </p>
                </div>
            </section>
        </div>
    </main>

    @php
        $loginNotice = session('notice') ?: request('notice');
    @endphp

    @if ($loginNotice === 'session_ended')
        <div id="loginNoticeModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
                <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6l4 2m6-2A10 10 0 1 1 2 12a10 10 0 0 1 20 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-lg font-extrabold">Sesi Ujian Sudah Habis</h2>
                            <p class="text-xs font-semibold text-amber-50">Silakan konfirmasi ke pengawas.</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm leading-6 text-slate-600">
                        Sesi ujian Anda telah berakhir. Jika masih ada sesi berikutnya, mintalah token baru kepada
                        pengawas ruangan.
                    </p>
                    <button type="button" data-login-notice-close
                        class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        const tokenInput = document.getElementById('token');
        tokenInput?.addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/\s+/g, '').slice(0, 6);
        });

        const installPwaButton = document.getElementById('installPwaButton');
        const loginPwaPrompt = document.getElementById('loginPwaPrompt');
        const loginPwaHint = document.getElementById('loginPwaHint');
        const installPwaButtons = document.querySelectorAll('[data-install-pwa]');

        function refreshInstallButton() {
            if (!window.SkadaExamPwa) {
                return;
            }

            const isStandalone = window.SkadaExamPwa.isStandalone();
            const canPromptInstall = window.SkadaExamPwa.canPromptInstall();

            installPwaButton?.classList.toggle('hidden', isStandalone || !canPromptInstall);
            installPwaButton?.classList.toggle('inline-flex', !isStandalone && canPromptInstall);

            loginPwaPrompt?.classList.toggle('hidden', isStandalone);
            installPwaButtons.forEach((button) => {
                button.disabled = !canPromptInstall;
                button.textContent = canPromptInstall ? 'Install Aplikasi' : 'Install dari Menu Browser';
            });

            if (loginPwaHint) {
                loginPwaHint.textContent = canPromptInstall ?
                    'Setelah terpasang, buka SkadaExam dari ikon aplikasi lalu login.' :
                    'Jika tombol belum aktif, gunakan menu browser: Install App/Add to Home Screen.';
            }
        }

        window.addEventListener('skadaexam:pwa-install-available', refreshInstallButton);
        document.addEventListener('DOMContentLoaded', refreshInstallButton);

        async function installPwaFromLogin() {
            if (!window.SkadaExamPwa?.canPromptInstall()) {
                return;
            }

            const choice = await window.SkadaExamPwa.promptInstall();
            if (choice.outcome === 'accepted' && loginPwaHint) {
                loginPwaHint.textContent = 'Aplikasi terpasang. Buka SkadaExam dari ikon aplikasi, lalu login.';
            }

            refreshInstallButton();
        }

        installPwaButton?.addEventListener('click', installPwaFromLogin);
        installPwaButtons.forEach((button) => {
            button.addEventListener('click', installPwaFromLogin);
        });

        @if ($loginNotice === 'session_ended')
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('loginNoticeModal');

                function closeLoginNotice() {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
                document.body.style.overflow = 'hidden';
                modal?.querySelectorAll('[data-login-notice-close]').forEach((button) => {
                    button.addEventListener('click', closeLoginNotice);
                });
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeLoginNotice();
                    }
                });

                const url = new URL(window.location.href);
                if (url.searchParams.has('notice')) {
                    url.searchParams.delete('notice');
                    window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
                }
            });
        @endif
    </script>
</body>

</html>
