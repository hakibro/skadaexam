@extends('layouts.admin')

@section('title', 'Edit Mata Pelajaran')
@section('page-title', 'Edit Mata Pelajaran')
@section('page-description', $mapel->nama_mapel)

@section('content')
    <div>
        <form action="{{ route('naskah.mapel.update', $mapel->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kode Mapel -->
                        <div>
                            <label for="kode_mapel" class="block text-sm font-medium text-gray-700">Kode Mapel <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="kode_mapel" id="kode_mapel"
                                value="{{ old('kode_mapel', $mapel->kode_mapel) }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm @error('kode_mapel') border-red-500 @else border-gray-300 @enderror rounded-md"
                                required>
                            @error('kode_mapel')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Mapel -->
                        <div>
                            <label for="nama_mapel" class="block text-sm font-medium text-gray-700">Nama Mata Pelajaran
                                <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_mapel" id="nama_mapel"
                                value="{{ old('nama_mapel', $mapel->nama_mapel) }}"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm @error('nama_mapel') border-red-500 @else border-gray-300 @enderror rounded-md"
                                required>
                            @error('nama_mapel')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tingkat -->
                        <div>
                            <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span
                                    class="text-red-500">*</span></label>
                            <select name="tingkat" id="tingkat" required
                                class="mt-1 form-select block w-full @error('tingkat') border-red-500 @enderror">
                                <option value="">-- Pilih Tingkat --</option>
                                @foreach ($tingkatList as $tingkat)
                                    <option value="{{ $tingkat }}"
                                        {{ old('tingkat', $mapel->tingkat ?? '') == $tingkat ? 'selected' : '' }}>
                                        {{ $tingkat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tingkat')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jurusan -->
                        <div>
                            <label for="jurusan" class="block text-sm font-medium text-gray-700">Jurusan</label>
                            <select name="jurusan" id="jurusan"
                                class="mt-1 form-select block w-full @error('jurusan') border-red-500 @enderror">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach ($jurusanList as $jurusan)
                                    <option value="{{ $jurusan }}"
                                        {{ old('jurusan', $mapel->jurusan ?? '') == $jurusan ? 'selected' : '' }}>
                                        {{ $jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jurusan')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                <option value="aktif" {{ old('status', $mapel->status) == 'aktif' ? 'selected' : '' }}>
                                    Aktif</option>
                                <option value="nonaktif"
                                    {{ old('status', $mapel->status) == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>


                    </div>

                    <!-- Deskripsi -->
                    <div class="mt-6">
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                            class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('deskripsi', $mapel->deskripsi) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Deskripsi singkat tentang mata pelajaran</p>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between">
                    <a href="{{ route('naskah.mapel.show', $mapel->id) }}"
                        class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
