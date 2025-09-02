@extends('layouts.admin')

@section('title', 'Tambah Sesi Ujian')
@section('page-title', 'Tambah Sesi Ujian')
@section('page-description', 'Jadwal: ' . $jadwal->nama_ujian)

@section('content')
    <div class="space-y-6">
        <form action="{{ route('naskah.sesi.store', $jadwal->id) }}" method="POST">
            @csrf
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Form Sesi Ujian</h3>
                    <p class="mt-1 text-sm text-gray-600">Tambahkan sesi ujian untuk jadwal "{{ $jadwal->nama_ujian }}" pada
                        tanggal {{ $jadwal->tanggal_ujian->format('d M Y') }}.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_sesi" class="block text-sm font-medium text-gray-700">Nama Sesi <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_sesi" id="nama_sesi" required value="{{ old('nama_sesi') }}"
                                class="mt-1 form-input block w-full @error('nama_sesi') border-red-500 @enderror"
                                placeholder="Contoh: Sesi Pagi">
                            @error('nama_sesi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_sesi" class="block text-sm font-medium text-gray-700">Jenis Sesi <span
                                    class="text-red-500">*</span></label>
                            <select name="jenis_sesi" id="jenis_sesi" required
                                class="mt-1 form-select block w-full @error('jenis_sesi') border-red-500 @enderror">
                                <option value="reguler" {{ old('jenis_sesi') == 'reguler' ? 'selected' : '' }}>Reguler
                                </option>
                                <option value="susulan" {{ old('jenis_sesi') == 'susulan' ? 'selected' : '' }}>Susulan
                                </option>
                            </select>
                            @error('jenis_sesi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai <span
                                        class="text-red-500">*</span></label>
                                <input type="time" name="waktu_mulai" id="waktu_mulai" required
                                    value="{{ old('waktu_mulai', $jadwal->waktu_mulai->format('H:i')) }}"
                                    class="mt-1 form-input block w-full @error('waktu_mulai') border-red-500 @enderror">
                                @error('waktu_mulai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai
                                    <span class="text-red-500">*</span></label>
                                <input type="time" name="waktu_selesai" id="waktu_selesai" required
                                    value="{{ old('waktu_selesai', $jadwal->waktu_selesai->format('H:i')) }}"
                                    class="mt-1 form-input block w-full @error('waktu_selesai') border-red-500 @enderror">
                                @error('waktu_selesai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="kapasitas_maksimal" class="block text-sm font-medium text-gray-700">Kapasitas
                                Maksimal <span class="text-red-500">*</span></label>
                            <input type="number" name="kapasitas_maksimal" id="kapasitas_maksimal" required min="1"
                                value="{{ old('kapasitas_maksimal', 40) }}"
                                class="mt-1 form-input block w-full @error('kapasitas_maksimal') border-red-500 @enderror"
                                placeholder="Kapasitas maksimal peserta">
                            @error('kapasitas_maksimal')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ruangan_id" class="block text-sm font-medium text-gray-700">Ruangan</label>
                            <select name="ruangan_id" id="ruangan_id"
                                class="mt-1 form-select block w-full @error('ruangan_id') border-red-500 @enderror">
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach ($ruangans as $ruangan)
                                    <option value="{{ $ruangan->id }}"
                                        {{ old('ruangan_id') == $ruangan->id ? 'selected' : '' }}>
                                        {{ $ruangan->nama_ruangan }} (Kapasitas: {{ $ruangan->kapasitas }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ruangan_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="pengawas_id" class="block text-sm font-medium text-gray-700">Pengawas</label>
                            <select name="pengawas_id" id="pengawas_id"
                                class="mt-1 form-select block w-full @error('pengawas_id') border-red-500 @enderror">
                                <option value="">-- Pilih Pengawas --</option>
                                @foreach ($pengawas as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{ old('pengawas_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pengawas_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t">
                    <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </a>
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Tambah Sesi Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
