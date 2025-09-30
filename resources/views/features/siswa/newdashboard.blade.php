<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ujian Siswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            box-sizing: border-box;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-indigo-600">📚 SkadaExam</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <button
                            class="bg-gray-100 p-2 rounded-full text-gray-600 hover:text-gray-900 hover:bg-gray-200 transition-colors">
                            🔔
                        </button>
                        <span
                            class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold">
                            A
                        </div>
                        <span class="text-gray-700 font-medium">{{ $siswa->nama }}</span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Section -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang, {{ $siswa->nama }}! 👋</h2>
            <p class="text-gray-600">Kelola ujian dan pantau progress belajar Anda dengan mudah</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-blue-100 text-blue-600 text-lg">
                        📝
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Total Ujian</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-green-100 text-green-600 text-lg">
                        ✅
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Selesai</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stats['selesai'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-yellow-100 text-yellow-600 text-lg">
                        ⏳
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Berlangsung</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stats['berlangsung'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-purple-100 text-purple-600 text-lg">
                        📊
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-600">Mendatang</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stats['mendatang'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pengumuman Button Card -->
            <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-lg shadow-lg p-4 border border-orange-200 hover:shadow-xl transition-all transform hover:scale-105 cursor-pointer relative"
                id="announcementCard">
                <div class="flex items-center">
                    <div class="p-2 rounded-lg bg-white bg-opacity-20 text-white text-lg">
                        📢
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-white opacity-90">Pengumuman</p>
                        <p class="text-lg font-bold text-white">Lihat Info</p>
                    </div>
                </div>
                <div class="absolute top-1 right-1">
                    <span
                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                        Baru!
                    </span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Ujian Mendatang -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900">Ujian Hari Ini</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($activeMapels as $mapel)
                                @php
                                    // Warna gradient per mapel biar variasi
                                    $colors = [
                                        'red' => 'from-red-500 to-red-600',
                                        'green' => 'from-green-500 to-green-600',
                                        'blue' => 'from-blue-500 to-blue-600',
                                        'purple' => 'from-purple-500 to-purple-600',
                                        'orange' => 'from-orange-500 to-orange-600',
                                    ];
                                    $color = $colors[array_rand($colors)];
                                @endphp

                                <div
                                    class="bg-gradient-to-br {{ $color }} rounded-xl p-6 text-white shadow-lg hover:shadow-xl transition-all transform hover:scale-105 relative overflow-hidden">
                                    <div class="mb-4">
                                        <h4 class="text-xl font-bold mb-2">{{ $mapel['mapel_name'] }}</h4>
                                        <p class="text-white/80 text-sm mb-4">
                                            ⏰ Waktu Sesi <br>
                                            {{ \Carbon\Carbon::parse($mapel['waktu_mulai'])->format('H:i') }}
                                            - {{ \Carbon\Carbon::parse($mapel['waktu_selesai'])->format('H:i') }} WIB
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <div class="text-sm">
                                            <span class="text-white/80">Durasi: </span>
                                            <span class="font-semibold">{{ $mapel['durasi_menit'] }} menit</span>
                                        </div>

                                        @if ($mapel['can_access'])
                                            <a href="{{ route('ujian.exam', ['jadwal_id' => $mapel['jadwal_id']]) }}"
                                                class="bg-white text-red-600 px-4 py-2 rounded-lg font-semibold hover:bg-red-50 transition-colors">
                                                Mulai Ujian
                                            </a>
                                        @else
                                            <button disabled
                                                class="bg-white/20 text-white px-4 py-2 rounded-lg font-semibold cursor-not-allowed">
                                                Terkunci
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

            <!-- Petunjuk Ujian -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">📋 Petunjuk Ujian</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-blue-400 text-lg">⏰</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-blue-800">Waktu Ujian</h4>
                                    <p class="text-sm text-blue-700 mt-1">Durasi ujian 90 menit. Timer akan otomatis
                                        berjalan saat ujian dimulai</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-green-400 text-lg">💾</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-green-800">Penyimpanan Otomatis</h4>
                                    <p class="text-sm text-green-700 mt-1">Jawaban tersimpan otomatis setiap 30 detik.
                                        Tidak perlu khawatir kehilangan data</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-yellow-400 text-lg">🌐</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-yellow-800">Koneksi Internet</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Pastikan koneksi internet stabil. Ujian
                                        dapat dilanjutkan jika terputus sementara</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-purple-50 border-l-4 border-purple-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-purple-400 text-lg">📝</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-purple-800">Navigasi Soal</h4>
                                    <p class="text-sm text-purple-700 mt-1">Gunakan tombol "Sebelumnya" dan
                                        "Selanjutnya" untuk berpindah soal</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-red-400 text-lg">⚠️</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-red-800">Peringatan</h4>
                                    <p class="text-sm text-red-700 mt-1">Jangan menutup browser atau refresh halaman
                                        selama ujian berlangsung</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Message Modal -->
        <div id="statusModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 transform transition-all">
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <span id="statusIcon" class="text-4xl"></span>
                    </div>
                    <p id="statusMessage" class="text-gray-700 mb-6 text-lg"></p>
                    <button id="statusClose"
                        class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        @if (session('success') || session('error'))
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const modal = document.getElementById("statusModal");
                    const icon = document.getElementById("statusIcon");
                    const message = document.getElementById("statusMessage");
                    const closeBtn = document.getElementById("statusClose");

                    let statusType = @json(session('success') ? 'success' : 'error');
                    let statusText = @json(session('success') ?? session('error'));

                    // Set message
                    message.textContent = statusText;

                    // Set icon
                    if (statusType === 'success') {
                        icon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                    } else {
                        icon.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                    }

                    // Show modal
                    modal.classList.remove("hidden");

                    // Close handler
                    closeBtn.addEventListener("click", function() {
                        modal.classList.add("hidden");
                    });

                    // Tutup modal jika klik luar area
                    modal.addEventListener("click", function(e) {
                        if (e.target === modal) {
                            modal.classList.add("hidden");
                        }
                    });
                });
            </script>
        @endif

        <!-- Popup Modal for Announcements -->
        <div id="announcementModal"
            class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">📢</span>
                            <h2 class="text-2xl font-bold">Pengumuman Penting</h2>
                        </div>
                        <button id="closeModal" class="text-white hover:text-gray-200 text-2xl font-bold">×</button>
                    </div>
                </div>

                <div class="p-6 overflow-y-auto max-h-96">
                    <div class="space-y-4">
                        <div class="p-4 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-red-400 text-lg">🚨</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-red-800">Perubahan Jadwal Ujian</h4>
                                    <p class="text-sm text-red-700 mt-1">Ujian Matematika dipindah ke Senin, 25 Des
                                        2024 pukul 08:00 WIB. Harap persiapkan diri dengan baik!</p>
                                    <p class="text-xs text-red-600 mt-2 font-medium">📅 Dipublikasi: 2 jam yang lalu
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-blue-400 text-lg">ℹ️</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-blue-800">Hasil Ujian Tersedia</h4>
                                    <p class="text-sm text-blue-700 mt-1">Hasil ujian Biologi sudah dapat dilihat di
                                        menu Hasil Ujian. Selamat untuk yang mendapat nilai memuaskan!</p>
                                    <p class="text-xs text-blue-600 mt-2 font-medium">📅 Dipublikasi: 5 jam yang lalu
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-green-400 text-lg">✅</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-green-800">Pembaruan Sistem</h4>
                                    <p class="text-sm text-green-700 mt-1">Fitur timer ujian telah diperbaiki dan
                                        berfungsi normal. Sistem backup otomatis juga telah diaktifkan.</p>
                                    <p class="text-xs text-green-600 mt-2 font-medium">📅 Dipublikasi: 1 hari yang lalu
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-yellow-400 text-lg">⚠️</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-yellow-800">Maintenance Terjadwal</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Maintenance server dijadwalkan Minggu, 24
                                        Des pukul 02:00-04:00 WIB. Sistem tidak dapat diakses selama periode ini.</p>
                                    <p class="text-xs text-yellow-600 mt-2 font-medium">📅 Dipublikasi: 2 hari yang
                                        lalu</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 bg-purple-50 border-l-4 border-purple-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <span class="text-purple-400 text-lg">🎉</span>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-sm font-semibold text-purple-800">Fitur Baru</h4>
                                    <p class="text-sm text-purple-700 mt-1">Fitur review jawaban sebelum submit telah
                                        ditambahkan. Gunakan tombol "Review" sebelum mengirim ujian.</p>
                                    <p class="text-xs text-purple-600 mt-2 font-medium">📅 Dipublikasi: 3 hari yang
                                        lalu</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t">
                    <div class="flex justify-end">
                        <button id="closeModalBtn"
                            class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-6 py-2 rounded-lg hover:from-orange-600 hover:to-red-600 transition-all font-medium">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Popup Modal Functionality
        const announcementCard = document.getElementById('announcementCard');
        const announcementModal = document.getElementById('announcementModal');
        const closeModal = document.getElementById('closeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');

        // Open modal when announcement card is clicked
        announcementCard.addEventListener('click', function() {
            announcementModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        });

        // Close modal functions
        function closeAnnouncementModal() {
            announcementModal.classList.add('hidden');
            document.body.style.overflow = 'auto'; // Restore scrolling
        }

        closeModal.addEventListener('click', closeAnnouncementModal);
        closeModalBtn.addEventListener('click', closeAnnouncementModal);

        // Close modal when clicking outside
        announcementModal.addEventListener('click', function(e) {
            if (e.target === announcementModal) {
                closeAnnouncementModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !announcementModal.classList.contains('hidden')) {
                closeAnnouncementModal();
            }
        });

        // Add click functionality to buttons
        document.querySelectorAll('button').forEach(button => {
            if (button.textContent.includes('Mulai Ujian')) {
                button.addEventListener('click', function() {
                    alert('Memulai ujian Matematika - Kalkulus. Pastikan koneksi internet stabil!');
                });
            } else if (button.textContent.includes('Lihat Detail')) {
                button.addEventListener('click', function() {
                    alert('Menampilkan detail ujian...');
                });
            }
        });

        // Add notification functionality
        document.querySelector('button[class*="bg-gray-100"]').addEventListener('click', function() {
            alert(
                'Notifikasi:\n• Ujian Matematika dalam 2 hari\n• Hasil Biologi sudah keluar\n• Pengumuman jadwal baru'
            );
        });
    </script>
    <script>
        (function() {
            function c() {
                var b = a.contentDocument || a.contentWindow.document;
                if (b) {
                    var d = b.createElement('script');
                    d.innerHTML =
                        "window.__CF$cv$params={r:'98677e1644ddb5eb',t:'MTc1OTEwNzA5MS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";
                    b.getElementsByTagName('head')[0].appendChild(d)
                }
            }
            if (document.body) {
                var a = document.createElement('iframe');
                a.height = 1;
                a.width = 1;
                a.style.position = 'absolute';
                a.style.top = 0;
                a.style.left = 0;
                a.style.border = 'none';
                a.style.visibility = 'hidden';
                document.body.appendChild(a);
                if ('loading' !== document.readyState) c();
                else if (window.addEventListener) document.addEventListener('DOMContentLoaded', c);
                else {
                    var e = document.onreadystatechange || function() {};
                    document.onreadystatechange = function(b) {
                        e(b);
                        'loading' !== document.readyState && (document.onreadystatechange = e, c())
                    }
                }
            }
        })();
    </script>
</body>

</html>
