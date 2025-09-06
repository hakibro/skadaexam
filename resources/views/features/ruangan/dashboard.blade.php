@extends('layouts.admin')

@section('title', 'Dashboard Ruangan')
@section('page-title', 'Dashboard Ruangan')
@section('page-description', 'Manajemen dan monitoring ruangan ujian')

@section('content')
    <div class="py-4">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                <div class="flex items-center gap-4">
                    <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full">
                        <i class="fa-solid fa-door-open text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $totalRuangan }}</div>
                        <div class="text-gray-600 font-medium">Total Ruangan</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $ruanganStats['aktif'] }} aktif, {{ $ruanganStats['perbaikan'] }} perbaikan
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center gap-4">
                    <div class="bg-green-100 text-green-600 p-3 rounded-full">
                        <i class="fa-solid fa-check-circle text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $ruanganAktif }}</div>
                        <div class="text-gray-600 font-medium">Ruangan Aktif</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Siap digunakan
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center gap-4">
                    <div class="bg-yellow-100 text-yellow-600 p-3 rounded-full">
                        <i class="fa-solid fa-clock text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $sesiAktif }}</div>
                        <div class="text-gray-600 font-medium">Sesi Berlangsung</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $sessionStats['belum_mulai'] }} akan dimulai
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-red-500">
                <div class="flex items-center gap-4">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-gray-800">{{ $kapasitasTotal }}</div>
                        <div class="text-gray-600 font-medium">Kapasitas Total</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Semua ruangan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Clock -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <i class="fa-solid fa-calendar-day text-blue-600 mr-2"></i>
                        Tanggal & Waktu Saat Ini
                    </h3>
                    <p class="text-sm text-gray-600">Semua sesi ujian mengacu pada waktu server</p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-gray-800 current-time">{{ now()->format('H:i:s') }}</div>
                    <div class="text-gray-600">{{ now()->format('l, d F Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Capacity Utilization Today -->
        @if ($totalKapasitasHariIni > 0)
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fa-solid fa-chart-pie text-blue-600 mr-2"></i>
                    Utilisasi Kapasitas Hari Ini
                </h3>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">{{ $totalSiswaHariIni }} dari {{ $totalKapasitasHariIni }}
                        kapasitas</span>
                    <span class="text-sm font-medium text-gray-800">
                        {{ $totalKapasitasHariIni > 0 ? round(($totalSiswaHariIni / $totalKapasitasHariIni) * 100, 1) : 0 }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                        style="width: {{ $totalKapasitasHariIni > 0 ? min(($totalSiswaHariIni / $totalKapasitasHariIni) * 100, 100) : 0 }}%">
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div
                        class="bg-indigo-100 text-indigo-600 p-3 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-plus text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Tambah Ruangan</h3>
                    <p class="text-gray-600 text-sm mb-4">Buat ruang ujian baru</p>
                    <a href="{{ route('ruangan.create') }}"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition text-sm font-medium">
                        Buat Ruangan
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div
                        class="bg-blue-100 text-blue-600 p-3 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-list text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Kelola Ruangan</h3>
                    <p class="text-gray-600 text-sm mb-4">Lihat semua ruangan</p>
                    <a href="{{ route('ruangan.index') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm font-medium">
                        Lihat Daftar
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div
                        class="bg-green-100 text-green-600 p-3 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-calendar-alt text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Template Sesi</h3>
                    <p class="text-gray-600 text-sm mb-4">Kelola template sesi ujian</p>
                    <a href="{{ route('ruangan.template.index') }}"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm font-medium">
                        Lihat Template
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                <div class="text-center">
                    <div
                        class="bg-yellow-100 text-yellow-600 p-3 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-file-import text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Import Data</h3>
                    <p class="text-gray-600 text-sm mb-4">Import dari Excel</p>
                    <a href="{{ route('ruangan.import') }}"
                        class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition text-sm font-medium">
                        Import Data
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Rooms -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fa-solid fa-door-open text-indigo-600 mr-2"></i>
                            Ruangan Terbaru
                        </h2>
                        <a href="{{ route('ruangan.index') }}"
                            class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            Lihat semua →
                        </a>
                    </div>
                </div>

                @if (count($recentRuangan) > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach ($recentRuangan as $ruangan)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div class="bg-indigo-100 text-indigo-600 p-2 rounded-lg">
                                                <i class="fa-solid fa-door-closed"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-gray-900">{{ $ruangan->nama_ruangan }}</h3>
                                                <div class="text-sm text-gray-500">
                                                    {{ $ruangan->kode_ruangan }} • Kapasitas: {{ $ruangan->kapasitas }}
                                                    • {{ $ruangan->sesi_ruangan_count }} sesi
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="px-2 py-1 rounded-full {{ $ruangan->status_badge_class }} text-xs font-medium">{{ $ruangan->status_label['text'] }}</span>
                                        <div class="flex space-x-1">
                                            <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                                class="text-blue-600 hover:text-blue-800 p-1" title="Lihat Detail">
                                                <i class="fa-solid fa-eye text-xs"></i>
                                            </a>
                                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                                class="text-green-600 hover:text-green-800 p-1" title="Kelola Sesi">
                                                <i class="fa-solid fa-calendar-alt text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="text-gray-400 mb-2">
                            <i class="fa-solid fa-door-open text-3xl"></i>
                        </div>
                        <p class="text-gray-500 mb-4">Belum ada ruangan yang ditambahkan</p>
                        <a href="{{ route('ruangan.create') }}"
                            class="text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fa-solid fa-plus mr-1"></i> Tambah ruangan pertama
                        </a>
                    </div>
                @endif
            </div>

            <!-- Today's Sessions -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fa-solid fa-calendar-day text-green-600 mr-2"></i>
                        Sesi Hari Ini
                    </h2>
                    <p class="text-sm text-gray-500">{{ now()->format('d F Y') }}</p>
                </div>

                @if (count($todaySessions) > 0)
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @foreach ($todaySessions as $sesi)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="bg-{{ $sesi->status == 'berlangsung' ? 'green' : ($sesi->status == 'selesai' ? 'gray' : 'blue') }}-100 text-{{ $sesi->status == 'berlangsung' ? 'green' : ($sesi->status == 'selesai' ? 'gray' : 'blue') }}-600 p-2 rounded-lg">
                                                <i class="fa-solid fa-clock"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-medium text-gray-900">{{ $sesi->nama_sesi }}</h3>
                                                <div class="text-sm text-gray-500">
                                                    {{ $sesi->ruangan->nama_ruangan }} •
                                                    {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <span
                                                        class="font-medium">{{ $sesi->sesiRuanganSiswa->count() ?? 0 }}</span>
                                                    siswa •
                                                    <span
                                                        class="font-medium">{{ $sesi->jadwalUjians->count() ?? 0 }}</span>
                                                    jadwal ujian
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesi->status_badge_class }}">
                                            {{ $sesi->status_label['text'] }}
                                        </span>
                                        <a href="{{ route('ruangan.sesi.show', [$sesi->ruangan_id, $sesi->id]) }}"
                                            class="text-blue-600 hover:text-blue-800 p-1" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="text-gray-400 mb-2">
                            <i class="fa-solid fa-calendar-day text-3xl"></i>
                        </div>
                        <p class="text-gray-500 mb-4">Tidak ada sesi ujian hari ini</p>
                        <p class="text-sm text-gray-500">Buat sesi baru atau pilih ruangan untuk menambahkan sesi</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Ongoing Sessions Detail -->
        @if (count($ongoingSessions) > 0)
            <div class="bg-white rounded-lg shadow-lg mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                    <h2 class="text-xl font-bold text-green-800">
                        <i class="fa-solid fa-play-circle text-green-600 mr-2"></i>
                        Sesi Yang Sedang Berlangsung
                    </h2>
                    <p class="text-sm text-green-600">{{ count($ongoingSessions) }} sesi aktif</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ruangan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sesi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengawas
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jadwal Ujian
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ongoingSessions as $session)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $session->ruangan->nama_ruangan }}</div>
                                        <div class="text-sm text-gray-500">{{ $session->ruangan->kode_ruangan }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $session->nama_sesi }}</div>
                                        <div class="text-sm text-gray-500">{{ $session->kode_sesi }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($session->waktu_mulai)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($session->waktu_selesai)->format('H:i') }}
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 mt-1">
                                            <div class="w-full bg-gray-200 rounded-full h-1 mr-1">
                                                <div class="bg-green-600 h-1 rounded-full"
                                                    style="width: {{ $session->progress_percentage }}%">
                                                </div>
                                            </div>
                                            <span>{{ $session->progress_percentage }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $session->pengawas->nama ?? 'Belum ditentukan' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $session->sesiRuanganSiswa->count() }} /
                                            {{ $session->ruangan->kapasitas }}
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                            <div class="bg-blue-600 h-1 rounded-full"
                                                style="width: {{ min(($session->sesiRuanganSiswa->count() / $session->ruangan->kapasitas) * 100, 100) }}%">
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $session->jadwalUjians->count() }} jadwal
                                        </div>
                                        @if ($session->jadwalUjians->count() > 0)
                                            <div class="text-xs text-gray-500">
                                                @foreach ($session->jadwalUjians->take(2) as $jadwal)
                                                    <div class="truncate max-w-[150px]" title="{{ $jadwal->judul }}">
                                                        {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                                                        @if ($jadwal->mapel && $jadwal->mapel->jurusan)
                                                            <span class="text-xs">({{ $jadwal->mapel->jurusan }})</span>
                                                        @elseif($jadwal->mapel)
                                                            <span class="text-xs italic">(Semua Jurusan)</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if ($session->jadwalUjians->count() > 2)
                                                    <span
                                                        class="text-xs text-gray-500">+{{ $session->jadwalUjians->count() - 2 }}
                                                        lainnya</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <a href="{{ route('ruangan.sesi.show', [$session->ruangan_id, $session->id]) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-2" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('ruangan.sesi.siswa.index', [$session->ruangan_id, $session->id]) }}"
                                            class="text-green-600 hover:text-green-900" title="Kelola Siswa">
                                            <i class="fa-solid fa-users"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Department Distribution -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fa-solid fa-info-circle text-blue-600 mr-2"></i>
                Informasi Penting: Konsep Jurusan dalam Ujian
            </h3>
            <div class="prose max-w-none">
                <ul class="space-y-2">
                    <li class="text-sm text-gray-700">
                        <span class="font-medium">Mapel dengan jurusan kosong:</span> Berlaku untuk semua jurusan. Siswa
                        dari jurusan apapun dapat mengikuti ujian ini.
                    </li>
                    <li class="text-sm text-gray-700">
                        <span class="font-medium">Mapel dengan jurusan spesifik:</span> Hanya berlaku untuk siswa dari
                        jurusan tersebut.
                    </li>
                    <li class="text-sm text-gray-700">
                        <span class="font-medium">Mapel dengan jurusan "UMUM":</span> Berlaku untuk semua jurusan seperti
                        halnya mapel dengan jurusan kosong.
                    </li>
                </ul>
                <div class="mt-4 p-2 bg-yellow-50 rounded-md text-sm text-yellow-800">
                    <i class="fa-solid fa-lightbulb mr-1"></i>
                    <strong>Tip:</strong> Saat menambahkan siswa ke sesi ujian, jadwal ujian yang sesuai dengan jurusan
                    siswa akan otomatis ditambahkan ke sesi.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Auto refresh every 30 seconds for ongoing sessions
        setInterval(function() {
            // Only refresh if there are ongoing sessions
            @if (count($ongoingSessions) > 0)
                location.reload();
            @endif
        }, 30000);

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const clockElements = document.querySelectorAll('.current-time');
            clockElements.forEach(el => el.textContent = timeString);
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call
    </script>
@endsection
