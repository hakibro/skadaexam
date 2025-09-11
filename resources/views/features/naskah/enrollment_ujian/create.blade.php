@extends('layouts.admin')

@section('title', 'Tambah Enrollment Ujian')
@section('page-title', 'Tambah Enrollment Ujian')
@section('page-description', 'Mendaftarkan siswa pada ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fa-solid fa-user-plus mr-2"></i>Form Tambah Enrollment Ujian
                </h3>
            </div>
            <div class="p-6">
                <form action="{{ route('naskah.enrollment-ujian.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="jadwal_ujian_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Jadwal Ujian <span class="text-red-500">*</span>
                            </label>
                            <select name="jadwal_ujian_id" id="jadwal_ujian_id"
                                class="form-select w-full rounded-md shadow-sm @error('jadwal_ujian_id') border-red-500 @enderror"
                                required>
                                <option value="">Pilih Jadwal Ujian</option>
                                @foreach ($jadwalUjians as $jadwal)
                                    <option value="{{ $jadwal->id }}"
                                        {{ old('jadwal_ujian_id') == $jadwal->id ? 'selected' : '' }}>
                                        {{ $jadwal->judul }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jadwal_ujian_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sesi_ruangan_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Sesi Ujian <span class="text-red-500">*</span>
                            </label>
                            <select name="sesi_ruangan_id" id="sesi_ruangan_id"
                                class="form-select w-full rounded-md shadow-sm @error('sesi_ruangan_id') border-red-500 @enderror"
                                required disabled>
                                <option value="">Pilih Sesi Ujian</option>
                                @if (old('jadwal_ujian_id') && old('sesi_ruangan_id'))
                                    <option value="{{ old('sesi_ruangan_id') }}" selected>
                                        {{ App\Models\SesiRuangan::find(old('sesi_ruangan_id'))->nama_sesi }}</option>
                                @endif
                            </select>
                            @error('sesi_ruangan_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="kelas_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Kelas Siswa
                            </label>
                            <select name="kelas_id" id="kelas_id"
                                class="form-select w-full rounded-md shadow-sm @error('kelas_id') border-red-500 @enderror">
                                <option value="">Pilih Kelas Siswa</option>
                                @foreach ($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}"
                                        {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Pilih kelas untuk mempermudah pencarian siswa</p>
                        </div>

                        <div>
                            <label for="siswa_ids" class="block text-sm font-medium text-gray-700 mb-1">
                                Siswa <span class="text-red-500">*</span>
                            </label>
                            <select name="siswa_ids[]" id="siswa_ids" multiple
                                class="form-select w-full rounded-md shadow-sm @error('siswa_ids') border-red-500 @enderror"
                                required>
                                <option value="">Pilih Siswa</option>
                                @if (old('siswa_ids'))
                                    @foreach (old('siswa_ids') as $siswaId)
                                        @php $siswa = App\Models\Siswa::find($siswaId); @endphp
                                        @if ($siswa)
                                            <option value="{{ $siswa->id }}" selected>
                                                {{ $siswa->nama }} ({{ $siswa->nis }})
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            @error('siswa_ids')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">Anda dapat memilih beberapa siswa sekaligus</p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="catatan" id="catatan"
                            class="form-textarea w-full rounded-md shadow-sm @error('catatan') border-red-500 @enderror" rows="3">{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="{{ route('naskah.enrollment-ujian.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-save mr-2"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize select2
            $('#siswa_ids').select2({
                placeholder: 'Cari siswa berdasarkan nama atau NIS',
                allowClear: true,
                ajax: {
                    url: '{{ route('naskah.enrollment-ujian.get-siswa-options') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            search: params.term,
                            kelas_id: $('#kelas_id').val()
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2
            });

            // Jadwal and Sesi relationship
            const jadwalSelect = document.getElementById('jadwal_ujian_id');
            const sesiSelect = document.getElementById('sesi_ruangan_id');

            if (jadwalSelect && sesiSelect) {
                jadwalSelect.addEventListener('change', function() {
                    const jadwalId = this.value;
                    sesiSelect.disabled = true;
                    sesiSelect.innerHTML = '<option value="">Pilih Sesi Ujian</option>';

                    if (jadwalId) {
                        fetch(
                                `{{ route('naskah.enrollment-ujian.get-sesi-options') }}?jadwal_id=${jadwalId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.length > 0) {
                                    data.forEach(item => {
                                        const option = document.createElement('option');
                                        option.value = item.id;
                                        option.textContent = item.text;
                                        sesiSelect.appendChild(option);
                                    });
                                    sesiSelect.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error loading sesi options', error);
                                alert('Gagal memuat sesi ujian');
                            });
                    }
                });

                // Trigger change if value already selected (for edit form)
                if (jadwalSelect.value) {
                    const event = new Event('change');
                    jadwalSelect.dispatchEvent(event);
                }
            }

            // Kelas filtering for siswa
            const kelasSelect = document.getElementById('kelas_id');
            if (kelasSelect) {
                kelasSelect.addEventListener('change', function() {
                    $('#siswa_ids').val(null).trigger('change');
                });
            }
        });
    </script>
@endsection

@push('meta')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border-color: #d1d5db;
            border-radius: 0.375rem;
        }

        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
        }
    </style>
@endpush
