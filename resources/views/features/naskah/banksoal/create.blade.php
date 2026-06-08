@extends('layouts.admin')

@section('title', 'Tambah Bank Soal')
@section('page-title', 'Tambah Bank Soal Baru')
@section('page-description', 'Buat koleksi soal baru untuk ujian')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('naskah.banksoal.store') }}" method="POST"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Informasi Bank Soal</h2>

                <!-- Judul -->
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700">Judul Bank Soal <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="judul"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('judul') }}" required>
                    @error('judul')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Paket Ujian -->
                <div>
                    <label for="paket_ujian_id" class="block text-sm font-medium text-gray-700">Paket Ujian <span
                            class="text-red-500">*</span></label>
                    <select name="paket_ujian_id" id="paket_ujian_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="">Pilih Paket Ujian</option>
                        @foreach ($paketUjians as $paket)
                            <option value="{{ $paket->id }}" {{ old('paket_ujian_id') == $paket->id ? 'selected' : '' }}>
                                {{ $paket->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('paket_ujian_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mata Pelajaran -->
                    <div>
                        <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran <span
                                class="text-red-500">*</span></label>
                        @if (isset($selectedMapel) && $selectedMapel)
                            <input type="hidden" name="mapel_id" value="{{ $selectedMapel->id }}">
                            <div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900">
                                {{ $selectedMapel->nama_mapel }} ({{ $selectedMapel->kode_mapel }})
                            </div>
                        @else
                            <select name="mapel_id" id="mapel_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}" data-tingkat="{{ $mapel->tingkat }}"
                                        {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('mapel_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tingkat -->
                    <div>
                        <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat Kelas <span
                                class="text-red-500">*</span></label>
                        <input type="hidden" name="tingkat" id="tingkat"
                            value="{{ old('tingkat', isset($selectedMapel) && $selectedMapel ? $selectedMapel->tingkat : '') }}">
                        <div id="tingkat_display"
                            class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900">
                            {{ isset($selectedMapel) && $selectedMapel ? 'Kelas ' . $selectedMapel->tingkat : 'Otomatis dari mata pelajaran' }}
                        </div>
                        @error('tingkat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="status" value="aktif">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="jumlah_pilihan" class="block text-sm font-medium text-gray-700">Jumlah Pilihan
                            Jawaban</label>
                        <select name="jumlah_pilihan" id="jumlah_pilihan"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach ([2, 3, 4, 5] as $jumlah)
                                <option value="{{ $jumlah }}"
                                    {{ old('jumlah_pilihan', 5) == $jumlah ? 'selected' : '' }}>
                                    {{ $jumlah }} pilihan
                                </option>
                            @endforeach
                        </select>
                        @error('jumlah_pilihan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tipe_soal_default" class="block text-sm font-medium text-gray-700">Tipe Soal
                            Default</label>
                        <select name="tipe_soal_default" id="tipe_soal_default"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach (\App\Models\Soal::QUESTION_TYPES as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('tipe_soal_default', 'pilihan_ganda') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipe_soal_default')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('naskah.banksoal.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Bank Soal
                </button>
            </div>
        </form>
    </div>

    <script>
        const mapelSelect = document.getElementById('mapel_id');
        const tingkatInput = document.getElementById('tingkat');
        const tingkatDisplay = document.getElementById('tingkat_display');

        if (mapelSelect && tingkatInput && tingkatDisplay) {
            mapelSelect.addEventListener('change', function() {
                const tingkat = this.selectedOptions[0]?.dataset?.tingkat || '';
                tingkatInput.value = tingkat;
                tingkatDisplay.textContent = tingkat ? `Kelas ${tingkat}` : 'Otomatis dari mata pelajaran';
            });
        }
    </script>
@endsection
