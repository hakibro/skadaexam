@extends('layouts.admin')

@section('title', 'Tambah Bank Soal')
@section('page-title', 'Tambah Bank Soal Baru')
@section('page-description', 'Buat koleksi soal baru untuk ujian')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('naskah.banksoal.store') }}" method="POST"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Informasi Bank Soal</h2>

                <!-- Judul -->
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700">Judul Bank Soal <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="judul"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('judul') }}" required>
                    @error('judul')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mata Pelajaran -->
                    <div>
                        <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran <span
                                class="text-red-500">*</span></label>
                        <select name="mapel_id" id="mapel_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})
                                </option>
                            @endforeach
                        </select>
                        @error('mapel_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tingkat -->
                    <div>
                        <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat Kelas <span
                                class="text-red-500">*</span></label>
                        <select name="tingkat" id="tingkat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                            <option value="X" {{ old('tingkat') == 'X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="XI" {{ old('tingkat') == 'XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="XII" {{ old('tingkat') == 'XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                        @error('tingkat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status <span
                            class="text-red-500">*</span></label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="arsip" {{ old('status') == 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Soal -->
                <div>
                    <label for="jenis_soal" class="block text-sm font-medium text-gray-700">Jenis Soal</label>
                    <select name="jenis_soal" id="jenis_soal"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="uts" {{ old('jenis_soal') == 'uts' ? 'selected' : '' }}>UTS</option>
                        <option value="uas" {{ old('jenis_soal') == 'uas' ? 'selected' : '' }}>UAS</option>
                        <option value="ulangan" {{ old('jenis_soal') == 'ulangan' ? 'selected' : '' }}>Ulangan</option>
                        <option value="latihan" {{ old('jenis_soal') == 'latihan' ? 'selected' : '' }}>Latihan</option>
                    </select>
                    @error('jenis_soal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('naskah.banksoal.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Bank Soal
                </button>
            </div>
        </form>
    </div>
@endsection
