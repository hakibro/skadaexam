@extends('layouts.admin')

@section('title', 'Buat Berita Acara Ujian')
@section('page-title', 'Buat Berita Acara Ujian')
@section('page-description', 'Buat laporan pelaksanaan ujian')

@section('content')
    <div>
        <div class="mb-6">
            <a href="{{ route('pengawas.berita-acara.show', $sesiRuangan->id) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Berita Acara
            </a>
        </div>

        <form action="{{ route('pengawas.berita-acara.store', $sesiRuangan->id) }}" method="POST">
            @csrf

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-2xl font-bold text-green-700">
                        <i class="fa-solid fa-clipboard-list mr-2"></i>
                        Buat Berita Acara Ujian
                    </h2>
                    <p class="text-gray-600 mt-1">Buat laporan pelaksanaan ujian untuk sesi ini</p>
                </div>

                <!-- Informasi Ujian -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-3">Informasi Ujian</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <table class="w-full">
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Mata Pelajaran</td>
                                    <td class="py-1 font-bold">
                                        @php
                                            $jadwalUjian = $sesiRuangan->jadwalUjians->first();
                                            $mapel = $jadwalUjian
                                                ? ($jadwalUjian->mapel
                                                    ? $jadwalUjian->mapel->nama
                                                    : 'Tidak ada mapel')
                                                : 'Tidak ada jadwal';
                                        @endphp
                                        {{ $mapel }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Ruangan</td>
                                    <td class="py-1 font-bold">
                                        {{ $sesiRuangan->ruangan ? $sesiRuangan->ruangan->nama_ruangan : 'Tidak ada ruangan' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Sesi</td>
                                    <td class="py-1 font-bold">{{ $sesiRuangan->nama_sesi }}</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <table class="w-full">
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Waktu</td>
                                    <td class="py-1 font-bold">{{ $sesiRuangan->waktu_mulai }} -
                                        {{ $sesiRuangan->waktu_selesai }}</td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Tanggal</td>
                                    <td class="py-1 font-bold">
                                        {{ $jadwalUjian ? $jadwalUjian->tanggal->format('d M Y') : 'Tidak ada jadwal' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-1 text-gray-600 font-medium">Status Sesi</td>
                                    <td class="py-1">
                                        <span
                                            class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sesiRuangan->status_badge_class }}">
                                            {{ $sesiRuangan->status_label['text'] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-6">
                    <!-- Catatan Pembukaan -->
                    <div>
                        <label for="catatan_pembukaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Pembukaan <span class="text-gray-500">(opsional)</span>
                        </label>
                        <textarea id="catatan_pembukaan" name="catatan_pembukaan" rows="3"
                            class="block w-full rounded-md @error('catatan_pembukaan') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Catatan terkait pembukaan ujian, seperti persiapan ruangan, briefing siswa, dll.">{{ old('catatan_pembukaan') }}</textarea>
                        @error('catatan_pembukaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Pelaksanaan -->
                    <div>
                        <label for="catatan_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Pelaksanaan <span class="text-gray-500">(opsional)</span>
                        </label>
                        <textarea id="catatan_pelaksanaan" name="catatan_pelaksanaan" rows="3"
                            class="block w-full rounded-md @error('catatan_pelaksanaan') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Catatan terkait pelaksanaan ujian, seperti kendala teknis, kejadian khusus, dll.">{{ old('catatan_pelaksanaan') }}</textarea>
                        @error('catatan_pelaksanaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Catatan Penutupan -->
                    <div>
                        <label for="catatan_penutupan" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Penutupan <span class="text-gray-500">(opsional)</span>
                        </label>
                        <textarea id="catatan_penutupan" name="catatan_penutupan" rows="3"
                            class="block w-full rounded-md @error('catatan_penutupan') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            placeholder="Catatan terkait penutupan ujian, seperti saran perbaikan, dll.">{{ old('catatan_penutupan') }}</textarea>
                        @error('catatan_penutupan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Pelaksanaan -->
                    <div>
                        <label for="status_pelaksanaan" class="block text-sm font-medium text-gray-700 mb-1">
                            Status Pelaksanaan <span class="text-red-500">*</span>
                        </label>
                        <select id="status_pelaksanaan" name="status_pelaksanaan" required
                            class="block w-full rounded-md @error('status_pelaksanaan') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="" disabled selected>-- Pilih Status Pelaksanaan --</option>
                            <option value="selesai_normal"
                                {{ old('status_pelaksanaan') == 'selesai_normal' ? 'selected' : '' }}>Selesai Normal
                            </option>
                            <option value="selesai_terganggu"
                                {{ old('status_pelaksanaan') == 'selesai_terganggu' ? 'selected' : '' }}>Selesai Terganggu
                            </option>
                            <option value="dibatalkan" {{ old('status_pelaksanaan') == 'dibatalkan' ? 'selected' : '' }}>
                                Dibatalkan</option>
                        </select>
                        @error('status_pelaksanaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statistik Kehadiran -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Statistik Kehadiran</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Total Siswa -->
                            <div>
                                <label for="jumlah_peserta_terdaftar" class="block text-sm font-medium text-gray-700 mb-1">
                                    Jumlah Terdaftar <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="jumlah_peserta_terdaftar" name="jumlah_peserta_terdaftar"
                                    value="{{ old('jumlah_peserta_terdaftar', $totalStudents) }}" min="0" required
                                    class="block w-full rounded-md @error('jumlah_peserta_terdaftar') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('jumlah_peserta_terdaftar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Siswa Hadir -->
                            <div>
                                <label for="jumlah_peserta_hadir" class="block text-sm font-medium text-gray-700 mb-1">
                                    Jumlah Hadir <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="jumlah_peserta_hadir" name="jumlah_peserta_hadir"
                                    value="{{ old('jumlah_peserta_hadir', $presentStudents) }}" min="0" required
                                    class="block w-full rounded-md @error('jumlah_peserta_hadir') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('jumlah_peserta_hadir')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Siswa Tidak Hadir -->
                            <div>
                                <label for="jumlah_peserta_tidak_hadir"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    Jumlah Tidak Hadir <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="jumlah_peserta_tidak_hadir" name="jumlah_peserta_tidak_hadir"
                                    value="{{ old('jumlah_peserta_tidak_hadir', $absentStudents) }}" min="0"
                                    required
                                    class="block w-full rounded-md @error('jumlah_peserta_tidak_hadir') border-red-300 @else border-gray-300 @enderror shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                @error('jumlah_peserta_tidak_hadir')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- <!-- Finalisasi -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_final" name="is_final" type="checkbox" value="1"
                                    {{ old('is_final') ? 'checked' : '' }}
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_final" class="font-medium text-gray-700">Finalisasi Berita Acara</label>
                                <p class="text-gray-500">Berita acara yang sudah difinalisasi tidak dapat diubah lagi.</p>
                            </div>
                        </div>
                    </div> --}}

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6 mt-6">
                        <a href="{{ route('pengawas.berita-acara.show', $sesiRuangan->id) }}"
                            class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition">
                            Simpan Berita Acara
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation for statistics fields to ensure consistency
            const totalField = document.getElementById('jumlah_peserta_terdaftar');
            const presentField = document.getElementById('jumlah_peserta_hadir');
            const absentField = document.getElementById('jumlah_peserta_tidak_hadir');

            // Function to update fields on change
            function updateFields() {
                // Ensure all values are non-negative
                const total = Math.max(0, parseInt(totalField.value) || 0);
                const present = Math.max(0, parseInt(presentField.value) || 0);
                const absent = Math.max(0, parseInt(absentField.value) || 0);

                // Logic to ensure consistency
                if (this === totalField) {
                    // If total changes, adjust absent to maintain consistency
                    presentField.value = Math.min(present, total);
                    absentField.value = total - presentField.value;
                } else if (this === presentField) {
                    // If present changes, adjust total and absent
                    presentField.value = Math.min(present, total);
                    absentField.value = total - presentField.value;
                } else if (this === absentField) {
                    // If absent changes, adjust total and present
                    absentField.value = Math.min(absent, total);
                    presentField.value = total - absentField.value;
                }
            }

            // Add change event listeners
            totalField.addEventListener('change', updateFields);
            presentField.addEventListener('change', updateFields);
            absentField.addEventListener('change', updateFields);

            // Status pelaksanaan help text
            const statusSelect = document.getElementById('status_pelaksanaan');
            statusSelect.addEventListener('change', function() {
                if (this.value === 'selesai_terganggu') {
                    // Add help text for selesai_terganggu
                    const helpText = document.createElement('p');
                    helpText.id = 'status-help-text';
                    helpText.className = 'mt-1 text-sm text-yellow-600';
                    helpText.innerHTML = 'Harap jelaskan gangguan yang terjadi pada catatan pelaksanaan.';

                    const existingHelp = document.getElementById('status-help-text');
                    if (!existingHelp) {
                        this.parentNode.appendChild(helpText);
                    }
                } else {
                    // Remove help text if exists
                    const helpText = document.getElementById('status-help-text');
                    if (helpText) {
                        helpText.remove();
                    }
                }
            });
        });
    </script>
@endsection
