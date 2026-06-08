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
                    <input type="hidden" name="tahun_ajaran_id" value="{{ $activeYear->id }}">

                    <!-- Status Section - Added for better visibility -->
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-circle-info text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Tahun Ajaran:</strong> {{ $activeYear->nama }}.
                                    Jadwal ujian baru akan dibuat dengan status <span class="font-bold">Aktif</span>.
                                </p>
                                <p class="text-xs text-blue-700 mt-1">
                                    Status jadwal dapat diubah di halaman detail jadwal ujian setelah pembuatan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="paket_ujian_id" class="block text-sm font-medium text-gray-700">Paket Ujian <span
                                    class="text-red-500">*</span></label>
                            <select name="paket_ujian_id" id="paket_ujian_id" required
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('paket_ujian_id') border-red-500 @enderror">
                                @foreach ($paketUjians as $paket)
                                    <option value="{{ $paket->id }}"
                                        {{ (string) old('paket_ujian_id', $paketUjianId) === (string) $paket->id ? 'selected' : '' }}>
                                        {{ $paket->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('paket_ujian_id')
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
                                    <option value="{{ $mapel->id }}" data-mapel-name="{{ $mapel->nama_mapel }}"
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
                            <label for="judul" class="block text-sm font-medium text-gray-700">Judul Ujian <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="judul" id="judul" required
                                value="{{ old('judul') }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('judul') border-red-500 @enderror"
                                placeholder="Contoh: Matematika atau Susulan - Matematika">
                            @error('judul')
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

                        <input type="hidden" name="jumlah_soal" id="jumlah_soal" value="{{ old('jumlah_soal') }}">

                        <div>
                            <label for="durasi_menit" class="block text-sm font-medium text-gray-700">Durasi (menit) <span
                                    class="text-red-500">*</span></label>
                            @php
                                $oldDurasi = old('durasi_menit');
                                $oldPreset = old('durasi_preset', in_array((string) $oldDurasi, ['25', '30', '45']) ? $oldDurasi : ($oldDurasi ? 'manual' : '30'));
                            @endphp
                            <select name="durasi_preset" id="durasi_preset" required
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('durasi_preset') border-red-500 @enderror">
                                <option value="25" {{ (string) $oldPreset === '25' ? 'selected' : '' }}>25 menit
                                </option>
                                <option value="30" {{ (string) $oldPreset === '30' ? 'selected' : '' }}>30 menit
                                </option>
                                <option value="45" {{ (string) $oldPreset === '45' ? 'selected' : '' }}>45 menit
                                </option>
                                <option value="manual" {{ $oldPreset === 'manual' ? 'selected' : '' }}>Isi manual
                                </option>
                            </select>
                            <div id="durasi_manual_container" class="mt-2 hidden">
                                <input type="number" name="durasi_manual" id="durasi_manual" min="1"
                                    value="{{ old('durasi_manual', $oldPreset === 'manual' ? $oldDurasi : '') }}"
                                    class="form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('durasi_manual') border-red-500 @enderror"
                                    placeholder="Masukkan durasi manual">
                            </div>
                            <input type="hidden" name="durasi_menit" id="durasi_menit" value="{{ old('durasi_menit') }}">
                            @error('durasi_preset')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            @error('durasi_manual')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                            @error('durasi_menit')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal Ujian
                                <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" required value="{{ old('tanggal') }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-500 @enderror">
                            @error('tanggal')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi
                                (optional)</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3"
                                class="mt-1 form-textarea block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('deskripsi') border-red-500 @enderror"
                                placeholder="Deskripsi atau petunjuk tambahan untuk ujian">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Kelas Target Section -->
                    {{-- <div class=" bg-white p-4 rounded-md border border-gray-200 mb-6">
                        <h4 class="text-base font-medium text-gray-800 mb-3">Kelas Target</h4>
                        <p class="text-sm text-gray-600 mb-4">Pilih kelas yang akan mengikuti ujian ini. Jika tidak ada
                            kelas yang dipilih, sistem akan secara otomatis memilih kelas berdasarkan tingkat dan jurusan
                            dari mata pelajaran.</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($kelasList as $kelas)
                                <div class="flex items-start">
                                    <input type="checkbox" name="kelas_target[]" id="kelas_{{ $kelas->id }}"
                                        value="{{ $kelas->id }}"
                                        {{ in_array($kelas->id, old('kelas_target', [])) ? 'checked' : '' }}
                                        class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="kelas_{{ $kelas->id }}" class="ml-2 block text-sm text-gray-700">
                                        {{ $kelas->nama_kelas }}
                                        <span class="text-xs text-gray-500">({{ $kelas->tingkat }}
                                            {{ $kelas->jurusan }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div> --}}

                    <!-- Pengaturan Ujian Section -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-base font-medium text-gray-800 mb-3">Pengaturan Ujian</h4>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
                                    Tampilkan Hasil
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="aktifkan_auto_logout" id="aktifkan_auto_logout"
                                    value="1" {{ old('aktifkan_auto_logout', true) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="aktifkan_auto_logout" class="ml-2 block text-sm text-gray-700">
                                    Aktifkan Auto Logout
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

@endsection

@section('scripts')
    <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Filter bank soal based on selected mapel
                const mapelSelect = document.getElementById('mapel_id');
                const judulInput = document.getElementById('judul');
                const bankSoalSelect = document.getElementById('bank_soal_id');
                const jumlahSoalInput = document.getElementById('jumlah_soal');
                const durasiPresetSelect = document.getElementById('durasi_preset');
                const durasiManualContainer = document.getElementById('durasi_manual_container');
                const durasiManualInput = document.getElementById('durasi_manual');
                const durasiMenitInput = document.getElementById('durasi_menit');
                const jadwalForm = document.getElementById('jadwalForm');
                const submitButton = document.getElementById('submitButton');

                // Store original bank soal options
                const originalOptions = Array.from(bankSoalSelect.options);

                let lastAutoJudul = '';

                function updateJudulFromMapel() {
                    const selectedOption = mapelSelect.options[mapelSelect.selectedIndex];
                    const mapelName = selectedOption?.dataset.mapelName || '';

                    if (!judulInput.value || judulInput.value === lastAutoJudul) {
                        judulInput.value = mapelName;
                    }

                    lastAutoJudul = mapelName;
                }

                function updateJumlahSoalFromBankSoal() {
                    const selectedOption = bankSoalSelect.options[bankSoalSelect.selectedIndex];

                    if (selectedOption && selectedOption.dataset.soalCount) {
                        const soalCount = parseInt(selectedOption.dataset.soalCount);
                        jumlahSoalInput.value = Number.isNaN(soalCount) ? '' : soalCount;
                    } else {
                        jumlahSoalInput.value = '';
                    }
                }

                function syncDurasiInput() {
                    const preset = durasiPresetSelect.value;

                    if (preset === 'manual') {
                        durasiManualContainer.classList.remove('hidden');
                        durasiManualInput.required = true;
                        durasiMenitInput.value = durasiManualInput.value;
                    } else {
                        durasiManualContainer.classList.add('hidden');
                        durasiManualInput.required = false;
                        durasiManualInput.value = '';
                        durasiMenitInput.value = preset;
                    }
                }

                durasiPresetSelect.addEventListener('change', syncDurasiInput);
                durasiManualInput.addEventListener('input', syncDurasiInput);

                // Filter bank soal when mapel changes
                mapelSelect.addEventListener('change', function() {
                    const selectedMapelId = this.value;
                    updateJudulFromMapel();

                    // Reset bank soal options
                    bankSoalSelect.innerHTML = '<option value="">-- Pilih Bank Soal --</option>';
                    bankSoalSelect.disabled = !selectedMapelId;

                    if (selectedMapelId) {
                        // Filter bank soal options for the selected mapel
                        originalOptions.forEach(option => {
                            if (option.value !== '' && option.dataset.mapelId == selectedMapelId) {
                                bankSoalSelect.appendChild(option.cloneNode(true));
                            }
                        });
                        const firstBankSoalWithQuestions = Array.from(bankSoalSelect.options)
                            .findIndex(option => parseInt(option.dataset.soalCount || '0') > 0);
                        if (firstBankSoalWithQuestions > 0) {
                            bankSoalSelect.selectedIndex = firstBankSoalWithQuestions;
                        } else if (bankSoalSelect.options.length > 1) {
                            bankSoalSelect.selectedIndex = 1;
                        }
                    } else {
                        // Show all options if no mapel selected
                        originalOptions.forEach(option => {
                            if (option.value !== '') {
                                bankSoalSelect.appendChild(option.cloneNode(true));
                            }
                        });
                    }

                    updateJumlahSoalFromBankSoal();
                    bankSoalSelect.dispatchEvent(new Event('change'));
                });

                // Validate jumlah soal when bank soal changes
                bankSoalSelect.addEventListener('change', function() {
                    updateJumlahSoalFromBankSoal();
                });

                updateJudulFromMapel();
                syncDurasiInput();
                bankSoalSelect.disabled = !mapelSelect.value;
                if (mapelSelect.value) {
                    mapelSelect.dispatchEvent(new Event('change'));
                    const oldBankSoalId = @json(old('bank_soal_id'));
                    if (oldBankSoalId) {
                        bankSoalSelect.value = oldBankSoalId;
                        updateJumlahSoalFromBankSoal();
                    }
                } else {
                    updateJumlahSoalFromBankSoal();
                }

                // Form validation
                jadwalForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    syncDurasiInput();

                    // Check if bank soal is selected
                    if (!bankSoalSelect.value) {
                        alert('Silahkan pilih bank soal terlebih dahulu');
                        isValid = false;
                    }

                    if (!jumlahSoalInput.value || parseInt(jumlahSoalInput.value) < 1) {
                        alert('Bank soal yang dipilih belum memiliki soal');
                        isValid = false;
                    }

                    if (durasiPresetSelect.value === 'manual' && !durasiManualInput.value) {
                        alert('Silahkan isi durasi manual');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                    }
                });
            });
    </script>
@endsection
