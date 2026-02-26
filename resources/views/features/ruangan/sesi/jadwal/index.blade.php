{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\sesi\jadwal\index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Kelola Jadwal Ujian - ' . $sesi->nama_sesi)
@section('page-title', 'Kelola Jadwal Ujian')
@section('page-description', $sesi->nama_sesi . ' - ' . $ruangan->nama_ruangan)

@section('content')
    <div class="py-4">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Breadcrumb Navigation -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('ruangan.index') }}" class="text-gray-700 hover:text-blue-600">
                            <i class="fa-solid fa-door-open mr-2"></i>Ruangan
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right mx-2 text-gray-400"></i>
                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                class="text-gray-700 hover:text-blue-600">
                                {{ $ruangan->nama_ruangan }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right mx-2 text-gray-400"></i>
                            <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                                class="text-gray-700 hover:text-blue-600">
                                {{ $sesi->nama_sesi }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fa-solid fa-chevron-right mx-2 text-gray-400"></i>
                            <span class="text-gray-500">Jadwal Ujian</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Session Info Card -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $sesi->nama_sesi }}</h2>
                    <p class="text-sm text-gray-600 mt-1">{{ $sesi->kode_sesi }}</p>
                    <div class="flex items-center space-x-4 mt-2">
                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-door-open mr-1"></i>
                            {{ $ruangan->nama_ruangan }}
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-calendar-day mr-1"></i>
                            @if ($sesi->jadwalUjians->count() > 0)
                                {{ $sesi->jadwalUjians->first()->tanggal->format('d M Y') }}
                            @else
                                <span class="text-yellow-500">Belum ada jadwal</span>
                            @endif
                        </span>
                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-clock mr-1"></i>
                            {{ $sesi->waktu_mulai ? \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') : '00:00' }} -
                            {{ $sesi->waktu_selesai ? \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') : '00:00' }}
                        </span>
                    </div>
                    <div class="mt-2">
                        <span class="text-sm text-gray-500">
                            <i class="fa-solid fa-users mr-1"></i>
                            {{ $sesi->sesiRuanganSiswa->count() }} siswa terdaftar
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Assigned Jadwal Ujian -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-green-50">
                    <h3 class="text-lg font-medium text-gray-900">Jadwal Ujian Terkait</h3>
                    <p class="text-sm text-gray-500">{{ $sesi->jadwalUjians->count() }} jadwal ujian</p>
                </div>

                @if ($sesi->jadwalUjians->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach ($sesi->jadwalUjians as $jadwal)
                            <div class="p-6">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h4 class="text-lg font-medium text-gray-900">{{ $jadwal->judul }} -
                                            {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m') }}</h4>

                                        <p class="text-sm text-gray-600 mt-1">{{ $jadwal->kode_ujian }}</p>
                                        <div class="flex items-center space-x-4 mt-2">
                                            <span class="text-sm text-gray-500">
                                                <i class="fa-solid fa-book mr-1"></i>
                                                {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                                                @if ($jadwal->mapel && $jadwal->mapel->jurusan)
                                                    <span class="font-medium">({{ $jadwal->mapel->jurusan }})</span>
                                                @elseif($jadwal->mapel)
                                                    <span class="italic">(Semua Jurusan)</span>
                                                @endif
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                <i class="fa-solid fa-clipboard mr-1"></i>
                                                {{ $jadwal->jenis_ujian }}
                                            </span>
                                            <span class="text-sm text-gray-700">
                                                <i class="fa-solid fa-clock mr-1"></i>
                                                <strong>{{ $jadwal->durasi_menit }} menit</strong>
                                            </span>
                                        </div>
                                        <div class="mt-2">
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $jadwal->status == 'aktif'
                                                    ? 'bg-green-100 text-green-800'
                                                    : ($jadwal->status == 'draft'
                                                        ? 'bg-gray-100 text-gray-800'
                                                        : ($jadwal->status == 'selesai'
                                                            ? 'bg-blue-100 text-blue-800'
                                                            : 'bg-red-100 text-red-800')) }}">
                                                {{ ucfirst($jadwal->status) }}
                                            </span>
                                        </div>
                                        <div class="mt-2 text-xs text-gray-500">
                                            @if ($jadwal->kelas_target && is_array($jadwal->kelas_target) && count($jadwal->kelas_target) > 0)
                                                <span class="font-medium">Kelas Target:</span>
                                                {{ implode(', ', $jadwal->kelasTarget()->pluck('nama_kelas')->toArray()) }}
                                            @else
                                                <span class="italic">Tidak ada kelas target yang ditentukan</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <form
                                            action="{{ route('ruangan.sesi.jadwal.destroy', [$ruangan->id, $sesi->id, $jadwal->id]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin melepas jadwal ujian ini dari sesi?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fa-solid fa-unlink"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center">
                        <i class="fa-solid fa-calendar-xmark text-gray-400 text-4xl mb-4"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada jadwal ujian</h3>
                        <p class="mt-1 text-sm text-gray-500">Sesi ini belum memiliki jadwal ujian yang terkait.</p>
                    </div>
                @endif
            </div>

            <!-- Available Jadwal Ujian -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                    <h3 class="text-lg font-medium text-gray-900">Jadwal Ujian Tersedia</h3>
                    <p class="text-sm text-gray-500">{{ $availableJadwals->count() }} jadwal tersedia</p>
                </div>

                @if ($availableJadwals->count() > 0)
                    <form action="{{ route('ruangan.sesi.jadwal.store', [$ruangan->id, $sesi->id]) }}" method="POST">
                        @csrf
                        <div class="p-4">
                            <input type="text" id="search-jadwal" placeholder="Cari jadwal ujian..."
                                class="w-full border-gray-300 rounded-md mb-2">
                        </div>
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-200" id="jadwal-container">
                            @foreach ($availableJadwals as $jadwal)
                                <div class="p-4 jadwal-item">
                                    <label class="flex items-start cursor-pointer">
                                        <input type="checkbox" name="jadwal_ids[]" value="{{ $jadwal->id }}"
                                            class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <div class="flex-1">
                                            <h4 class="text-base font-medium text-gray-900 jadwal-judul">
                                                {{ $jadwal->judul }} -
                                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d/m') }}</h4>
                                            <p class="text-sm text-gray-600 mt-1 jadwal-kode">{{ $jadwal->kode_ujian }}
                                            </p>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="text-sm text-gray-500 jadwal-mapel">
                                                    <i class="fa-solid fa-book mr-1"></i>
                                                    {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                                                    @if ($jadwal->mapel && $jadwal->mapel->jurusan)
                                                        <span class="font-medium">({{ $jadwal->mapel->jurusan }})</span>
                                                    @elseif($jadwal->mapel)
                                                        <span class="italic">(Semua Jurusan)</span>
                                                    @endif
                                                </span>
                                                <span class="text-sm text-gray-500 jadwal-jenis">
                                                    <i class="fa-solid fa-clipboard mr-1"></i>
                                                    {{ $jadwal->jenis_ujian }}
                                                </span>
                                                <span class="text-sm text-gray-700">
                                                    <i class="fa-solid fa-clock mr-1"></i>
                                                    <strong>{{ $jadwal->durasi_menit }} menit</strong>
                                                </span>
                                            </div>
                                            <div class="mt-2">
                                                <span
                                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $jadwal->status == 'aktif'
                                                        ? 'bg-green-100 text-green-800'
                                                        : ($jadwal->status == 'draft'
                                                            ? 'bg-gray-100 text-gray-800'
                                                            : ($jadwal->status == 'selesai'
                                                                ? 'bg-blue-100 text-blue-800'
                                                                : 'bg-red-100 text-red-800')) }}">
                                                    {{ ucfirst($jadwal->status) }}
                                                </span>
                                            </div>
                                            <div class="mt-2 text-xs text-gray-500">
                                                @if ($jadwal->kelas_target && is_array($jadwal->kelas_target) && count($jadwal->kelas_target) > 0)
                                                    <span class="font-medium">Kelas Target:</span>
                                                    {{ implode(', ', $jadwal->kelasTarget()->pluck('nama_kelas')->toArray()) }}
                                                @else
                                                    <span class="italic">Tidak ada kelas target yang ditentukan</span>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="px-6 py-4 bg-gray-50 border-t">
                            <button type="submit"
                                class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <i class="fa-solid fa-link mr-2"></i>
                                Tambahkan Jadwal Terpilih
                            </button>
                        </div>
                    </form>
                @else
                    <div class="p-12 text-center">
                        <i class="fa-solid fa-calendar-check text-gray-400 text-4xl mb-4"></i>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada jadwal tersedia</h3>
                        <p class="mt-1 text-sm text-gray-500">Semua jadwal ujian sudah terkait dengan sesi ini atau tidak
                            ada jadwal ujian yang tersedia.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <div class="text-sm text-gray-600">
                <h4 class="font-medium text-gray-800 mb-2">Catatan Penting:</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Jadwal ujian dengan Mapel yang memiliki jurusan <strong>kosong</strong> berlaku untuk semua jurusan
                    </li>
                    <li>Ketika menambahkan siswa ke sesi, sistem akan otomatis menambahkan jadwal ujian yang sesuai dengan
                        jurusan/kelas siswa</li>
                    <li>Pastikan jadwal ujian memiliki status "Aktif" agar dapat diikuti oleh siswa</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchJadwal = document.getElementById('search-jadwal');
            const jadwalItems = document.querySelectorAll('.jadwal-item');

            if (searchJadwal) {
                searchJadwal.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();

                    jadwalItems.forEach(item => {
                        const judul = item.querySelector('.jadwal-judul').textContent.toLowerCase();
                        const kode = item.querySelector('.jadwal-kode').textContent.toLowerCase();
                        const mapel = item.querySelector('.jadwal-mapel').textContent.toLowerCase();
                        const jenis = item.querySelector('.jadwal-jenis').textContent.toLowerCase();

                        if (judul.includes(searchTerm) || kode.includes(searchTerm) ||
                            mapel.includes(searchTerm) || jenis.includes(searchTerm)) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@endsection
