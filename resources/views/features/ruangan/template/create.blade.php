@extends('layouts.admin')

@section('title', 'Tambah Template Sesi')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 ">
            Buat Template Sesi Baru
        </h2>

        <!-- Breadcrumb -->
        <div class="flex text-sm text-gray-600 mb-4">
            <a href="{{ route('ruangan.template.index') }}" class="hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar template
            </a>
        </div>

        <!-- Flash Messages -->
        @include('components.alert')

        <!-- Form -->
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md ">
            <form action="{{ route('ruangan.template.store') }}" method="POST">
                @csrf

                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    <!-- Nama Template -->
                    <div>
                        <label for="nama_sesi" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Nama Template <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_sesi" name="nama_sesi" value="{{ old('nama_sesi') }}" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                        @error('nama_sesi')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Status Default <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5   ">
                            <option value="belum_mulai" {{ old('status') == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai
                            </option>
                            <option value="berlangsung" {{ old('status') == 'berlangsung' ? 'selected' : '' }}>Berlangsung
                            </option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    <!-- Waktu Mulai -->
                    <div>
                        <label for="waktu_mulai" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5   ">
                        @error('waktu_mulai')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label for="waktu_selesai" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Waktu Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}"
                            required
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5   ">
                        @error('waktu_selesai')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deskripsi -->
                <div class="mb-6">
                    <label for="deskripsi" class="block mb-2 text-sm font-medium text-gray-900 ">
                        Deskripsi
                    </label>
                    <input type="text" id="deskripsi" name="deskripsi" value="{{ old('deskripsi') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                    @error('deskripsi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="mb-6">
                    <label for="keterangan" class="block mb-2 text-sm font-medium text-gray-900 ">
                        Keterangan Tambahan
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="3"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <button type="submit"
                        class=" text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                        Simpan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const waktuMulaiInput = document.getElementById('waktu_mulai');
            const waktuSelesaiInput = document.getElementById('waktu_selesai');

            // Auto calculate end time based on start time (add 2 hours)
            waktuMulaiInput.addEventListener('change', function() {
                if (waktuMulaiInput.value && !waktuSelesaiInput.value) {
                    const startTime = new Date(`2000-01-01T${waktuMulaiInput.value}`);
                    startTime.setHours(startTime.getHours() + 2);
                    const hours = String(startTime.getHours()).padStart(2, '0');
                    const minutes = String(startTime.getMinutes()).padStart(2, '0');
                    waktuSelesaiInput.value = `${hours}:${minutes}`;
                }
            });
        });
    </script>
@endsection
