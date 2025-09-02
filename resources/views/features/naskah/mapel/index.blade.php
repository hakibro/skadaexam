@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Daftar Mata Pelajaran')
@section('page-description', 'Kelola data mata pelajaran untuk ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Daftar Mata Pelajaran</h3>
                <a href="{{ route('naskah.mapel.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Mapel
                </a>
            </div>

            <div class="p-4 bg-gray-50">
                <form action="{{ route('naskah.mapel.index') }}" method="get">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-input w-full md:w-64" placeholder="Cari nama atau kode mapel...">
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="form-select w-full">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif
                                </option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                            <select name="tingkat" class="form-select w-full">
                                <option value="">Semua Tingkat</option>
                                @foreach ($tingkats as $tingkat)
                                    <option value="{{ $tingkat }}"
                                        {{ request('tingkat') == $tingkat ? 'selected' : '' }}>
                                        {{ $tingkat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                            <select name="jurusan" class="form-select w-full">
                                <option value="">Semua Jurusan</option>
                                @foreach ($jurusans as $jurusan)
                                    <option value="{{ $jurusan }}"
                                        {{ request('jurusan') == $jurusan ? 'selected' : '' }}>
                                        {{ $jurusan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto flex items-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                                <i class="fa-solid fa-search mr-2"></i> Filter
                            </button>
                            <a href="{{ route('naskah.mapel.index') }}"
                                class="inline-flex items-center px-4 py-2 ml-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md">
                                <i class="fa-solid fa-times mr-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if (count($mapels) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mata Pelajaran</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tingkat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jurusan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($mapels as $mapel)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $mapel->kode_mapel }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-md flex items-center justify-center text-blue-600">
                                                <i class="fa-solid fa-book-open text-xl"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $mapel->nama_mapel }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapel->tingkat }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $mapel->jurusan ?? 'Umum' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mapel->status == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($mapel->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('naskah.mapel.show', $mapel->id) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('naskah.mapel.edit', $mapel->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4">
                        {{ $mapels->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="fa-solid fa-book text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">Belum ada data mata pelajaran</p>
                        <p class="text-gray-400 mb-4">Tambahkan mata pelajaran untuk memulai</p>
                        <a href="{{ route('naskah.mapel.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Mata Pelajaran
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
