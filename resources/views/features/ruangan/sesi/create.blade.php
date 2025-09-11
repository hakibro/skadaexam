{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\sesi\create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Tambah Sesi - ' . $ruangan->nama_ruangan)
@section('page-title', 'Tambah Sesi Baru')
@section('page-description', 'Ruangan: ' . $ruangan->nama_ruangan)

@section('content')
    <div class="max-w-3xl mx-auto py-4">
        <!-- Flash Messages -->
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('ruangan.sesi.store', $ruangan->id) }}" method="POST"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Informasi Sesi Ruangan</h2>

                <!-- Template Sesi -->
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700">
                        Template Sesi
                    </label>
                    <select name="template_id" id="template_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Pilih Template (Opsional)</option>
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->nama_sesi }} - {{ $template->kode_sesi }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Memilih template akan mengisi otomatis detail sesi</p>
                </div>

                <!-- Nama Sesi -->
                <div>
                    <label for="nama_sesi" class="block text-sm font-medium text-gray-700">
                        Nama Sesi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_sesi" id="nama_sesi"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('nama_sesi') }}" required>
                    @error('nama_sesi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kode Sesi -->
                <div>
                    <label for="kode_sesi" class="block text-sm font-medium text-gray-700">
                        Kode Sesi
                    </label>
                    <input type="text" name="kode_sesi" id="kode_sesi"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('kode_sesi') }}" placeholder="{{ $ruangan->kode_ruangan ?? '' }}-">
                    <p class="mt-1 text-xs text-gray-500">Format: kode_ruangan-kode_sesi. Jika dibiarkan kosong akan
                        digenerate otomatis.</p>
                    @error('kode_sesi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Waktu Mulai -->
                    <div>
                        <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">
                            Waktu Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu_mulai" id="waktu_mulai"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('waktu_mulai') }}" required>
                        @error('waktu_mulai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Selesai -->
                    <div>
                        <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">
                            Waktu Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="waktu_selesai" id="waktu_selesai"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            value="{{ old('waktu_selesai') }}" required>
                        @error('waktu_selesai')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Note about pengawas assignment -->
                <div>
                    <div class="rounded-md bg-blue-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-info-circle text-blue-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>Pengawas ditugaskan per jadwal ujian melalui menu Koordinator > Penugasan Pengawas
                                        setelah sesi dibuat.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Keterangan -->
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Info Ruangan -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Informasi Ruangan</h3>
                    <div class="text-sm text-gray-600">
                        <p><strong>Nama:</strong> {{ $ruangan->nama_ruangan }}</p>
                        <p><strong>Kode:</strong> {{ $ruangan->kode_ruangan }}</p>
                        <p><strong>Kapasitas:</strong> {{ $ruangan->kapasitas }} orang</p>
                        <p><strong>Lokasi:</strong> {{ $ruangan->lokasi ?: 'Tidak ditentukan' }}</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('ruangan.sesi.index', $ruangan->id) }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Simpan Sesi
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateSelect = document.getElementById('template_id');
            const namaSesiInput = document.getElementById('nama_sesi');
            const waktuMulaiInput = document.getElementById('waktu_mulai');
            const waktuSelesaiInput = document.getElementById('waktu_selesai');
            const keteranganInput = document.getElementById('keterangan');

            // Template data
            const templates = @json($templates);

            // Handle template selection
            templateSelect.addEventListener('change', function() {
                const selectedTemplateId = parseInt(this.value);

                if (selectedTemplateId) {
                    const selectedTemplate = templates.find(template => template.id === selectedTemplateId);

                    if (selectedTemplate) {
                        // Fill form fields with template data
                        namaSesiInput.value = selectedTemplate.nama_sesi;
                        waktuMulaiInput.value = selectedTemplate.waktu_mulai;
                        waktuSelesaiInput.value = selectedTemplate.waktu_selesai;
                        if (selectedTemplate.deskripsi) {
                            keteranganInput.value = selectedTemplate.deskripsi;
                        }
                    }
                }
            });

            // Handle start time change
            waktuMulaiInput.addEventListener('change', function() {
                // If end time is empty or earlier than start time, set it to start time + 2 hours
                if (!waktuSelesaiInput.value || waktuSelesaiInput.value <= waktuMulaiInput.value) {
                    const startTime = new Date(`2000-01-01T${waktuMulaiInput.value}`);
                    startTime.setHours(startTime.getHours() + 2);
                    const hours = String(startTime.getHours()).padStart(2, '0');
                    const minutes = String(startTime.getMinutes()).padStart(2, '0');
                    waktuSelesaiInput.value = `${hours}:${minutes}`;
                }
            });
        });
    </script>
@endsection
