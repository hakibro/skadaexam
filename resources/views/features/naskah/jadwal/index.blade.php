@extends('layouts.admin')

@section('title', 'Jadwal Ujian')
@section('page-title', 'Jadwal Ujian')
@section('page-description', 'Kelola jadwal ujian dan sesi ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Daftar Jadwal Ujian</h3>
                <a href="{{ route('naskah.jadwal.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Jadwal
                </a>
            </div>

            <div class="p-4 bg-gray-50">
                <form action="{{ route('naskah.jadwal.index') }}" method="get">
                    <div class="flex flex-wrap gap-4">
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                class="form-input w-full md:w-64" placeholder="Cari nama atau kode ujian...">
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="form-select w-full">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai
                                </option>
                                <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>
                                    Dibatalkan</option>
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                            <select name="mapel_id" class="form-select w-full">
                                <option value="">Semua Mapel</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ request('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                class="form-input w-full">
                        </div>
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                class="form-input w-full">
                        </div>
                        <div class="w-full md:w-auto flex items-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                                <i class="fa-solid fa-search mr-2"></i> Filter
                            </button>
                            <a href="{{ route('naskah.jadwal.index') }}"
                                class="inline-flex items-center px-4 py-2 ml-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md">
                                <i class="fa-solid fa-times mr-2"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if (count($jadwalUjians) > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Ujian</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Mapel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jadwal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sesi</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($jadwalUjians as $jadwal)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $jadwal->kode_ujian }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $jadwal->nama_ujian }}</div>
                                        <div class="text-sm text-gray-500">{{ $jadwal->jenis_ujian }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $jadwal->mapel->nama_mapel ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div><i class="fa-solid fa-calendar-day mr-1"></i>
                                            {{ $jadwal->tanggal_ujian->format('d M Y') }}</div>
                                        <div class="text-sm text-gray-500"><i class="fa-solid fa-clock mr-1"></i>
                                            {{ $jadwal->waktu_mulai->format('H:i') }} -
                                            {{ $jadwal->waktu_selesai->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($jadwal->status)
                                            @case('draft')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Draft</span>
                                            @break

                                            @case('aktif')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                            @break

                                            @case('selesai')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Selesai</span>
                                            @break

                                            @case('dibatalkan')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Dibatalkan</span>
                                            @break

                                            @default
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $jadwal->status }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $jadwal->sesiUjians_count ?? $jadwal->sesiUjians->count() }} sesi
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fa-solid fa-eye mr-1"></i> Detail
                                        </a>
                                        <a href="{{ route('naskah.jadwal.edit', $jadwal->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            <i class="fa-solid fa-edit mr-1"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="p-4">
                        {{ $jadwalUjians->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-10">
                        <i class="fa-solid fa-calendar-xmark text-gray-300 text-5xl mb-3"></i>
                        <p class="text-gray-500 text-lg">Belum ada jadwal ujian</p>
                        <p class="text-gray-400 mb-4">Tambahkan jadwal ujian baru untuk memulai</p>
                        <a href="{{ route('naskah.jadwal.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Jadwal Ujian
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
