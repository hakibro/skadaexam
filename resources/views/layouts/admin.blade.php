<!-- filepath: resources\views\layouts\admin.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SKADA Exam') }}</title>

    <!-- Add any additional meta tags from sections -->
    @stack('meta')

    <!-- Your existing CSS and JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 space-y-6 py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out"
            id="sidebar">

            <!-- Logo -->
            <div class="flex items-center space-x-2 px-4">
                <div class="bg-purple-600 p-2 rounded">
                    <i class="fa-solid fa-graduation-cap text-xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold">SKADA Exam</h2>
                    <p class="text-gray-400 text-sm">Admin Panel</p>
                </div>
            </div>

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
                    // Cek role dari field 'role' di database, bukan Spatie roles
                    $isAdmin = $user->role === 'admin' || $user->email === 'admin@skadaexam.test';
                    $hasDataAccess = $isAdmin || $user->role === 'data';
                    $hasNaskahAccess = $isAdmin || $user->role === 'naskah';
                    $hasPengawasAccess = $isAdmin || $user->role === 'pengawas';
                    $hasKoordinatorAccess = $isAdmin || $user->role === 'koordinator';
                    $hasRuanganAccess = $isAdmin || $user->role === 'ruangan';
                @endphp

                <!-- Admin Panel -->
                @if ($isAdmin)
                    <div class="px-4 py-2 text-gray-500 uppercase text-xs font-semibold tracking-wide">
                        Admin Panel
                    </div>

                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-purple-600 text-white' : '' }}">
                        <i class="fa-solid fa-users"></i>
                        <span>Manage Users</span>
                    </a>
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
                    <a href="{{ route('naskah.dashboard') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('naskah.*') ? 'bg-green-600 text-white' : '' }}">
                        <i class="fa-solid fa-file-alt"></i>
                        <span>Naskah Management</span>
                    </a>
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
                    <a href="{{ route('koordinator.dashboard') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('koordinator.*') ? 'bg-yellow-600 text-white' : '' }}">
                        <i class="fa-solid fa-bullseye"></i>
                        <span>Koordinator Panel</span>
                    </a>
                @endif

                <!-- Ruangan Management -->
                @if ($hasRuanganAccess)
                    <a href="{{ route('ruangan.dashboard') }}"
                        class="flex items-center space-x-2 px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white transition-colors {{ request()->routeIs('ruangan.*') ? 'bg-red-600 text-white' : '' }}">
                        <i class="fa-solid fa-home"></i>
                        <span>Ruangan Management</span>
                    </a>
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
                        <h1 class="text-2xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-sm text-gray-600">@yield('page-description', 'Welcome to admin panel')</p>
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-600">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="relative">
                            <button class="bg-gray-300 rounded-full w-10 h-10 flex items-center justify-center">
                                <i class="fa-solid fa-user text-gray-600"></i>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition-colors">
                                <i class="fa-solid fa-sign-out-alt mr-1"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        }
    </script>
</body>

</html>
