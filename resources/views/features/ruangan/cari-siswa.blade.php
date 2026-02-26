@extends('layouts.admin')

@section('title', 'Cari Siswa di Ruangan/Sesi')
@section('page-title', 'Cari Siswa di Ruangan/Sesi')
@section('page-description', 'Cari siswa berdasarkan nama, ID Yayasan')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Notifikasi Sukses / Error --}}
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Card Utama Pencarian -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Cari Siswa di Ruangan / Sesi</h3>
                </div>
                <div class="p-6 bg-white">
                    <!-- Form Pencarian -->
                    <form action="{{ route('ruangan.cari-siswa') }}" method="GET" class="mb-6">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <input type="text" name="q" value="{{ request('q') }}"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Masukkan nama / ID Yayasan...">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-search mr-2"></i> Cari
                            </button>
                        </div>
                    </form>

                    <!-- Hasil Pencarian -->
                    @if (request()->has('q'))
                        @if ($siswas->count() > 0)
                            <div class="space-y-6">
                                @foreach ($siswas as $siswa)
                                    <!-- Card per Siswa -->
                                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                        <!-- Header Siswa -->
                                        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div>
                                                    <h4 class="text-lg font-semibold text-gray-800">{{ $siswa->nama }}</h4>
                                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600 mt-1">
                                                        <span><span class="font-medium">ID Yayasan:</span>
                                                            {{ $siswa->idyayasan }}</span>
                                                        <span><span class="font-medium">Kelas:</span>
                                                            {{ $siswa->kelas->nama_kelas ?? '-' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-sm bg-white px-3 py-1 rounded-full border border-gray-300">
                                                    <span class="font-medium">Total Sesi:</span>
                                                    {{ $siswa->sesiRuanganSiswa->count() }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Body: Daftar Sesi -->
                                        <div class="p-4">
                                            @if ($siswa->sesiRuanganSiswa->count() > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    @foreach ($siswa->sesiRuanganSiswa as $sesiSiswa)
                                                        @php
                                                            $sesi = $sesiSiswa->sesiRuangan;
                                                            $ruangan = $sesi->ruangan ?? null;
                                                        @endphp
                                                        <div
                                                            class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:shadow transition flex flex-col h-full">
                                                            <!-- Info Ruangan & Status -->
                                                            <div class="flex justify-between items-start mb-2">
                                                                <a class="text-gray-800 hover:text-blue-600"
                                                                    href="{{ route('ruangan.sesi.show', ['ruangan' => $ruangan->id ?? 0, 'sesi' => $sesi->id]) }}">

                                                                    <span class="font-semibold  truncate"
                                                                        title="{{ $ruangan->nama_ruangan ?? 'Unknown' }}">
                                                                        {{ $ruangan->nama_ruangan ?? 'Unknown' }}
                                                                        - {{ $sesi->kode_sesi }}
                                                                    </span>
                                                                    <i class="fas fa-link ml-2"></i>
                                                                </a>

                                                                @php
                                                                    $statusColors = [
                                                                        'berlangsung' => 'bg-green-100 text-green-800',
                                                                        'selesai' => 'bg-gray-100 text-gray-800',
                                                                        'dibatalkan' => 'bg-red-100 text-red-800',
                                                                        'default' => 'bg-yellow-100 text-yellow-800',
                                                                    ];
                                                                    $color =
                                                                        $statusColors[$sesi->status] ??
                                                                        $statusColors['default'];
                                                                @endphp
                                                                <span
                                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                                                    {{ ucfirst($sesi->status) }}
                                                                </span>
                                                            </div>

                                                            <!-- Detail Sesi -->
                                                            <div class="text-xs space-y-1 text-gray-700 flex-1">
                                                                <div><span class="font-medium">Sesi:</span>
                                                                    {{ $sesi->nama_sesi }}</div>
                                                                <div><span class="font-medium">Waktu:</span>
                                                                    {{ substr($sesi->waktu_mulai, 0, 5) }} -
                                                                    {{ substr($sesi->waktu_selesai, 0, 5) }}
                                                                </div>
                                                                <div><span class="font-medium">Kehadiran:</span>
                                                                    @php
                                                                        $kehadiranColors = [
                                                                            'hadir' => 'bg-green-100 text-green-800',
                                                                            'tidak_hadir' => 'bg-red-100 text-red-800',
                                                                            'sakit' => 'bg-yellow-100 text-yellow-800',
                                                                            'izin' => 'bg-blue-100 text-blue-800',
                                                                            'default' => 'bg-gray-100 text-gray-800',
                                                                        ];
                                                                        $kehadiran = $sesiSiswa->status_kehadiran;
                                                                        $kehadiranColor =
                                                                            $kehadiranColors[$kehadiran] ??
                                                                            $kehadiranColors['default'];
                                                                    @endphp
                                                                    <span
                                                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $kehadiranColor }}">
                                                                        {{ ucfirst(str_replace('_', ' ', $kehadiran)) }}
                                                                    </span>
                                                                </div>

                                                                <!-- Jadwal Ujian (compact) -->
                                                                @if ($sesi->jadwalUjians->count() > 0)
                                                                    <div class="mt-2">
                                                                        <span class="font-medium">Tanggal Ujian:</span>
                                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                                            @foreach ($sesi->jadwalUjians as $jadwal)
                                                                                <span
                                                                                    class="inline-flex items-center px-2 py-0.5 rounded-md text-xs bg-indigo-100 text-indigo-800">
                                                                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m') }}
                                                                                </span>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <div class="mt-2 text-gray-400 italic text-xs">Tidak ada
                                                                        jadwal</div>
                                                                @endif
                                                            </div>

                                                            <!-- Tombol Hapus -->
                                                            <div class="mt-3 flex justify-end">
                                                                <form
                                                                    action="{{ route('ruangan.sesi.siswa.destroy', [$sesi->ruangan_id, $sesi->id, $siswa->id]) }}"
                                                                    method="POST"
                                                                    onsubmit="return confirm('Yakin ingin menghapus siswa ini dari sesi? Semua data enrollment ujian terkait juga akan dihapus.');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit"
                                                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                                        <i class="fas fa-trash mr-1"></i> Hapus dari sesi
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-8 text-gray-400">
                                                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                                                    <p>Belum pernah terdaftar di sesi manapun</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $siswas->links('pagination::tailwind') }}
                            </div>
                        @else
                            <div class="rounded-md bg-blue-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            Tidak ada siswa yang ditemukan dengan kata kunci "{{ request('q') }}".
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
