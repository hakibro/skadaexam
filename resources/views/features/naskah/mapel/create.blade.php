@extends('layouts.admin')

@section('title', 'Tambah Mata Pelajaran')
@section('page-title', 'Tambah Mata Pelajaran')
@section('page-description', 'Tambahkan mata pelajaran baru')

@section('content')
    <div class="space-y-6">
        <form action="{{ route('naskah.mapel.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Mata Pelajaran</h3>
                    <p class="mt-1 text-sm text-gray-600">Lengkapi informasi mata pelajaran yang akan dibuat.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="kode_mapel" class="block text-sm font-medium text-gray-700">Kode Mapel <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="kode_mapel" id="kode_mapel" required value="{{ old('kode_mapel') }}"
                                class="mt-1 form-input block w-full @error('kode_mapel') border-red-500 @enderror"
                                placeholder="Contoh: MTK-10">
                            @error('kode_mapel')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="nama_mapel" class="block text-sm font-medium text-gray-700">Nama Mapel <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_mapel" id="nama_mapel" required value="{{ old('nama_mapel') }}"
                                class="mt-1 form-input block w-full @error('nama_mapel') border-red-500 @enderror"
                                placeholder="Contoh: Matematika Kelas 10">
                            @error('nama_mapel')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat <span
                                    class="text-red-500">*</span></label>
                            <select name="tingkat" id="tingkat" required
                                class="mt-1 form-select block w-full @error('tingkat') border-red-500 @enderror">
                                <option value="">-- Pilih Tingkat --</option>
                                @foreach ($tingkatList as $tingkat)
                                    <option value="{{ $tingkat }}" {{ old('tingkat') == $tingkat ? 'selected' : '' }}>
                                        {{ $tingkat }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tingkat')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jurusan" class="block text-sm font-medium text-gray-700">Jurusan</label>
                            <select name="jurusan" id="jurusan"
                                class="mt-1 form-select block w-full @error('jurusan') border-red-500 @enderror">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach ($jurusanList as $jurusan)
                                    <option value="{{ $jurusan }}" {{ old('jurusan') == $jurusan ? 'selected' : '' }}>
                                        {{ $jurusan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jurusan')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>



                        <div class="md:col-span-2">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="4"
                                class="mt-1 form-textarea block w-full @error('deskripsi') border-red-500 @enderror"
                                placeholder="Deskripsi mata pelajaran">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t">
                    <a href="{{ route('naskah.mapel.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </a>
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Mata Pelajaran
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
