@extends('layouts.admin')

@section('title', 'Tambah Ruangan')
@section('page-title', 'Tambah Ruangan Baru')
@section('page-description', 'Buat ruangan ujian baru')

@section('content')
    <div class="max-w-3xl mx-auto py-4">
        <form action="{{ route('ruangan.store') }}" method="POST" class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Informasi Ruangan</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Ruangan -->
                    <div>
                        <label for="kode_ruangan" class="block text-sm font-medium text-gray-700">
                            Kode Ruangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kode_ruangan" id="kode_ruangan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('kode_ruangan') }}" required>
                        @error('kode_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Ruangan -->
                    <div>
                        <label for="nama_ruangan" class="block text-sm font-medium text-gray-700">
                            Nama Ruangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_ruangan" id="nama_ruangan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('nama_ruangan') }}" required>
                        @error('nama_ruangan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kapasitas -->
                    <div>
                        <label for="kapasitas" class="block text-sm font-medium text-gray-700">
                            Kapasitas <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="kapasitas" id="kapasitas" min="1" max="1000"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('kapasitas') }}" required>
                        @error('kapasitas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label for="lokasi" class="block text-sm font-medium text-gray-700">Lokasi</label>
                        <input type="text" name="lokasi" id="lokasi"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('lokasi') }}" placeholder="Contoh: Lantai 2, Gedung A">
                        @error('lokasi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>



                <!-- Fasilitas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Fasilitas</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $facilities = [
                                'wifi' => 'WiFi',
                                'ac' => 'AC',
                                'proyektor' => 'Proyektor',
                                'komputer' => 'Komputer',
                                'papan_tulis' => 'Papan Tulis',
                                'cctv' => 'CCTV',
                                'printer' => 'Printer',
                                'speaker' => 'Speaker',
                            ];
                        @endphp

                        @foreach ($facilities as $key => $label)
                            <div class="flex items-center">
                                <input type="checkbox" name="fasilitas[]" value="{{ $key }}"
                                    id="facility_{{ $key }}"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    {{ in_array($key, old('fasilitas', [])) ? 'checked' : '' }}>
                                <label for="facility_{{ $key }}"
                                    class="ml-2 text-sm text-gray-700">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    @error('fasilitas')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="aktif" {{ old('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="perbaikan" {{ old('status') == 'perbaikan' ? 'selected' : '' }}>Perbaikan</option>
                        <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif
                        </option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Catatan tambahan tentang ruangan...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('ruangan.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Simpan Ruangan
                </button>
            </div>
        </form>
    </div>
@endsection
