<!-- filepath: resources\views\features\data\siswa\edit.blade.php -->

@extends('layouts.admin')

@section('title', 'Edit Siswa: ' . ($siswa->nama ?: $siswa->idyayasan))
@section('page-title', 'Edit Siswa')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('data.siswa.update', $siswa) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- ID Yayasan (read-only) -->
                <div class="mb-6">
                    <label for="idyayasan" class="block text-sm font-medium text-gray-700 mb-2">ID Yayasan</label>
                    <input type="text" id="idyayasan" readonly
                        class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100 text-gray-600"
                        value="{{ $siswa->idyayasan }}">
                    <p class="text-sm text-gray-500 mt-1">ID Yayasan cannot be changed</p>
                </div>

                <!-- Nama -->
                <div class="mb-6">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input type="text" name="nama" id="nama"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                        value="{{ old('nama', $siswa->nama) }}" placeholder="Full name">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        value="{{ old('email', $siswa->email) }}" placeholder="student@example.com">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div class="mb-6">
                    <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <input type="text" name="kelas" id="kelas"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('kelas') border-red-500 @enderror"
                        value="{{ old('kelas', $siswa->kelas) }}" placeholder="e.g., XII IPA 1">
                    @error('kelas')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rekomendasi -->
                <div class="mb-6">
                    <label for="rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">Rekomendasi *</label>
                    <select name="rekomendasi" id="rekomendasi" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('rekomendasi') border-red-500 @enderror">
                        <option value="">Choose recommendation</option>
                        <option value="ya" {{ old('rekomendasi', $siswa->rekomendasi) === 'ya' ? 'selected' : '' }}>Ya
                        </option>
                        <option value="tidak" {{ old('rekomendasi', $siswa->rekomendasi) === 'tidak' ? 'selected' : '' }}>
                            Tidak</option>
                    </select>
                    @error('rekomendasi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('data.siswa.show', $siswa) }}"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fa-solid fa-save mr-2"></i>Update Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
