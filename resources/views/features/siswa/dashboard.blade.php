<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-meta')

    <title>Dashboard Siswa - {{ config('app.name', 'SkadaExam') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 font-sans text-slate-900 antialiased">
    <main class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.32),_transparent_30%),radial-gradient(circle_at_80%_10%,_rgba(16,185,129,0.18),_transparent_28%),linear-gradient(135deg,_#0f172a_0%,_#111827_48%,_#1e1b4b_100%)]"></div>
        <div class="absolute -left-24 top-24 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"></div>
        <div class="absolute -right-28 bottom-16 h-96 w-96 rounded-full bg-emerald-400/15 blur-3xl"></div>

        <div class="relative z-10 min-h-screen px-4 py-5 sm:px-6 lg:px-10">
            <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-3">
                <a href="/" class="inline-flex min-w-0 items-center gap-3 text-white">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg shadow-blue-950/20">
                        <img src="{{ asset('assets/logo-compressed.png') }}" alt="SkadaExam" class="h-8 w-8 object-contain">
                    </span>
                    <span class="min-w-0 leading-tight">
                        <span class="block text-base font-extrabold tracking-tight">SkadaExam</span>
                        <span class="block truncate text-xs font-medium text-blue-100">Dashboard Ujian Siswa</span>
                    </span>
                </a>

                <div class="flex items-center gap-2 sm:gap-3">
                    <button type="button" id="installPwaButton"
                        class="hidden items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-2 text-xs font-bold text-white backdrop-blur hover:bg-white/15 sm:px-4 sm:text-sm">
                        <i class="fa-solid fa-download"></i>
                        <span class="hidden sm:inline">Install App</span>
                    </button>

                    <div class="hidden max-w-[220px] items-center gap-3 rounded-full border border-white/15 bg-white/10 py-1.5 pl-2 pr-4 text-white backdrop-blur sm:flex">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-500 text-sm font-extrabold">
                            {{ strtoupper(substr($siswa->nama ?? 'S', 0, 1)) }}
                        </span>
                        <span class="truncate text-sm font-bold">{{ $siswa->nama }}</span>
                    </div>

                    <form method="POST" action="{{ route('siswa.logout') }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-full bg-red-500 px-3 text-sm font-bold text-white shadow-lg shadow-red-950/20 transition hover:bg-red-600 sm:px-4"
                            onclick="return confirm('Yakin ingin logout dari sistem ujian?')">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <section class="mx-auto mt-7 w-full max-w-7xl">
                <div class="grid gap-5 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
                    <div class="text-white">
                        <p class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold text-blue-50 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                            Sesi aktif siap digunakan
                        </p>
                        <h1 class="text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">
                            Selamat datang, {{ $siswa->nama }}.
                        </h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200 sm:text-base">
                            Pilih ujian yang tersedia hari ini. Pastikan perangkat stabil dan gunakan aplikasi PWA saat mulai ujian.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @php
                            $statCards = [
                                ['label' => 'Total', 'value' => $stats['total'] ?? 0, 'icon' => 'fa-clipboard-list', 'tone' => 'blue'],
                                ['label' => 'Selesai', 'value' => $stats['selesai'] ?? 0, 'icon' => 'fa-circle-check', 'tone' => 'emerald'],
                                ['label' => 'Hari Ini', 'value' => $stats['berlangsung'] ?? 0, 'icon' => 'fa-clock', 'tone' => 'amber'],
                                ['label' => 'Mendatang', 'value' => $stats['mendatang'] ?? 0, 'icon' => 'fa-calendar-days', 'tone' => 'violet'],
                            ];
                        @endphp

                        @foreach ($statCards as $stat)
                            <div class="rounded-2xl border border-white/15 bg-white/10 p-3 text-white shadow-lg shadow-slate-950/10 backdrop-blur">
                                <div class="flex items-center justify-between gap-2">
                                    <span @class([
                                        'flex h-9 w-9 items-center justify-center rounded-xl text-sm',
                                        'bg-blue-400/20 text-blue-100' => $stat['tone'] === 'blue',
                                        'bg-emerald-400/20 text-emerald-100' => $stat['tone'] === 'emerald',
                                        'bg-amber-400/20 text-amber-100' => $stat['tone'] === 'amber',
                                        'bg-violet-400/20 text-violet-100' => $stat['tone'] === 'violet',
                                    ])>
                                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                                    </span>
                                    <span class="text-2xl font-extrabold">{{ $stat['value'] }}</span>
                                </div>
                                <p class="mt-2 text-xs font-semibold text-slate-200">{{ $stat['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if (session('success') || session('error'))
                    <div @class([
                        'mt-5 rounded-2xl border px-4 py-3 text-sm font-semibold shadow-lg',
                        'border-emerald-200 bg-emerald-50 text-emerald-800' => session('success'),
                        'border-red-200 bg-red-50 text-red-700' => session('error'),
                    ])>
                        <i class="fa-solid {{ session('success') ? 'fa-circle-check' : 'fa-circle-xmark' }} mr-2"></i>
                        {{ session('success') ?? session('error') }}
                    </div>
                @endif

                @if ($announcementHtml)
                    <button type="button" id="announcementCard"
                        class="mt-5 flex w-full items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-amber-900 shadow-lg shadow-amber-950/10 transition hover:bg-amber-100">
                        <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                            <i class="fa-solid fa-bullhorn"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-extrabold">Pengumuman koordinator</span>
                            <span class="mt-0.5 block text-xs font-medium text-amber-800">Klik untuk membaca informasi terbaru.</span>
                        </span>
                        <span class="hidden rounded-full bg-white px-3 py-1 text-xs font-bold text-amber-700 sm:inline-flex">Info</span>
                    </button>
                @endif

                <div class="mt-6 grid gap-5 lg:grid-cols-[1fr_340px]">
                    <section class="overflow-hidden rounded-[1.75rem] border border-white/20 bg-white shadow-2xl shadow-slate-950/20">
                        <div class="flex flex-col gap-2 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div>
                                <h2 class="text-lg font-extrabold text-slate-900">Ujian Hari Ini</h2>
                                <p class="text-sm text-slate-500">Daftar ujian sesuai sesi login siswa.</p>
                            </div>
                            <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ $activeMapels->count() }} ujian
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100">
                            @forelse ($activeMapels as $mapel)
                                @php
                                    $startTime = $mapel['waktu_mulai'] && $mapel['waktu_mulai'] !== 'N/A'
                                        ? \Carbon\Carbon::parse($mapel['waktu_mulai'])->format('H:i')
                                        : '-';
                                    $endTime = $mapel['waktu_selesai'] && $mapel['waktu_selesai'] !== 'N/A'
                                        ? \Carbon\Carbon::parse($mapel['waktu_selesai'])->format('H:i')
                                        : '-';
                                    $isCompleted = (bool) ($mapel['is_completed'] ?? false);
                                    $isLocked = !$isCompleted && !($mapel['can_access'] ?? false);
                                @endphp

                                <article class="p-4 transition hover:bg-slate-50 sm:p-5">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="truncate text-base font-extrabold text-slate-900 sm:text-lg">
                                                    {{ $mapel['mapel_name'] }}
                                                </h3>
                                                @if ($isCompleted)
                                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Selesai</span>
                                                @elseif ($isLocked)
                                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">Terkunci</span>
                                                @else
                                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">Tersedia</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                {{ $mapel['mapel_kode'] }} | {{ $mapel['sesi_ruangan_name'] }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-3 gap-2 text-center sm:min-w-[330px]">
                                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Waktu</p>
                                                <p class="mt-1 text-sm font-extrabold text-slate-800">{{ $startTime }}-{{ $endTime }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Durasi</p>
                                                <p class="mt-1 text-sm font-extrabold text-slate-800">{{ $mapel['durasi_menit'] }} mnt</p>
                                            </div>
                                            <div class="rounded-2xl bg-slate-50 px-3 py-2">
                                                <p class="text-[10px] font-bold uppercase text-slate-400">Ruangan</p>
                                                <p class="mt-1 truncate text-sm font-extrabold text-slate-800">{{ $mapel['ruangan'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        @if ($isCompleted)
                                            <button type="button" disabled
                                                class="inline-flex h-11 min-w-[130px] items-center justify-center gap-2 rounded-2xl bg-emerald-100 px-5 text-sm font-extrabold text-emerald-700">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Selesai
                                            </button>
                                        @elseif ($mapel['can_access'])
                                            <a href="{{ route('ujian.exam', ['jadwal_id' => $mapel['jadwal_id']]) }}"
                                                data-require-pwa="1"
                                                class="inline-flex h-11 min-w-[130px] items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 text-sm font-extrabold text-white shadow-lg shadow-blue-600/25 transition hover:bg-blue-700 active:scale-[0.99]">
                                                Mulai Ujian
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        @else
                                            <button type="button" disabled
                                                class="inline-flex h-11 min-w-[130px] items-center justify-center gap-2 rounded-2xl bg-slate-100 px-5 text-sm font-extrabold text-slate-500">
                                                <i class="fa-solid fa-lock"></i>
                                                Terkunci
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="px-5 py-14 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="fa-solid fa-calendar-xmark text-xl"></i>
                                    </div>
                                    <h3 class="mt-4 text-base font-extrabold text-slate-900">Belum ada ujian hari ini</h3>
                                    <p class="mt-1 text-sm text-slate-500">Jika seharusnya ada ujian, hubungi pengawas ruangan.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <aside class="rounded-[1.75rem] border border-white/20 bg-white p-5 shadow-2xl shadow-slate-950/20">
                        <h2 class="text-lg font-extrabold text-slate-900">Petunjuk Singkat</h2>
                        <div class="mt-4 space-y-3">
                            <div class="flex gap-3 rounded-2xl bg-blue-50 p-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white">
                                    <i class="fa-solid fa-stopwatch"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-blue-900">Submit ujian</p>
                                    <p class="mt-0.5 text-xs leading-5 text-blue-700">Tombol submit muncul saat sisa waktu di bawah 5 menit, atau saat pengawas mengaktifkannya.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 rounded-2xl bg-emerald-50 p-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white">
                                    <i class="fa-solid fa-wifi"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-emerald-900">Koneksi stabil</p>
                                    <p class="mt-0.5 text-xs leading-5 text-emerald-700">Pastikan jaringan aktif selama mengerjakan ujian.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 rounded-2xl bg-violet-50 p-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white">
                                    <i class="fa-solid fa-mobile-screen"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-violet-900">Gunakan PWA</p>
                                    <p class="mt-0.5 text-xs leading-5 text-violet-700">Saat mulai ujian, sistem akan meminta mode aplikasi jika belum dibuka dari PWA.</p>
                                </div>
                            </div>
                            <div class="flex gap-3 rounded-2xl bg-red-50 p-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-extrabold text-red-900">Jangan berpindah tab</p>
                                    <p class="mt-0.5 text-xs leading-5 text-red-700">Pelanggaran akan tercatat sesuai pengaturan ujian.</p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>
        </div>
    </main>

    @php
        $examNotice = session('notice') ?: request('notice');
    @endphp

    @if ($examNotice === 'duration_expired')
        <div id="examNoticeModal"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div class="w-full max-w-md overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-extrabold">Durasi Ujian Selesai</h2>
                            <p class="text-xs font-semibold text-blue-50">Jawaban telah dikumpulkan otomatis.</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <p class="text-sm leading-6 text-slate-600">
                        Durasi ujian sudah habis. Sistem telah menyimpan dan mengumpulkan jawaban Anda secara otomatis.
                    </p>
                    <button type="button" data-exam-notice-close
                        class="mt-5 inline-flex h-11 w-full items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($announcementHtml)
        <div id="announcementModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm">
            <div class="max-h-[85vh] w-full max-w-2xl overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-4 text-white sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                            <i class="fa-solid fa-bullhorn"></i>
                        </span>
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-extrabold">Pengumuman Koordinator</h2>
                            <p class="text-xs font-semibold text-amber-50">Informasi terbaru untuk sesi ujian.</p>
                        </div>
                    </div>
                    <button type="button" data-announcement-close
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/25">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto px-5 py-5 sm:px-6">
                    <div class="prose prose-sm max-w-none prose-headings:text-slate-900 prose-p:text-slate-700 prose-a:text-blue-600">
                        {!! $announcementHtml !!}
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-5 py-4 sm:px-6">
                    <button type="button" data-announcement-close
                        class="inline-flex h-11 items-center justify-center rounded-2xl bg-slate-900 px-5 text-sm font-extrabold text-white transition hover:bg-slate-800">
                        Saya Mengerti
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        const installPwaButton = document.getElementById('installPwaButton');

        function refreshInstallButton() {
            if (!installPwaButton || !window.SkadaExamPwa || window.SkadaExamPwa.isStandalone()) {
                return;
            }

            installPwaButton.classList.toggle('hidden', !window.SkadaExamPwa.canPromptInstall());
            installPwaButton.classList.toggle('inline-flex', window.SkadaExamPwa.canPromptInstall());
        }

        window.addEventListener('skadaexam:pwa-install-available', refreshInstallButton);
        document.addEventListener('DOMContentLoaded', refreshInstallButton);

        installPwaButton?.addEventListener('click', async () => {
            if (!window.SkadaExamPwa?.canPromptInstall()) {
                return;
            }

            await window.SkadaExamPwa.promptInstall();
            refreshInstallButton();
        });

        @if ($examNotice === 'duration_expired')
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('examNoticeModal');

                function closeExamNotice() {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                modal?.classList.remove('hidden');
                modal?.classList.add('flex');
                document.body.style.overflow = 'hidden';
                modal?.querySelectorAll('[data-exam-notice-close]').forEach((button) => {
                    button.addEventListener('click', closeExamNotice);
                });
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeExamNotice();
                    }
                });

                const url = new URL(window.location.href);
                if (url.searchParams.has('notice')) {
                    url.searchParams.delete('notice');
                    window.history.replaceState({}, document.title, url.pathname + url.search + url.hash);
                }
            });
        @endif

        @if ($announcementHtml)
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('announcementModal');
                const card = document.getElementById('announcementCard');
                const storageKey = @json('skadaexam:announcement:' . $siswa->id . ':' . $announcementKey);

                function openAnnouncement() {
                    modal?.classList.remove('hidden');
                    modal?.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }

                function closeAnnouncement() {
                    modal?.classList.add('hidden');
                    modal?.classList.remove('flex');
                    document.body.style.overflow = '';
                    try {
                        localStorage.setItem(storageKey, 'seen');
                    } catch (error) {}
                }

                card?.addEventListener('click', openAnnouncement);
                modal?.querySelectorAll('[data-announcement-close]').forEach((button) => {
                    button.addEventListener('click', closeAnnouncement);
                });
                modal?.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeAnnouncement();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                        closeAnnouncement();
                    }
                });

                let hasSeenAnnouncement = false;
                try {
                    hasSeenAnnouncement = localStorage.getItem(storageKey) === 'seen';
                } catch (error) {}

                if (!hasSeenAnnouncement) {
                    openAnnouncement();
                }
            });
        @endif
    </script>
</body>

</html>
