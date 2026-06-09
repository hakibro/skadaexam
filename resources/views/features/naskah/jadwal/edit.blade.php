@extends('layouts.admin')

@section('title', 'Edit Jadwal Ujian')
@section('page-title', 'Edit Jadwal Ujian')
@section('page-description', $jadwal->judul)

@section('content')
    <div class="space-y-6">
        <form action="{{ route('naskah.jadwal.update', $jadwal->id) }}" method="POST" id="editJadwalForm">
            @csrf
            @method('PUT')
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 sm:p-6 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Edit Jadwal Ujian</h3>
                    <p class="mt-1 text-sm text-gray-600">Ubah informasi jadwal ujian yang sudah dibuat.</p>
                </div>

                <div class="p-4 sm:p-6 space-y-6">
                    @if ($errors->any())
                        <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            <div class="font-medium">Jadwal belum tersimpan.</div>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Status Section - Added for better visibility -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-circle-info text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Status saat ini:
                                    <span class="font-bold">
                                        @if ($jadwal->status == 'draft')
                                            <span class="text-gray-800">Draft</span>
                                        @elseif($jadwal->status == 'aktif')
                                            <span class="text-green-800">Aktif</span>
                                        @elseif($jadwal->status == 'selesai')
                                            <span class="text-blue-800">Selesai</span>
                                        @elseif($jadwal->status == 'dibatalkan')
                                            <span class="text-red-800">Dibatalkan</span>
                                        @else
                                            <span>{{ $jadwal->status }}</span>
                                        @endif
                                    </span>
                                </p>
                                <p class="text-xs text-yellow-700 mt-1">
                                    Status jadwal dapat diubah di halaman detail jadwal ujian.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700">Judul Ujian <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="judul" id="judul" required
                                value="{{ old('judul', $jadwal->judul) }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('judul') border-red-500 @enderror"
                                placeholder="Contoh: Ulangan Tengah Semester Matematika">
                            @error('judul')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <span class="block text-sm font-medium text-gray-700">Paket Ujian</span>
                            <div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900">
                                {{ $jadwal->paketUjian->nama ?? 'Belum ada paket' }}
                            </div>
                        </div>

                        <div>
                            <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran <span
                                    class="text-red-500">*</span></label>
                            <select name="mapel_id" id="mapel_id" required
                                class="mt-1 form-select block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('mapel_id') border-red-500 @enderror">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ old('mapel_id', $jadwal->mapel_id) == $mapel->id ? 'selected' : '' }}>
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
                                    <option value="{{ $bankSoal->id }}"
                                        data-soal-count="{{ $bankSoal->soals_count ?? $bankSoal->soals->count() }}"
                                        {{ old('bank_soal_id', $jadwal->bank_soal_id) == $bankSoal->id ? 'selected' : '' }}>
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
                            <label class="block text-sm font-medium text-gray-700">Jumlah Soal</label>
                            <div class="mt-1 flex items-center gap-2">
                                <div class="form-input block w-full rounded-md shadow-sm bg-gray-100 border-gray-200 px-3 py-2 text-gray-700 font-semibold"
                                    id="jumlah_soal_display">
                                    {{ $jadwal->jumlah_soal }} soal
                                </div>
                                <input type="hidden" id="jumlah_soal"
                                    value="{{ old('jumlah_soal', $jadwal->jumlah_soal) }}">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-sync-alt text-blue-500 mr-1"></i>
                                Otomatis tersinkronisasi dari bank soal yang dipilih
                            </p>
                        </div>

                        <div>
                            <label for="durasi_menit" class="block text-sm font-medium text-gray-700">Durasi (menit) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="durasi_menit" id="durasi_menit" required min="1"
                                value="{{ old('durasi_menit', $jadwal->durasi_menit) }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('durasi_menit') border-red-500 @enderror"
                                placeholder="Durasi ujian dalam menit">
                            @error('durasi_menit')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal Ujian (Default)
                                <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal" id="tanggal" required
                                value="{{ old('tanggal', $jadwal->tanggal ? $jadwal->tanggal->format('Y-m-d') : '') }}"
                                class="mt-1 form-input block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('tanggal') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Tanggal default ini dapat diubah per sesi ruangan</p>
                            @error('tanggal')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3"
                                class="mt-1 form-textarea block w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('deskripsi') border-red-500 @enderror"
                                placeholder="Deskripsi atau petunjuk tambahan untuk ujian">{{ old('deskripsi', $jadwal->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="bg-blue-50 p-4 rounded-md border border-blue-100">
                        <h4 class="text-base font-medium text-blue-900 mb-1">Kelas Target Otomatis</h4>
                        <p class="text-sm text-blue-800">
                            Kelas peserta ditentukan otomatis oleh sistem berdasarkan tingkat dan jurusan mata pelajaran
                            yang dipilih.
                        </p>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="text-base font-medium text-gray-800 mb-3">Pengaturan Ujian</h4>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="acak_soal" id="acak_soal" value="1"
                                    {{ old('acak_soal', $jadwal->acak_soal) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acak_soal" class="ml-2 block text-sm text-gray-700">
                                    Acak Soal
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="acak_jawaban" id="acak_jawaban" value="1"
                                    {{ old('acak_jawaban', $jadwal->acak_jawaban) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="acak_jawaban" class="ml-2 block text-sm text-gray-700">
                                    Acak Jawaban
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="tampilkan_hasil" id="tampilkan_hasil" value="1"
                                    {{ old('tampilkan_hasil', $jadwal->tampilkan_hasil) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="tampilkan_hasil" class="ml-2 block text-sm text-gray-700">
                                    Tampilkan Hasil Setelah Selesai
                                </label>
                            </div>

                            <div class="flex items-center">
                                <input type="checkbox" name="aktifkan_auto_logout" id="aktifkan_auto_logout"
                                    value="1"
                                    {{ old('aktifkan_auto_logout', $jadwal->aktifkan_auto_logout) ? 'checked' : '' }}
                                    class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="aktifkan_auto_logout" class="ml-2 block text-sm text-gray-700">
                                    Aktifkan Auto Logout
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 border-t">
                    <a href="{{ route('naskah.jadwal.show', $jadwal->id) }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        <i class="fa-solid fa-times mr-2"></i> Batal
                    </a>
                    <button type="submit" id="submitButton"
                        class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <i class="fa-solid fa-save mr-2"></i> Update Jadwal Ujian
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bankSoalSelect = document.getElementById('bank_soal_id');
                const jumlahSoalDisplay = document.getElementById('jumlah_soal_display');
                const jumlahSoalHidden = document.getElementById('jumlah_soal');
                const editJadwalForm = document.getElementById('editJadwalForm');

                // Update jumlah_soal display when bank soal changes
                function updateJumlahSoal() {
                    const selectedOption = bankSoalSelect.options[bankSoalSelect.selectedIndex];
                    if (selectedOption && selectedOption.dataset.soalCount) {
                        const soalCount = parseInt(selectedOption.dataset.soalCount);
                        jumlahSoalDisplay.textContent = soalCount + ' soal';
                        jumlahSoalHidden.value = soalCount;
                    }
                }

                bankSoalSelect.addEventListener('change', updateJumlahSoal);

                // Confirm form submission if there are existing sesi
                @if ($jadwal->sesiRuangan && $jadwal->sesiRuangan->count() > 0)
                    editJadwalForm.addEventListener('submit', function(e) {
                        const confirmation = confirm(
                            "Jadwal ini memiliki {{ $jadwal->sesiRuangan->count() }} sesi terkait. Perubahan jadwal akan mempengaruhi semua sesi tersebut. Lanjutkan?"
                        );
                        if (!confirmation) {
                            e.preventDefault();
                        }
                    });
                @endif
            });
        </script>
    @endpush
@endsection
