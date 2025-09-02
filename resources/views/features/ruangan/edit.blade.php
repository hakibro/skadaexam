@extends('layouts.admin')

@section('title', 'Edit Ruangan')
@section('page-title', 'Edit Ruangan')
@section('page-description', 'Edit data ruangan')

@section('content')
    <div class="py-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <form action="{{ route('ruangan.update', $ruangan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="nama_ruangan" class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_ruangan" id="nama_ruangan"
                            value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('nama_ruangan') border-red-500 @enderror"
                            required>
                        @error('nama_ruangan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kode_ruangan" class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="kode_ruangan" id="kode_ruangan"
                            value="{{ old('kode_ruangan', $ruangan->kode_ruangan) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('kode_ruangan') border-red-500 @enderror"
                            required>
                        @error('kode_ruangan')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="kapasitas" class="block text-sm font-medium text-gray-700 mb-1">Kapasitas <span
                                class="text-red-500">*</span></label>
                        <input type="number" name="kapasitas" id="kapasitas"
                            value="{{ old('kapasitas', $ruangan->kapasitas) }}" min="1" max="1000"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('kapasitas') border-red-500 @enderror"
                            required>
                        @error('kapasitas')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                        <input type="text" name="lokasi" id="lokasi" value="{{ old('lokasi', $ruangan->lokasi) }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('lokasi') border-red-500 @enderror">
                        @error('lokasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fasilitas</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @php
                            $fasilitas = old('fasilitas', $ruangan->fasilitas ?? []);
                        @endphp

                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="wifi" value="wifi"
                                {{ is_array($fasilitas) && in_array('wifi', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="wifi" class="ml-2 text-sm text-gray-700">WiFi</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="ac" value="ac"
                                {{ is_array($fasilitas) && in_array('ac', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="ac" class="ml-2 text-sm text-gray-700">AC</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="proyektor" value="proyektor"
                                {{ is_array($fasilitas) && in_array('proyektor', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="proyektor" class="ml-2 text-sm text-gray-700">Proyektor</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="komputer" value="komputer"
                                {{ is_array($fasilitas) && in_array('komputer', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="komputer" class="ml-2 text-sm text-gray-700">Komputer</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="papan_tulis" value="papan_tulis"
                                {{ is_array($fasilitas) && in_array('papan_tulis', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="papan_tulis" class="ml-2 text-sm text-gray-700">Papan Tulis</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="cctv" value="cctv"
                                {{ is_array($fasilitas) && in_array('cctv', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="cctv" class="ml-2 text-sm text-gray-700">CCTV</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="printer" value="printer"
                                {{ is_array($fasilitas) && in_array('printer', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="printer" class="ml-2 text-sm text-gray-700">Printer</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="fasilitas[]" id="speaker" value="speaker"
                                {{ is_array($fasilitas) && in_array('speaker', $fasilitas) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="speaker" class="ml-2 text-sm text-gray-700">Speaker</label>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span
                            class="text-red-500">*</span></label>
                    <select name="status" id="status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('status') border-red-500 @enderror"
                        required>
                        <option value="aktif" {{ old('status', $ruangan->status) == 'aktif' ? 'selected' : '' }}>Aktif
                        </option>
                        <option value="perbaikan" {{ old('status', $ruangan->status) == 'perbaikan' ? 'selected' : '' }}>
                            Perbaikan</option>
                        <option value="tidak_aktif"
                            {{ old('status', $ruangan->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 @error('deskripsi') border-red-500 @enderror">{{ old('deskripsi', $ruangan->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-between">
                    <div>
                        <a href="{{ route('ruangan.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                        </a>
                        <a href="{{ route('ruangan.show', $ruangan->id) }}"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 ml-2">
                            <i class="fa-solid fa-eye mr-2"></i> Lihat Detail
                        </a>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700">
                        <i class="fa-solid fa-save mr-2"></i> Update
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Status Update -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status Ruangan</h3>

            <form action="{{ route('ruangan.update-status', $ruangan->id) }}" method="POST" class="flex items-center">
                @csrf
                @method('PUT')

                <div class="flex-grow">
                    <select name="status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="aktif" {{ $ruangan->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="perbaikan" {{ $ruangan->status == 'perbaikan' ? 'selected' : '' }}>Perbaikan
                        </option>
                        <option value="tidak_aktif" {{ $ruangan->status == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>
                </div>

                <button type="submit" class="ml-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    <i class="fa-solid fa-check mr-2"></i> Update Status
                </button>
            </form>
        </div>

        <!-- Delete Section -->
        <div class="bg-white rounded-lg shadow-lg p-6 mt-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Hapus Ruangan</h3>
            <p class="text-gray-500 mb-4">Tindakan ini tidak dapat dibatalkan. Ruangan akan dihapus secara permanen dari
                sistem.</p>

            <form action="{{ route('ruangan.destroy', $ruangan->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                    onclick="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?')">
                    <i class="fa-solid fa-trash mr-2"></i> Hapus Ruangan
                </button>
            </form>
        </div>
    </div>
@endsection
