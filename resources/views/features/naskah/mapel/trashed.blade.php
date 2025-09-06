@extends('layouts.admin')

@section('title', 'Mapel Terhapus')
@section('page-title', 'Mapel Terhapus')
@section('page-description', 'Kelola data mata pelajaran yang sudah dihapus (soft-deleted)')

@section('content')
    <div class="mb-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6 border-b">
                <div class="flex flex-wrap justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Daftar Mapel Terhapus</h3>

                    <div class="space-x-2">
                        <a href="{{ route('naskah.mapel.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                        </a>

                        @if (count($trashedMapels) > 0)
                            <a href="{{ route('naskah.mapel.trashed.restore-all') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                                onclick="return confirm('Pulihkan semua mapel yang sudah dihapus?')">
                                <i class="fa-solid fa-trash-arrow-up mr-2"></i> Pulihkan Semua
                            </a>

                            <a href="{{ route('naskah.mapel.trashed.force-delete-all') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                                onclick="return confirm('PERHATIAN! Tindakan ini tidak dapat dibatalkan. Hapus permanen semua mapel ini?')">
                                <i class="fa-solid fa-trash mr-2"></i> Hapus Permanen Semua
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if (count($trashedMapels) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Mapel
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tingkat / Jurusan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Dihapus
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($trashedMapels as $mapel)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $mapel->kode_mapel }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $mapel->nama_mapel }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                            {{ $mapel->tingkat ?? 'Semua' }}
                                        </span>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $mapel->jurusan ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $mapel->deleted_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('naskah.mapel.trashed.restore', $mapel->id) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fa-solid fa-trash-arrow-up"></i> Pulihkan
                                        </a>
                                        <a href="{{ route('naskah.mapel.trashed.force-delete', $mapel->id) }}"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('PERHATIAN! Tindakan ini tidak dapat dibatalkan. Hapus permanen mapel ini?')">
                                            <i class="fa-solid fa-trash"></i> Hapus Permanen
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6 text-center">
                    <div class="text-gray-500 mb-4">
                        <i class="fa-solid fa-check-circle text-3xl text-green-500"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Tidak ada mapel yang dihapus</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Semua data mata pelajaran tersedia di sistem.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection
