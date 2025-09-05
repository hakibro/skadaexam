@extends('layouts.admin')

@section('title', 'Edit Template Sesi')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 ">
            Edit Template Sesi
        </h2>

        <!-- Breadcrumb -->
        <div class="flex text-sm text-gray-600 mb-4">
            <a href="{{ route('ruangan.template.index') }}" class="hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar template
            </a>
            <span class="mx-2">|</span>
            <a href="{{ route('ruangan.template.show', $template->id) }}" class="hover:underline">
                Lihat detail template
            </a>
        </div>

        <!-- Flash Messages -->
        @include('components.alert')

        <!-- Form -->
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md ">
            <form action="{{ route('ruangan.template.update', $template->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid gap-6 mb-6 md:grid-cols-2">
                    <!-- Nama Template -->
                    <div>
                        <label for="nama_sesi" class="block mb-2 text-sm font-medium text-gray-900 ">
                            Nama Template <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_sesi" name="nama_sesi"
                            value="{{ old('nama_sesi', $template->nama_sesi) }}" required
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
                            <option value="belum_mulai"
                                {{ old('status', $template->status) == 'belum_mulai' ? 'selected' : '' }}>Belum Mulai
                            </option>
                            <option value="berlangsung"
                                {{ old('status', $template->status) == 'berlangsung' ? 'selected' : '' }}>Berlangsung
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
                        <input type="time" id="waktu_mulai" name="waktu_mulai"
                            value="{{ old('waktu_mulai', $template->waktu_mulai) }}" required
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
                        <input type="time" id="waktu_selesai" name="waktu_selesai"
                            value="{{ old('waktu_selesai', $template->waktu_selesai) }}" required
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
                    <input type="text" id="deskripsi" name="deskripsi"
                        value="{{ old('deskripsi', $template->deskripsi) }}"
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
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">{{ old('keterangan', $template->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 :ring-blue-600  focus:ring-2  ">
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-900 ">
                            Aktif
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Template tidak aktif tidak akan muncul di daftar pilihan saat membuat sesi baru.
                    </p>
                </div>

                <!-- Update Existing Sessions -->
                <div class="mb-6 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <div class="flex items-center">
                        <input id="update_sessions" name="update_sessions" type="checkbox" value="1"
                            {{ old('update_sessions') ? 'checked' : '' }}
                            class="w-4 h-4 text-yellow-600 bg-yellow-100 border-yellow-300 rounded focus:ring-yellow-500 :ring-yellow-600  focus:ring-2  ">
                        <label for="update_sessions" class="ml-2 text-sm font-medium text-yellow-900">
                            Perbarui semua sesi yang menggunakan template ini
                        </label>
                    </div>
                    <p class="text-xs text-yellow-700 mt-1">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Perhatian: Tindakan ini akan memperbarui semua sesi yang belum selesai dan menggunakan template ini.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
