<!-- filepath: resources\views\features\data\siswa\create.blade.php -->

@extends('layouts.admin')

@section('title', 'Add New Siswa')
@section('page-title', 'Add New Siswa')

@section('content')
    <div class="max-w-4xl mx-auto">

        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('data.dashboard') }}" class="text-gray-700 hover:text-blue-600">
                        <i class="fa-solid fa-home"></i>
                        Data Management
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('data.siswa.index') }}" class="text-gray-700 hover:text-blue-600">Siswa</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fa-solid fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-gray-500">Add New</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Form Card -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Information</h3>
                <p class="text-sm text-gray-500 mt-1">Fill in the information below to add a new student.</p>
            </div>

            <form action="{{ route('data.siswa.store') }}" method="POST" id="siswa-form">
                @csrf

                <div class="px-6 py-6 space-y-6">

                    <!-- ID Yayasan -->
                    <div>
                        <label for="idyayasan" class="block text-sm font-medium text-gray-700 mb-2">
                            ID Yayasan <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="idyayasan" id="idyayasan"
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('idyayasan') border-red-500 @enderror"
                                value="{{ old('idyayasan') }}" required>
                            <div id="idyayasan-validation" class="hidden mt-1 text-sm text-red-500">
                                <i class="fa-solid fa-times-circle mr-1"></i>
                                <span id="idyayasan-message"></span>
                            </div>
                        </div>
                        @error('idyayasan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Siswa -->
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Siswa <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama" id="nama"
                            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                            value="{{ old('nama') }}" required>
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email
                            <span class="text-gray-400 text-xs">(Leave empty to auto-generate)</span>
                        </label>
                        <div class="relative">
                            <input type="email" name="email" id="email"
                                class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                                value="{{ old('email') }}">
                            <button type="button" id="preview-email-btn"
                                class="absolute right-2 top-2 text-blue-600 hover:text-blue-800 text-sm">
                                Preview
                            </button>
                        </div>
                        <div id="email-preview" class="hidden mt-1 text-sm text-blue-600">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Auto-generated email: <span id="preview-email-text"></span>
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select name="kelas" id="kelas"
                            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('kelas') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach (App\Models\Siswa::getKelasOptions() as $kelas)
                                <option value="{{ $kelas }}" {{ old('kelas') === $kelas ? 'selected' : '' }}>
                                    {{ $kelas }}
                                </option>
                            @endforeach
                        </select>
                        @error('kelas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Pembayaran -->
                    <div>
                        <label for="status_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select name="status_pembayaran" id="status_pembayaran"
                            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('status_pembayaran') border-red-500 @enderror"
                            required>
                            <option value="">-- Pilih Status --</option>
                            @foreach (App\Models\Siswa::getStatusPembayaranOptions() as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('status_pembayaran') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status_pembayaran')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Default password will be set to ID Yayasan
                    </div>

                    <div class="flex items-center space-x-3">
                        <a href="{{ route('data.siswa.index') }}"
                            class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center space-x-2">
                            <i class="fa-solid fa-times"></i>
                            <span>Cancel</span>
                        </a>

                        <button type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center space-x-2"
                            id="submit-btn">
                            <i class="fa-solid fa-save"></i>
                            <span>Create Student</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const idyayasanInput = document.getElementById('idyayasan');
            const namaInput = document.getElementById('nama');
            const emailInput = document.getElementById('email');
            const previewEmailBtn = document.getElementById('preview-email-btn');
            const emailPreview = document.getElementById('email-preview');
            const previewEmailText = document.getElementById('preview-email-text');
            const idyayasanValidation = document.getElementById('idyayasan-validation');
            const idyayasanMessage = document.getElementById('idyayasan-message');
            const submitBtn = document.getElementById('submit-btn');

            let validationTimeout;

            // Validate ID Yayasan
            function validateIdYayasan() {
                const idyayasan = idyayasanInput.value.trim();

                if (!idyayasan) {
                    hideValidation();
                    return;
                }

                clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => {
                    fetch('{{ route('data.siswa.validate-idyayasan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                idyayasan: idyayasan
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.valid) {
                                hideValidation();
                            } else {
                                showValidation(data.message, false);
                            }
                        })
                        .catch(error => {
                            console.error('Validation error:', error);
                            hideValidation();
                        });
                }, 500);
            }

            function showValidation(message, isValid) {
                idyayasanMessage.textContent = message;
                idyayasanValidation.classList.remove('hidden');

                if (isValid) {
                    idyayasanValidation.className = 'mt-1 text-sm text-green-500';
                } else {
                    idyayasanValidation.className = 'mt-1 text-sm text-red-500';
                    idyayasanInput.classList.add('border-red-500');
                }

                submitBtn.disabled = !isValid;
            }

            function hideValidation() {
                idyayasanValidation.classList.add('hidden');
                idyayasanInput.classList.remove('border-red-500');
                submitBtn.disabled = false;
            }

            // Preview email
            function previewEmail() {
                const nama = namaInput.value.trim();
                const idyayasan = idyayasanInput.value.trim();

                if (!nama && !idyayasan) {
                    emailPreview.classList.add('hidden');
                    return;
                }

                fetch('{{ route('data.siswa.preview-email') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            nama: nama,
                            idyayasan: idyayasan
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            previewEmailText.textContent = data.email;
                            emailPreview.classList.remove('hidden');
                        }
                    })
                    .catch(error => {
                        console.error('Email preview error:', error);
                    });
            }

            // Event listeners
            idyayasanInput.addEventListener('input', validateIdYayasan);
            previewEmailBtn.addEventListener('click', previewEmail);

            // Auto preview when nama or idyayasan changes
            namaInput.addEventListener('input', previewEmail);
            idyayasanInput.addEventListener('input', previewEmail);
        });
    </script>
@endsection
