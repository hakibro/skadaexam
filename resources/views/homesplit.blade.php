<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pilihan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen w-screen">

    @auth
        <!-- Jika web user sudah login, redirect ke dashboard sesuai role -->
        <script>
            window.location.href = '{{ route('dashboard') }}';
        </script>
    @endauth

    @auth('siswa')
        <!-- Jika siswa sudah login, redirect ke dashboard siswa -->
        <script>
            window.location.href = '{{ route('siswa.dashboard') }}';
        </script>
    @else
        <!-- Tampilkan pilihan login jika belum login -->
        <div class="flex flex-col md:flex-row h-full">

            <!-- Siswa Section -->
            <a href="{{ route('login.siswa') }}"
                class="flex-1 flex flex-col items-center justify-center bg-blue-600 text-white hover:bg-blue-700 transition-all duration-300 group">
                <div class="text-center">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div class="text-3xl font-bold">Login Siswa</div>
                    <div class="text-lg opacity-90 mt-2">Akses ujian online</div>
                </div>
            </a>

            <!-- Guru Section -->
            <a href="{{ route('login.guru') }}"
                class="flex-1 flex flex-col items-center justify-center bg-green-600 text-white hover:bg-green-700 transition-all duration-300 group">
                <div class="text-center">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div class="text-3xl font-bold">Login Guru</div>
                    <div class="text-lg opacity-90 mt-2">Portal guru</div>
                </div>
            </a>

            <!-- Admin Section -->
            <a href="{{ route('login') }}"
                class="flex-1 flex flex-col items-center justify-center bg-purple-600 text-white hover:bg-purple-700 transition-all duration-300 group">
                <div class="text-center">
                    <div class="text-6xl mb-4 transform group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                    <div class="text-3xl font-bold">Login Admin</div>
                    <div class="text-lg opacity-90 mt-2">Panel administrasi</div>
                </div>
            </a>

        </div>
    @endauth

    <!-- FontAwesome CDN untuk icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

</body>

</html>
