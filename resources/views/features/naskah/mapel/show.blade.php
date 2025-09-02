@extends('layouts.admin')

@section('title', 'Detail Mata Pelajaran')
@section('page-title', 'Detail Mata Pelajaran')
@section('page-description', $mapel->nama_mapel)

@section('content')
    <div class="space-y-6">
        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <div class="flex space-x-2">
                <a href="{{ route('naskah.mapel.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
                <a href="{{ route('naskah.mapel.edit', $mapel->id) }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-yellow-700 bg-yellow-50 hover:bg-yellow-100">
                    <i class="fa-solid fa-edit mr-2"></i> Edit
                </a>
            </div>

            <div class="flex space-x-2">
                <form action="{{ route('naskah.mapel.status', $mapel->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    @if ($mapel->status == 'aktif')
                        <input type="hidden" name="status" value="nonaktif">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                            <i class="fa-solid fa-ban mr-2"></i> Nonaktifkan
                        </button>
                    @else
                        <input type="hidden" name="status" value="aktif">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fa-solid fa-check mr-2"></i> Aktifkan
                        </button>
                    @endif
                </form>

                <form action="{{ route('naskah.mapel.destroy', $mapel->id) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-700 hover:bg-red-800"
                        onclick="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini? Tindakan ini tidak dapat dibatalkan.')">
                        <i class="fa-solid fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Mapel Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2 bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Informasi Mata Pelajaran</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Detail lengkap mata pelajaran.</p>
                    </div>
                    <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mapel->status == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($mapel->status) }}
                    </span>
                </div>

                <div class="border-b border-gray-200">
                    <dl>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Kode Mapel</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $mapel->kode_mapel }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Nama Mapel</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $mapel->nama_mapel }}</dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Tingkat</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">Kelas {{ $mapel->tingkat }}</dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 border-b">
                            <dt class="text-sm font-medium text-gray-500">Jurusan</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $mapel->jurusan ?? 'Umum' }}
                            </dd>
                        </div>
                        <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ ucfirst($mapel->status) }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($mapel->deskripsi)
                    <div class="px-4 py-5 sm:px-6">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Deskripsi</h4>
                        <div class="prose max-w-none text-sm text-gray-900">
                            {{ $mapel->deskripsi }}
                        </div>
                    </div>
                @endif

                <!-- Bank Soal Terbaru -->
                <div class="px-4 py-5 sm:px-6 border-t">
                    <h4 class="text-base font-medium text-gray-700 mb-3">Bank Soal Terkait</h4>
                    @if (count($latestBankSoals) > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach ($latestBankSoals as $bankSoal)
                                <li class="py-3">
                                    <a href="{{ route('naskah.banksoal.show', $bankSoal->id) }}"
                                        class="hover:text-blue-600">
                                        <div class="flex justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $bankSoal->judul }}</p>
                                                <p class="text-xs text-gray-500">Dibuat:
                                                    {{ $bankSoal->created_at->format('d M Y') }}</p>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                {{ $bankSoal->total_soal ?? 0 }} soal
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        @if ($mapel->bankSoals->count() > 5)
                            <div class="mt-3 text-right">
                                <a href="{{ route('naskah.banksoal.index', ['mapel_id' => $mapel->id]) }}"
                                    class="text-sm text-blue-600 hover:text-blue-800">Lihat semua bank
                                    soal</a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500">Belum ada bank soal untuk mata pelajaran ini</p>
                            <a href="{{ route('naskah.banksoal.create', ['mapel_id' => $mapel->id]) }}"
                                class="mt-2 inline-flex items-center text-sm text-blue-600 hover:text-blue-900">
                                <i class="fa-solid fa-plus mr-1"></i> Tambah Bank Soal
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Side Info -->
            <div class="space-y-6">
                <!-- Icon -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Icon</h3>
                    </div>
                    <div class="p-4 text-center">
                        <div
                            class="h-40 w-40 bg-blue-100 rounded-md flex items-center justify-center text-blue-600 mx-auto">
                            <i class="fa-solid fa-book-open text-6xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Statistik</h3>
                    </div>
                    <div class="p-4">
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Bank Soal</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $mapel->bankSoals->count() }}</dd>
                            </div>
                            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Jadwal Ujian</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalJadwal }}</dd>
                            </div>
                            <div class="px-4 py-5 bg-gray-50 shadow rounded-lg overflow-hidden sm:p-6">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Soal</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ $mapel->soals->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Tambahan</h3>
                    </div>
                    <div class="p-4">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Dibuat pada</dt>
                                <dd class="text-sm text-gray-900">{{ $mapel->created_at->format('d M Y, H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Terakhir diupdate</dt>
                                <dd class="text-sm text-gray-900">{{ $mapel->updated_at->format('d M Y, H:i') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="bg-gray-50 shadow-md rounded-lg overflow-hidden border border-gray-200">
                    <div class="p-4">
                        <h3 class="text-md font-medium text-gray-700 mb-3">Aksi Lainnya</h3>
                        <div class="space-y-2">
                            <a href="{{ route('naskah.mapel.edit', $mapel->id) }}"
                                class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                <i class="fa-solid fa-edit w-5 h-5 mr-3 text-gray-400"></i>
                                Edit Mata Pelajaran
                            </a>
                            <a href="{{ route('naskah.banksoal.create') }}"
                                class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                <i class="fa-solid fa-folder-plus w-5 h-5 mr-3 text-green-500"></i>
                                Tambah Bank Soal
                            </a>
                            <a href="{{ route('naskah.jadwal.create') }}"
                                class="flex items-center p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700">
                                <i class="fa-solid fa-calendar-plus w-5 h-5 mr-3 text-blue-500"></i>
                                Buat Jadwal Ujian
                            </a>
                            <form action="{{ route('naskah.mapel.destroy', $mapel->id) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="flex items-center w-full p-2 rounded-md hover:bg-gray-100 text-sm text-gray-700"
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus mata pelajaran ini? Tindakan ini tidak dapat dibatalkan.')">
                                    <i class="fa-solid fa-trash w-5 h-5 mr-3 text-red-400"></i>
                                    Hapus Mata Pelajaran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
