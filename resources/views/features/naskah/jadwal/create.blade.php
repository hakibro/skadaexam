@extends('layouts.admin')

@section('title', 'Tambah Jadwal Ujian')
@section('page-title', 'Tambah Jadwal Ujian')
@section('page-description', 'Buat jadwal ujian baru')

@section('content')
    <div class="space-y-6">
        <form action="{{ route('naskah.jadwal.store') }}" method="POST">
            @csrf
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Jadwal Ujian</h3>
                    <p class="mt-1 text-sm text-gray-600">Lengkapi informasi jadwal ujian yang akan dibuat.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="nama_ujian" class="block text-sm font-medium text-gray-700">Nama Ujian <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="nama_ujian" id="nama_ujian" required value="{{ old('nama_ujian') }}"
                                class="mt-1 form-input block w-full @error('nama_ujian') border-red-500 @enderror"
                                placeholder="Contoh: Ulangan Tengah Semester Matematika">
                            @error('nama_ujian')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_ujian" class="block text-sm font-medium text-gray-700">Jenis Ujian <span
                                    class="text-red-500">*</span></label>
                            <select name="jenis_ujian" id="jenis_ujian" required
                                class="mt-1 form-select block w-full @error('jenis_ujian') border-red-500 @enderror">
                                <option value="reguler" {{ old('jenis_ujian') == 'reguler' ? 'selected' : '' }}>Reguler
                                </option>
                                <option value="susulan" {{ old('jenis_ujian') == 'susulan' ? 'selected' : '' }}>Susulan
                                </option>
                                <option value="remedial" {{ old('jenis_ujian') == 'remedial' ? 'selected' : '' }}>Remedial
                                </option>
                                <option value="tryout" {{ old('jenis_ujian') == 'tryout' ? 'selected' : '' }}>Try Out
                                </option>
                                <option value="penilaian_harian"
                                    {{ old('jenis_ujian') == 'penilaian_harian' ? 'selected' : '' }}>Penilaian Harian
                                </option>
                                <option value="uts" {{ old('jenis_ujian') == 'uts' ? 'selected' : '' }}>UTS</option>
                                <option value="uas" {{ old('jenis_ujian') == 'uas' ? 'selected' : '' }}>UAS</option>
                            </select>
                            @error('jenis_ujian')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran <span
                                    class="text-red-500">*</span></label>
                            <select name="mapel_id" id="mapel_id" required
                                class="mt-1 form-select block w-full @error('mapel_id') border-red-500 @enderror">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('mapel_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bank_soal_id" class="block text-sm font-medium text-gray-700">Bank Soal <span
                                    class="text-red-500">*</span></label>
                            <select name="bank_soal_id" id="bank_soal_id" required
                                class="mt-1 form-select block w-full @error('bank_soal_id') border-red-500 @enderror">
                                <option value="">-- Pilih Bank Soal --</option>
                                @foreach ($bankSoals as $bankSoal)
                                    <option value="{{ $bankSoal->id }}" data-mapel-id="{{ $bankSoal->mapel_id }}"
                                        {{ old('bank_soal_id') == $bankSoal->id ? 'selected' : '' }}>
                                        {{ $bankSoal->judul }} ({{ $bankSoal->soals_count ?? $bankSoal->soals->count() }}
                                        soal)
                                    </option>
                                @endforeach
                            </select>
                            @error('bank_soal_id')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jumlah_soal" class="block text-sm font-medium text-gray-700">Jumlah Soal <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_soal" id="jumlah_soal" required min="1"
                                value="{{ old('jumlah_soal') }}"
                                class="mt-1 form-input block w-full @error('jumlah_soal') border-red-500 @enderror"
                                placeholder="Jumlah soal yang ditampilkan">
                            @error('jumlah_soal')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500">Jumlah soal yang akan ditampilkan dalam ujian (harus <=
                                    jumlah soal dalam bank soal)</p>
                        </div>

                        <div>
                            <label for="durasi_menit" class="block text-sm font-medium text-gray-700">Durasi (menit) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="durasi_menit" id="durasi_menit" required min="1"
                                value="{{ old('durasi_menit') }}"
                                class="mt-1 form-input block w-full @error('durasi_menit') border-red-500 @enderror"
                                placeholder="Durasi ujian dalam menit">
                            @error('durasi_menit')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal_ujian" class="block text-sm font-medium text-gray-700">Tanggal Ujian <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_ujian" id="tanggal_ujian" required
                                value="{{ old('tanggal_ujian') }}"
                                class="mt-1 form-input block w-full @error('tanggal_ujian') border-red-500 @enderror">
                            @error('tanggal_ujian')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">Waktu Mulai <span
                                        class="text-red-500">*</span></label>
                                <input type="time" name="waktu_mulai" id="waktu_mulai" required
                                    value="{{ old('waktu_mulai') }}"
                                    class="mt-1 form-input block w-full @error('waktu_mulai') border-red-500 @enderror">
                                @error('waktu_mulai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">Waktu Selesai
                                    <span class="text-red-500">*</span></label>
                                <input type="time" name="waktu_selesai" id="waktu_selesai" required
                                    value="{{ old('waktu_selesai') }}"
                                    class="mt-1 form-input block w-full @error('waktu_selesai') border-red-500 @enderror">
                                @error('waktu_selesai')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3"
                                class="mt-1 form-textarea block w-full @error('deskripsi') border-red-500 @enderror"
                                placeholder="Deskripsi atau petunjuk tambahan untuk ujian">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-base font-medium text-gray-800 mb-3">Pengaturan Ujian</h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="acak_soal" id="acak_soal" value="1"
                                    {{ old('acak_soal') ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acak_soal" class="ml-2 block text-sm text-gray-700">
                                    Acak Soal
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="acak_jawaban" id="acak_jawaban" value="1"
                                    {{ old('acak_jawaban') ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acak_jawaban" class="ml-2 block text-sm text-gray-700">
                                    Acak Jawaban
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="tampilkan_hasil" id="tampilkan_hasil" value="1"
                                    {{ old('tampilkan_hasil') ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="tampilkan_hasil" class="ml-2 block text-sm text-gray-700">
                                    Tampilkan Hasil Setelah Selesai
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t">
                    <a href="{{ route('naskah.jadwal.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Batal
                    </a>
                    <button type="submit"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan Jadwal Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filter bank soal based on selected mapel
            const mapelSelect = document.getElementById('mapel_id');
            const bankSoalSelect = document.getElementById('bank_soal_id');
            const originalOptions = Array.from(bankSoalSelect.options);

            mapelSelect.addEventListener('change', function() {
                const selectedMapelId = this.value;

                // Reset bank soal options
                bankSoalSelect.innerHTML = '<option value="">-- Pilih Bank Soal --</option>';

                if (selectedMapelId) {
                    // Filter bank soal options for the selected mapel
                    originalOptions.forEach(option => {
                        if (option.dataset.mapelId == selectedMapelId || option.value === '') {
                            bankSoalSelect.appendChild(option.cloneNode(true));
                        }
                    });
                } else {
                    // Show all options if no mapel selected
                    originalOptions.forEach(option => {
                        bankSoalSelect.appendChild(option.cloneNode(true));
                    });
                }
            });
        });
    </script>
@endsection
