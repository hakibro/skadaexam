@extends('layouts.admin')

@section('title', 'Detail Template Sesi')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Detail Template Sesi
        </h2>

        <!-- Breadcrumb -->
        <div class="flex text-sm text-gray-600 mb-4">
            <a href="{{ route('ruangan.template.index') }}" class="hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar template
            </a>
        </div>

        <!-- Flash Messages -->
        @include('components.alert')

        <!-- Template Info Card -->
        <div class="mb-6 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="border-b p-5 flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                        {{ $template->nama_sesi }}
                        <span
                            class="px-2 py-1 text-xs font-medium leading-tight rounded-full {{ $template->activeStatusLabel['class'] }} ml-2">
                            {{ $template->activeStatusLabel['text'] }}
                        </span>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $template->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('ruangan.template.edit', $template->id) }}"
                        class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-yellow-600 border border-transparent rounded-md active:bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:shadow-outline-yellow">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('ruangan.template.show-apply', $template->id) }}"
                        class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-green-600 border border-transparent rounded-md active:bg-green-600 hover:bg-green-700 focus:outline-none focus:shadow-outline-green">
                        <i class="fas fa-check-circle mr-1"></i> Terapkan
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-5">
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Detail Template</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex">
                            <span class="w-32 font-medium">Kode:</span>
                            <span>{{ $template->kode_sesi }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Waktu:</span>
                            <span>{{ \Carbon\Carbon::parse($template->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($template->waktu_selesai)->format('H:i') }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Durasi:</span>
                            <span>{{ \Carbon\Carbon::parse($template->waktu_mulai)->diffInMinutes(\Carbon\Carbon::parse($template->waktu_selesai)) }}
                                menit</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Status Default:</span>
                            <span
                                class="px-2 py-0.5 text-xs font-medium leading-tight rounded-full {{ $template->statusLabel['class'] }}">
                                {{ $template->statusLabel['text'] }}
                            </span>
                        </div>
                        @if ($template->keterangan)
                            <div>
                                <span class="w-32 font-medium block">Keterangan:</span>
                                <p class="mt-1 text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    {{ $template->keterangan }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">Statistik Penggunaan</h4>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Total Sesi Terhubung:</span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full text-blue-600 bg-blue-100">
                                {{ $template->sesiRuangan()->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Sesi Aktif:</span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full text-green-600 bg-green-100">
                                {{ $template->sesiRuangan()->where('status', 'belum_mulai')->orWhere('status', 'berlangsung')->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Sesi Mendatang:</span>
                            <span class="px-3 py-1 text-sm font-medium rounded-full text-purple-600 bg-purple-100">
                                {{ $template->sesiRuangan()->where('tanggal', '>', date('Y-m-d'))->count() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Dibuat Pada:</span>
                            <span class="text-sm text-gray-500">
                                {{ $template->created_at ? $template->created_at->format('d M Y, H:i') : 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm">Terakhir Diperbarui:</span>
                            <span class="text-sm text-gray-500">
                                {{ $template->updated_at ? $template->updated_at->format('d M Y, H:i') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Sessions using this template -->
        @if (count($activeSessions) > 0)
            <div class="mb-6">
                <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4">
                    Sesi Aktif Menggunakan Template Ini
                </h4>
                <div class="bg-white rounded-lg shadow-md dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
                            <thead>
                                <tr
                                    class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                    <th class="px-4 py-3">Ruangan</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Waktu</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                                @foreach ($activeSessions as $sesi)
                                    <tr class="text-gray-700 dark:text-gray-400">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('ruangan.show', $sesi->ruangan_id) }}"
                                                class="text-blue-600 hover:underline">
                                                {{ $sesi->ruangan->nama_ruangan }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ $sesi->tanggal->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            {{ \Carbon\Carbon::parse($sesi->waktu_mulai)->format('H:i') }} -
                                            {{ \Carbon\Carbon::parse($sesi->waktu_selesai)->format('H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="px-2 py-1 font-semibold leading-tight rounded-full {{ $sesi->statusBadgeClass }}">
                                                {{ $sesi->status_label['text'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('ruangan.sesi.show', ['ruangan' => $sesi->ruangan_id, 'sesi' => $sesi->id]) }}"
                                                class="text-blue-600 hover:underline">
                                                <i class="fas fa-eye mr-1"></i> Lihat
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                <p class="text-gray-600 dark:text-gray-400">
                    Belum ada sesi aktif yang menggunakan template ini.
                    <a href="{{ route('ruangan.template.show-apply', $template->id) }}"
                        class="text-blue-600 hover:underline">
                        Klik di sini untuk menerapkan template ke ruangan.
                    </a>
                </p>
            </div>
        @endif
    </div>
@endsection
