{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Ruangan - ' . $ruangan->nama_ruangan)
@section('page-title', 'Detail Ruangan')
@section('page-description', $ruangan->nama_ruangan . ' (' . $ruangan->kode_ruangan . ')')

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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Room Details -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Informasi Ruangan</h3>
                            <div class="flex space-x-2">
                                <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                                    class="px-3 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                                    <i class="fa-solid fa-edit mr-1"></i> Edit
                                </a>
                                <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                                    <i class="fa-solid fa-calendar-alt mr-1"></i> Kelola Sesi
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Kode Ruangan</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $ruangan->kode_ruangan }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @if ($ruangan->status == 'aktif')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Aktif
                                        </span>
                                    @elseif($ruangan->status == 'perbaikan')
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Perbaikan
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Tidak Aktif
                                        </span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Kapasitas</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ruangan->kapasitas }} orang</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Lokasi</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ruangan->lokasi ?: 'Tidak ditentukan' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ruangan->created_at->format('d M Y H:i') }}</dd>
                            </div>
                        </dl>

                        @if ($ruangan->fasilitas && is_array($ruangan->fasilitas))
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <dt class="text-sm font-medium text-gray-500 mb-2">Fasilitas</dt>
                                <dd class="mt-1">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($ruangan->fasilitas as $fasilitas)
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                {{ ucfirst(str_replace('_', ' ', $fasilitas)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </dd>
                            </div>
                        @endif

                        @if ($ruangan->keterangan)
                            <div class="mt-6 border-t border-gray-200 pt-6">
                                <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                                <dd class="mt-2 text-sm text-gray-900">{{ $ruangan->keterangan }}</dd>
                            </div>
                        @endif

                        <div class="mt-6 border-t border-gray-200 pt-6 p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <i class="fa-solid fa-info-circle mr-2 text-blue-600"></i>
                                Status Ruangan
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Status</span>
                                    <span
                                        class="text-sm font-medium capitalize {{ $ruangan->status == 'aktif' ? 'text-green-600' : ($ruangan->status == 'perbaikan' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $ruangan->status }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Terakhir Digunakan</span>
                                    <span class="text-sm text-gray-900">
                                        {{ $lastUsedSession ? $lastUsedSession->tanggal->format('d M Y') : 'Belum pernah' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Sesi Mendatang</span>
                                    <span class="text-sm text-gray-900">
                                        {{ $upcomingSessions }} sesi
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Stats -->
            <div class="space-y-6">
                <!-- Room Statistics -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fa-solid fa-chart-bar mr-2 text-green-600"></i>
                        Statistik
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Sesi</span>
                            <span class="text-lg font-semibold">{{ $ruangan->sesi_ruangan_count }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Sesi Aktif</span>
                            <span class="text-lg font-semibold text-green-600">{{ $activeSessions }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Total Peserta</span>
                            <span class="text-lg font-semibold text-blue-600">{{ $totalParticipants }}</span>
                        </div>

                        <!-- Utilization Progress -->
                        @if ($ruangan->sesi_ruangan_count > 0)
                            <div class="mt-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Utilisasi Bulanan</span>
                                    <span>{{ round(($activeSessions / max($ruangan->sesi_ruangan_count, 1)) * 100) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                        style="width: {{ min(($activeSessions / max($ruangan->sesi_ruangan_count, 1)) * 100, 100) }}%">
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fa-solid fa-bolt mr-2 text-yellow-600"></i>
                        Aksi Cepat
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                            class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded hover:bg-blue-700">
                            <i class="fa-solid fa-calendar-alt mr-2"></i> Kelola Sesi
                        </a>
                        <a href="{{ route('ruangan.sesi.create', $ruangan->id) }}"
                            class="block w-full px-4 py-2 bg-green-600 text-white text-center rounded hover:bg-green-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Sesi Baru
                        </a>
                        <a href="{{ route('ruangan.edit', $ruangan->id) }}"
                            class="block w-full px-4 py-2 bg-yellow-600 text-white text-center rounded hover:bg-yellow-700">
                            <i class="fa-solid fa-edit mr-2"></i> Edit Ruangan
                        </a>
                        <a href="{{ route('ruangan.index') }}"
                            class="block w-full px-4 py-2 bg-gray-600 text-white text-center rounded hover:bg-gray-700">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <!-- Room Status -->

            </div>
        </div>

        <!-- Recent Sessions -->
        @if ($recentSessions->count() > 0)
            <div class="mt-6">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium text-gray-900">Sesi Terbaru</h3>
                            <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                                class="text-sm text-blue-600 hover:text-blue-800">
                                Lihat semua →
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Sesi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Sesi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentSessions as $sesi)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $sesi->kode_sesi }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $sesi->nama_sesi }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ $sesi->tanggal->format('d M Y') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $sesi->sesi_ruangan_siswa_count ?? 0 }} / {{ $ruangan->kapasitas }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesi->status_label['class'] }}">
                                                {{ $sesi->status_label['text'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center space-x-2">
                                                <a href="{{ route('ruangan.sesi.show', [$ruangan->id, $sesi->id]) }}"
                                                    class="text-indigo-600 hover:text-indigo-900" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="{{ route('ruangan.sesi.siswa.index', [$ruangan->id, $sesi->id]) }}"
                                                    class="text-blue-600 hover:text-blue-900" title="Kelola Siswa">
                                                    <i class="fa-solid fa-users"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
