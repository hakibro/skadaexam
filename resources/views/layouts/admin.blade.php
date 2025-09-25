{{-- filepath: c:\laragon\www\skadaexam\resources\views\layouts\admin.blade.php --}}
<!-- filepath: resources\views\layouts\admin.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SKADA Exam') }}</title>

    <!-- Add any additional meta tags from sections -->
    @stack('meta')

    <!-- Your existing CSS and JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('styles')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <!-- Navigation Fixes -->
    <script src="{{ asset('js/sidebar-navigation-fix.js') }}"></script>
    <script src="{{ asset('js/debug-reload.js') }}"></script>
    <script src="{{ asset('js/interval-patch.js') }}"></script>
    <script src="{{ asset('js/role-switching-handler.js') }}"></script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen bg-gray-100">

        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out overflow-y-auto h-screen z-50"
            id="sidebar">

            <!-- Logo -->
            <div class="flex items-center space-x-2 px-4">
                <img src="{{ asset('assets/logo-compressed.png') }}" alt="SKADA Exam Logo" class="h-8 w-8">
                <div>
                    <h2 class="text-xl font-bold">SkadaExam</h2>
                    <p class="text-gray-400 text-sm">Admin Panel</p>
                </div>
            </div>
            <!-- Tombol Close (hanya mobile) -->
            <button class="absolute top-4 right-4 md:hidden text-gray-400 hover:text-white" onclick="toggleSidebar()">
                <i class="fa-solid fa-times text-xl"></i>
            </button>

            <!-- Navigation Menu -->
            <nav class="mt-8">

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-purple-600 text-white' : '' }}">
                    <i class="fa-solid fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>

                @php
                    $user = Auth::user();
                    // Use the proper role checking methods
                    $isAdmin = $user->isAdmin();
                    $isGuru = $user->isGuru(); // Check if user has guru role

                    // Access permissions - admins get everything, teachers get basic access
                    $hasDataAccess = $isAdmin || $user->canManageData() || $isGuru; // Teachers need data access
                    $hasNaskahAccess = $isAdmin || $user->canManageNaskah() || $isGuru; // Teachers need naskah access
                    $hasPengawasAccess =
                        $isAdmin ||
                        $user->canSupervise() ||
                        ($user->guru && $user->guru->count() > 0) ||
                        $user->canCoordinate(); // Teachers and Koordinator can access pengawas
                    $hasKoordinatorAccess = $isAdmin || $user->canCoordinate();
                    $hasRuanganAccess = $isAdmin || $user->canManageRuangan() || $user->canCoordinate();
                @endphp

                <!-- Admin Panel -->
                @if ($isAdmin)
                    {{-- <div class="px-4 py-2 text-gray-500 uppercase text-xs font-semibold tracking-wide">
                        Admin Panel
                    </div> --}}

                    {{-- <a href="{{ route('admin.users.index') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Manage Users</span>
                    </a> --}}
                @endif

                <!-- Feature Modules -->
                <div class="px-4 py-2 text-gray-500 uppercase text-xs font-semibold tracking-wide mt-6">
                    Feature Modules
                </div>

                <!-- Data Management -->
                @if ($hasDataAccess)
                    <div class="space-y-1">
                        <a href="{{ route('data.dashboard') }}"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('data.dashboard') ? 'bg-blue-600 text-white' : '' }}">

                            <i class="fa-solid fa-database"></i>
                            <span>Data Management</span>
                            </button>
                        </a>
                        <a href="{{ route('data.guru.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('data.guru.*') ? 'bg-blue-600 text-white' : '' }}">
                            <i class="fa-solid fa-chalkboard-user text-sm"></i>
                            <span class="text-sm">Guru</span>
                        </a>
                        <a href="{{ route('data.siswa.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('data.siswa.*') ? 'bg-blue-600 text-white' : '' }}">
                            <i class="fa-solid fa-user-graduate text-sm"></i>
                            <span class="text-sm">Siswa</span>
                        </a>
                        <a href="{{ route('data.kelas.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('data.kelas.*') ? 'bg-blue-600 text-white' : '' }}">
                            <i class="fa-solid fa-door-open text-sm"></i>
                            <span class="text-sm">Kelas</span>
                        </a>
                    </div>
                @endif

                <!-- Naskah Management -->
                @if ($hasNaskahAccess)
                    <div class="space-y-1">
                        <a href="{{ route('naskah.dashboard') }}"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.dashboard') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-file-alt"></i>
                            <span>Naskah Management</span>
                        </a>
                        <a href="{{ route('naskah.mapel.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.mapel.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-book text-sm"></i>
                            <span class="text-sm">Mata Pelajaran</span>
                        </a>
                        <a href="{{ route('naskah.banksoal.index') }}"
                            class="flex items-center space-x-2 px-8 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.banksoal.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-folder text-sm"></i>
                            <span class="text-sm">Bank Soal</span>
                        </a>
                        <a href="{{ route('naskah.soal.index') }}"
                            class="flex items-center space-x-2 px-8 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.soal.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-list-check text-sm"></i>
                            <span class="text-sm">Soal</span>
                        </a>
                        <a href="{{ route('naskah.panduan.format-docx') }}"
                            class="flex items-center space-x-2 px-10 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.panduan.format-docx') ? 'bg-green-600 text-white' : '' }}"
                            title="Panduan format file DOCX untuk impor soal">
                            <i class="fa-solid fa-circle-question text-sm"></i>
                            <span class="text-sm">Panduan DOCX</span>
                        </a>

                        <a href="{{ route('naskah.jadwal.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.jadwal.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-calendar-alt text-sm"></i>
                            <span class="text-sm">Jadwal Ujian</span>
                        </a>
                        <a href="{{ route('naskah.hasil.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.hasil.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-chart-bar text-sm"></i>
                            <span class="text-sm">Hasil Ujian</span>
                        </a>
                        <a href="{{ route('naskah.enrollment-ujian.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.enrollment.*') ? 'bg-green-600 text-white' : '' }}">
                            <i class="fa-solid fa-user-plus text-sm"></i>
                            <span class="text-sm">Enrollment</span>
                        </a>
                    </div>
                @endif

                <!-- Pengawas Panel -->
                @if ($hasPengawasAccess)
                    <a href="{{ route('pengawas.dashboard') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('pengawas.*') ? 'bg-purple-600 text-white' : '' }}">
                        <i class="fa-solid fa-eye"></i>
                        <span>Pengawas Panel</span>
                    </a>
                @endif

                <!-- Koordinator Panel -->
                @if ($hasKoordinatorAccess)
                    <div class="space-y-1">
                        <a href="{{ route('koordinator.dashboard') }}"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.dashboard') ? 'bg-yellow-600 text-white' : '' }}">
                            <i class="fa-solid fa-bullseye"></i>
                            <span>Koordinator Panel</span>
                        </a>

                        <a href="{{ route('koordinator.pengawas-assignment.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.pengawas-assignment.*') ? 'bg-yellow-600 text-white' : '' }}">
                            <i class="fa-solid fa-user-check text-sm"></i>
                            <span class="text-sm">Penugasan Pengawas</span>
                        </a>

                        <a href="{{ route('koordinator.pengawas-assignment.calendar') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.pengawas-assignment.calendar') ? 'bg-yellow-600 text-white' : '' }}">
                            <i class="fa-solid fa-calendar-alt text-sm"></i>
                            <span class="text-sm">Kalender Penugasan</span>
                        </a>

                        <a href="{{ route('koordinator.monitoring.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.monitoring.*') ? 'bg-yellow-600 text-white' : '' }}">
                            <i class="fa-solid fa-desktop text-sm"></i>
                            <span class="text-sm">Monitoring Ujian</span>
                        </a>

                        <a href="{{ route('koordinator.laporan.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.laporan.*') ? 'bg-yellow-600 text-white' : '' }}">
                            <i class="fa-solid fa-file-export text-sm"></i>
                            <span class="text-sm">Laporan Koordinasi</span>
                        </a>
                    </div>
                @endif

                <!-- Ruangan Management -->
                @if ($hasRuanganAccess)
                    <div class="space-y-1">
                        <a href="{{ route('ruangan.dashboard') }}"
                            class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('ruangan.dashboard') ? 'bg-red-600 text-white' : '' }}">
                            <i class="fa-solid fa-home"></i>
                            <span>Ruangan Management</span>
                        </a>

                        <a href="{{ route('ruangan.index') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('ruangan.index') || request()->routeIs('ruangan.show') || request()->routeIs('ruangan.create') || request()->routeIs('ruangan.edit') ? 'bg-red-600 text-white' : '' }}">
                            <i class="fa-solid fa-door-open text-sm"></i>
                            <span class="text-sm">Daftar Ruangan</span>
                        </a>



                        {{-- Link untuk Sesi Ruangan yang hanya muncul jika ada parameter ruangan --}}
                        @if (request()->route('ruangan'))
                            <a href="{{ route('ruangan.sesi.index', request()->route('ruangan')) }}"
                                class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('ruangan.sesi.*') ? 'bg-red-600 text-white' : '' }}">
                                <i class="fa-solid fa-clock text-sm"></i>
                                <span class="text-sm">Sesi Ruangan</span>
                            </a>
                        @else
                            <span class="flex items-center space-x-2 px-6 py-1 text-gray-500 cursor-not-allowed">
                                <i class="fa-solid fa-clock text-sm"></i>
                                <span class="text-sm">Sesi Ruangan</span>
                                <span class="text-xs">(pilih ruangan dulu)</span>
                            </span>
                        @endif

                        <a href="{{ route('ruangan.import.comprehensive') }}"
                            class="flex items-center space-x-2 px-6 py-1 text-gray-400 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('ruangan.import*') ? 'bg-red-600 text-white' : '' }}">
                            <i class="fa-solid fa-file-import text-sm"></i>
                            <span class="text-sm">Import Data</span>
                        </a>
                    </div>
                @endif
                <!-- Settings -->
                <div class="px-4 py-2 text-gray-500 uppercase text-xs font-semibold tracking-wide mt-6">
                    Settings
                </div>

                <a href="{{ route('profile.edit') }}"
                    class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors">
                    <i class="fa-solid fa-user-cog"></i>
                    <span>Profile</span>
                </a>

            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Overlay untuk mobile -->
            <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden z-40"
                onclick="toggleSidebar()">
            </div>
            <!-- Top Header -->
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">

                    <!-- Mobile menu button -->
                    <button class="md:hidden text-gray-600 hover:text-gray-800 focus:outline-none"
                        onclick="toggleSidebar()">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>

                    <!-- Page Title -->
                    <div>
                        <h1 class="hidden md:inline text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="hidden md:inline text-sm text-gray-600">@yield('page-description', 'Welcome to admin panel')</p>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-600">{{ Auth::user()->email }}</p>
                            <p class="text-xs text-blue-600 font-semibold capitalize">
                                @php
                                    $displayRole = Auth::user()->role;
                                    if (empty($displayRole) && Auth::user()->isGuru()) {
                                        $displayRole = 'guru';
                                    } elseif (empty($displayRole)) {
                                        $displayRole = Auth::user()->roles->first()?->name ?? 'user';
                                    }
                                @endphp
                                {{ $displayRole }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                                <i class="fa-solid fa-sign-out-alt mr-1"></i>
                                <span class="hidden md:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 relative"
                        role="alert">
                        <p class="font-bold">Berhasil!</p>
                        <p>{{ session('success') }}</p>
                        <button onclick="this.parentElement.style.display='none'"
                            class="absolute top-0 right-0 mt-4 mr-4 text-green-700">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 relative" role="alert">
                        <p class="font-bold">Error!</p>
                        <p>{{ session('error') }}</p>
                        @stack('flash-action')
                        <button onclick="this.parentElement.style.display='none'"
                            class="absolute top-0 right-0 mt-4 mr-4 text-red-700">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 relative"
                        role="alert">
                        <p class="font-bold">Perhatian!</p>
                        <p>{{ session('warning') }}</p>
                        <button onclick="this.parentElement.style.display='none'"
                            class="absolute top-0 right-0 mt-4 mr-4 text-yellow-700">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            const isOpen = !sidebar.classList.contains('-translate-x-full');

            if (isOpen) {
                // Tutup sidebar
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                // Buka sidebar
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            }
        }

        // Auto-hide flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('[role="alert"]');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.display = 'none';
                }, 5000);
            });
        });
        // Dropdown toggle function
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const icon = document.getElementById("icon-" + id);

            if (dropdown.classList.contains("hidden")) {
                dropdown.classList.remove("hidden");
                icon.classList.add("rotate-180"); // animasi chevron
            } else {
                dropdown.classList.add("hidden");
                icon.classList.remove("rotate-180");
            }
        }
    </script>

    <!-- Additional Scripts -->
    @yield('scripts')
</body>

</html>
