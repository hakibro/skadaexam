@extends('layouts.admin')

@section('title', 'Tambah Jadwal Ujian')
@section('page-title', 'Tambah Jadwal Ujian')
@section('page-description', 'Buat jadwal ujian baru')

@section('content')
    <div class="space-y-6">
        <form action="{{ route('naskah.jadwal.store') }}" method="POST" id="jadwalForm">
            @csrf
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Jadwal Ujian</h3>
                    <p class="mt-1 text-sm text-gray-600">Lengkapi informasi jadwal ujian yang akan dibuat.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    <!-- Status Section - Added for better visibility -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-circle-info text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Informasi:</strong> Jadwal ujian baru akan dibuat dengan status <span
                                        class="font-bold">Draft</span>.
                                </p>
                                <p class="text-xs text-blue-700 mt-1">
                                    Status jadwal dapat diubah di halaman detail jadwal ujian setelah pembuatan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700">Judul Ujian <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="judul" id="judul" required value="{{ old('judul') }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('judul') border-red-500 @enderror"
                                placeholder="Contoh: Ulangan Tengah Semester Matematika">
                            @error('judul')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="jenis_ujian" class="block text-sm font-medium text-gray-700">Jenis Ujian <span
                                    class="text-red-500">*</span></label>
                            <select name="jenis_ujian" id="jenis_ujian" required
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('jenis_ujian') border-red-500 @enderror">
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
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('mapel_id') border-red-500 @enderror">
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
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('bank_soal_id') border-red-500 @enderror">
                                <option value="">-- Pilih Bank Soal --</option>
                                @foreach ($bankSoals as $bankSoal)
                                    <option value="{{ $bankSoal->id }}" data-mapel-id="{{ $bankSoal->mapel_id }}"
                                        data-soal-count="{{ $bankSoal->soals_count ?? $bankSoal->soals->count() }}"
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
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('jumlah_soal') border-red-500 @enderror"
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
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('durasi_menit') border-red-500 @enderror"
                                placeholder="Durasi ujian dalam menit">
                            @error('durasi_menit')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal & Waktu Ujian
                                <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="tanggal" id="tanggal" required
                                value="{{ old('tanggal') }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-500 @enderror">
                            @error('tanggal')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3"
                                class="mt-1 form-textarea block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('deskripsi') border-red-500 @enderror"
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
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        <i class="fa-solid fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" id="submitButton"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <i class="fa-solid fa-save mr-2"></i> Simpan Jadwal Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Filter bank soal based on selected mapel
                const mapelSelect = document.getElementById('mapel_id');
                const bankSoalSelect = document.getElementById('bank_soal_id');
                const jumlahSoalInput = document.getElementById('jumlah_soal');
                const jadwalForm = document.getElementById('jadwalForm');
                const submitButton = document.getElementById('submitButton');

                // Store original bank soal options
                const originalOptions = Array.from(bankSoalSelect.options);

                // Filter bank soal when mapel changes
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

                // Validate jumlah soal when bank soal changes
                bankSoalSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.dataset.soalCount) {
                        const maxSoal = parseInt(selectedOption.dataset.soalCount);
                        jumlahSoalInput.max = maxSoal;

                        // Update placeholder to show max available
                        jumlahSoalInput.placeholder = `Maksimal ${maxSoal} soal`;

                        // If current value exceeds max, reset to max
                        if (parseInt(jumlahSoalInput.value) > maxSoal) {
                            jumlahSoalInput.value = maxSoal;
                        }
                    }
                });

                // Form validation
                jadwalForm.addEventListener('submit', function(e) {
                    let isValid = true;

                    // Check if bank soal is selected
                    if (!bankSoalSelect.value) {
                        alert('Silahkan pilih bank soal terlebih dahulu');
                        isValid = false;
                    }

                    // Validate jumlah soal against bank soal
                    if (bankSoalSelect.value) {
                        const selectedOption = bankSoalSelect.options[bankSoalSelect.selectedIndex];
                        const maxSoal = parseInt(selectedOption.dataset.soalCount);
                        const requestedSoal = parseInt(jumlahSoalInput.value);

                        if (requestedSoal > maxSoal) {
                            alert(`Jumlah soal tidak boleh melebihi ${maxSoal} (jumlah soal dalam bank soal)`);
                            isValid = false;
                        }
                    }

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    @endpush
@endsection
