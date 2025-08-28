<!-- filepath: c:\laragon\www\skadaexam\resources\views\layouts\dashboard.blade.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Aplikasi Ujian Online</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome CDN for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg flex flex-col">
            <div class="p-6 flex items-center gap-3 border-b">
                <i class="fa-solid fa-graduation-cap text-3xl text-blue-600"></i>
                <span class="text-2xl font-bold text-blue-700">Dashboard</span>
            </div>
            <nav class="flex-1 p-4">
                <ul class="space-y-2">
                    @auth
                        @if (auth()->user()->role === 'admin')
                            <!-- Menu Admin (dulu Super Admin) -->
                            <li>
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-crown text-yellow-500"></i>
                                    Dashboard Admin
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-users-cog text-red-500"></i>
                                    Kelola User
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.roles.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-user-shield text-orange-500"></i>
                                    Kelola Role
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'data')
                            <!-- Menu Data (dulu Admin Sekolah) -->
                            <li>
                                <a href="{{ route('data.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('data.guru.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-chalkboard-user text-blue-500"></i>
                                    Guru
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('data.siswa.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-user-graduate text-green-500"></i>
                                    Siswa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('data.kelas.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-users text-purple-500"></i>
                                    Kelas
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'ruangan')
                            <!-- Menu Ruangan -->
                            <li>
                                <a href="{{ route('ruangan.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ruangan.sesi.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-clock text-orange-500"></i>
                                    Sesi Ujian
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('ruangan.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-door-open text-purple-500"></i>
                                    Kelola Ruangan
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'guru')
                            <!-- Menu Guru -->
                            <li>
                                <a href="{{ route('guru.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('guru.ujian.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-clipboard-list text-green-500"></i>
                                    Jadwal Ujian
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('guru.absensi.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-user-check text-orange-500"></i>
                                    Absensi Siswa
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('guru.berita-acara.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-file-alt text-red-500"></i>
                                    Berita Acara
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'pengawas')
                            <!-- Menu Pengawas -->
                            <li>
                                <a href="{{ route('pengawas.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('pengawas.guru-assignment.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-user-tie text-purple-500"></i>
                                    Assign Guru
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('pengawas.laporan.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-chart-bar text-green-500"></i>
                                    Laporan Ujian
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'naskah')
                            <!-- Menu Naskah -->
                            <li>
                                <a href="{{ route('naskah.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('naskah.jadwal.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-calendar text-green-500"></i>
                                    Jadwal Ujian
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('naskah.course.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-book text-orange-500"></i>
                                    Course/Soal
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('naskah.quiz.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-question-circle text-purple-500"></i>
                                    Quiz
                                </a>
                            </li>
                        @elseif(auth()->user()->role === 'siswa')
                            <!-- Menu Siswa -->
                            <li>
                                <a href="{{ route('siswa.dashboard') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-home text-blue-500"></i>
                                    Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('siswa.ujian.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-pencil-alt text-green-500"></i>
                                    Ujian
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('siswa.nilai.index') }}"
                                    class="flex items-center gap-3 px-4 py-2 rounded hover:bg-blue-100 text-gray-700 font-medium transition">
                                    <i class="fa-solid fa-chart-line text-orange-500"></i>
                                    Nilai
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
            </nav>
            <div class="p-4 border-t">
                @auth
                    <div class="flex items-center gap-2 mb-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff"
                            alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-xs text-red-600 hover:text-red-800 transition">
                            <i class="fa-solid fa-sign-out-alt mr-1"></i>
                            Logout
                        </button>
                    </form>
                @endauth
                <div class="text-xs text-gray-400 mt-2">
                    &copy; {{ date('Y') }} Aplikasi Ujian Online
                </div>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 p-8">
            <!-- Topbar -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    @auth
                        <h2 class="text-2xl font-semibold text-gray-700">
                            Selamat Datang, {{ auth()->user()->name }}!
                        </h2>
                        <p class="text-gray-500">
                            @if (auth()->user()->role === 'data')
                                Kelola data guru, siswa, dan kelas dengan mudah.
                            @elseif(auth()->user()->role === 'guru')
                                Pantau ujian dan kelola absensi siswa.
                            @elseif(auth()->user()->role === 'siswa')
                                Akses ujian dan lihat nilai Anda.
                            @else
                                Selamat datang di sistem ujian online.
                            @endif
                        </p>
                    @else
                        <h2 class="text-2xl font-semibold text-gray-700">Selamat Datang!</h2>
                        <p class="text-gray-500">Silakan login untuk mengakses sistem.</p>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    <button class="bg-white border rounded-full p-2 shadow hover:bg-blue-50 transition">
                        <i class="fa-solid fa-bell text-blue-600"></i>
                    </button>
                    @auth
                        <div class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff"
                                alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-full border">
                            <div>
                                <span class="font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <div class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</div>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Role-specific Statistic Cards -->
            @auth
                @if (auth()->user()->role === 'data')
                    <!-- Statistic Cards untuk Data (dulu Admin) -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                        <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                                <i class="fa-solid fa-chalkboard-user fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ $jumlahGuru ?? '...' }}</div>
                                <div class="text-gray-500">Total Guru</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                            <div class="bg-green-100 text-green-600 p-3 rounded-full">
                                <i class="fa-solid fa-user-graduate fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ $jumlahSiswa ?? '...' }}</div>
                                <div class="text-gray-500">Total Siswa</div>
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-6 flex items-center gap-4">
                            <div class="bg-purple-100 text-purple-600 p-3 rounded-full">
                                <i class="fa-solid fa-users fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold">{{ $jumlahKelas ?? '...' }}</div>
                                <div class="text-gray-500">Total Kelas</div>
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Content -->
            @yield('content')
        </main>
    </div>
</body>

</html>
