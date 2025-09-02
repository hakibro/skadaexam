{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\data\siswa\edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Rekomendasi: ' . ($siswa->nama ?: $siswa->idyayasan))
@section('page-title', 'Edit Student Recommendation')

@section('content')
    <div class="max-w-2xl mx-auto">

        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('data.dashboard') }}" class="text-gray-700 hover:text-blue-600">
                        <i class="fa-solid fa-home"></i>
                        Data Management
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('data.siswa.index') }}" class="text-gray-700 hover:text-blue-600">Siswa</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-gray-500">Edit Rekomendasi</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Student Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Information</h3>
                <p class="text-sm text-gray-500 mt-1">Basic student information (read-only)</p>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ID Yayasan</label>
                        <div class="text-sm text-gray-900 font-mono">{{ $siswa->idyayasan }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <div class="text-sm text-gray-900">{{ $siswa->nama ?: '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <div class="text-sm text-gray-900">{{ $siswa->email }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <div class="text-sm text-gray-900">{{ $siswa->kelas->nama_kelas ?: '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                        <div class="text-sm">
                            @if ($siswa->status_pembayaran === 'Lunas')
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check-circle mr-1"></i>Lunas
                                </span>
                            @else
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fa-solid fa-times-circle mr-1"></i>Belum Lunas
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Rekomendasi Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Recommendation</h3>
                <p class="text-sm text-gray-500 mt-1">Update student recommendation status</p>
            </div>

            <form action="{{ route('data.siswa.update', $siswa) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="px-6 py-6 space-y-6">

                    <!-- Current Rekomendasi Status -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-info-circle text-blue-600 mr-2"></i>
                            <div>
                                <h4 class="text-sm font-medium text-blue-900">Current Recommendation Status</h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    <strong>
                                        @if ($siswa->rekomendasi === 'ya')
                                            <span class="text-green-700">Ya - Direkomendasikan</span>
                                        @else
                                            <span class="text-red-700">Tidak - Belum Direkomendasikan</span>
                                        @endif
                                    </strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Rekomendasi -->
                    <div>
                        <label for="rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">
                            Rekomendasi <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 space-y-2">
                            <label class="inline-flex items-center">
                                <input type="radio" name="rekomendasi" value="ya" class="form-radio text-green-600"
                                    {{ old('rekomendasi', $siswa->rekomendasi) === 'ya' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fa-solid fa-check-circle text-green-600 mr-1"></i>
                                    Ya - Rekomendasikan siswa ini
                                </span>
                            </label>
                            <br>
                            <label class="inline-flex items-center">
                                <input type="radio" name="rekomendasi" value="tidak" class="form-radio text-red-600"
                                    {{ old('rekomendasi', $siswa->rekomendasi) === 'tidak' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">
                                    <i class="fa-solid fa-times-circle text-red-600 mr-1"></i>
                                    Tidak - Jangan rekomendasikan siswa ini
                                </span>
                            </label>
                        </div>
                        @error('rekomendasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Rekomendasi -->
                    <div>
                        <label for="catatan_rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan Rekomendasi
                            <span class="text-gray-400 text-xs">(Opsional)</span>
                        </label>
                        <textarea name="catatan_rekomendasi" id="catatan_rekomendasi" rows="4"
                            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('catatan_rekomendasi') border-red-500 @enderror"
                            placeholder="Tulis catatan atau alasan rekomendasi...">{{ old('catatan_rekomendasi', $siswa->catatan_rekomendasi) }}</textarea>
                        @error('catatan_rekomendasi')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Maksimal 500 karakter</p>
                    </div>

                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Only recommendation can be updated. Other data is synced from API.
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('data.siswa.show', $siswa) }}"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center space-x-2">
                            <i class="fa-solid fa-times"></i>
                            <span>Cancel</span>
                        </a>

                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2">
                            <i class="fa-solid fa-save"></i>
                            <span>Update Rekomendasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
