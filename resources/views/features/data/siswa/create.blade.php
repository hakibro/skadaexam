<!-- filepath: resources\views\features\data\siswa\create.blade.php -->

@extends('layouts.admin')

@section('title', 'Add New Siswa')
@section('page-title', 'Add New Siswa')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow rounded-lg p-6">
            <form action="{{ route('data.siswa.store') }}" method="POST" id="siswa-form">
                @csrf

                <!-- ID Yayasan -->
                <div class="mb-6">
                    <label for="idyayasan" class="block text-sm font-medium text-gray-700 mb-2">ID Yayasan *</label>
                    <input type="text" name="idyayasan" id="idyayasan" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('idyayasan') border-red-500 @enderror"
                        value="{{ old('idyayasan') }}" placeholder="e.g., 190001">
                    @error('idyayasan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama -->
                <div class="mb-6">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input type="text" name="nama" id="nama"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                        value="{{ old('nama') }}" placeholder="Full name">
                    @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email - Auto Generated -->
                <div class="mb-6">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email
                        <span class="text-sm text-gray-500">(Auto-generated)</span>
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="email" readonly
                            class="w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-50 text-gray-600 focus:ring-blue-500 focus:border-blue-500"
                            value="{{ old('email') }}" placeholder="Will be generated automatically">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <i class="fa-solid fa-magic text-gray-400" title="Auto-generated"></i>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        <strong>Format: idyayasan@smkdata.sch.id</strong> (e.g., 190001@smkdata.sch.id)
                    </p>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas -->
                <div class="mb-6">
                    <label for="kelas" class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                    <input type="text" name="kelas" id="kelas"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('kelas') border-red-500 @enderror"
                        value="{{ old('kelas') }}" placeholder="e.g., XII IPA 1">
                    @error('kelas')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rekomendasi - Default "tidak" -->
                <div class="mb-6">
                    <label for="rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">
                        Rekomendasi *
                        <span class="text-sm text-gray-500">(Default: Tidak)</span>
                    </label>
                    <select name="rekomendasi" id="rekomendasi" required
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('rekomendasi') border-red-500 @enderror">
                        <option value="tidak" {{ old('rekomendasi', 'tidak') === 'tidak' ? 'selected' : '' }}>Tidak
                        </option>
                        <option value="ya" {{ old('rekomendasi') === 'ya' ? 'selected' : '' }}>Ya</option>
                    </select>
                    @error('rekomendasi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catatan Rekomendasi -->
                <div class="mb-6">
                    <label for="catatan_rekomendasi" class="block text-sm font-medium text-gray-700 mb-2">Catatan
                        Rekomendasi</label>
                    <textarea name="catatan_rekomendasi" id="catatan_rekomendasi" rows="3"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 @error('catatan_rekomendasi') border-red-500 @enderror"
                        placeholder="Optional notes about recommendation">{{ old('catatan_rekomendasi') }}</textarea>
                    @error('catatan_rekomendasi')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Auto-Generated Info -->
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fa-solid fa-info-circle text-blue-600 mr-2 mt-0.5"></i>
                        <div class="text-sm text-blue-800">
                            <h4 class="font-medium mb-1">Auto-Generated Values:</h4>
                            <ul class="list-disc list-inside space-y-1">
                                <li><strong>Email:</strong> Generated from name (nama@smkdata.sch.id) or ID Yayasan if name
                                    empty</li>
                                <li><strong>Password:</strong> Default "password" (can be changed later)</li>
                                <li><strong>Recommendation:</strong> Default "Tidak" selected</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('data.siswa.index') }}"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        <i class="fa-solid fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fa-solid fa-save mr-2"></i>Save Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for Auto Email Generation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const namaInput = document.getElementById('nama');
            const idyayasanInput = document.getElementById('idyayasan');
            const emailInput = document.getElementById('email');

            let debounceTimer;

            // Function to generate email preview
            function generateEmailPreview() {
                const nama = namaInput.value.trim();
                const idyayasan = idyayasanInput.value.trim();

                if (!nama && !idyayasan) {
                    emailInput.value = '';
                    return;
                }

                // Clear previous timer
                clearTimeout(debounceTimer);

                // Show loading
                emailInput.value = 'Generating...';

                // Debounce the AJAX call
                debounceTimer = setTimeout(() => {
                    fetch('{{ route('data.siswa.preview-email') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .content,
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
                                emailInput.value = data.email;
                            } else {
                                emailInput.value = '';
                                console.error('Email generation failed:', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error generating email:', error);
                            // Fallback: simple client-side generation
                            emailInput.value = generateClientSideEmail(nama, idyayasan);
                        });
                }, 500);
            }

            // Fallback client-side email generation
            function generateClientSideEmail(nama, idyayasan) {
                // PRIORITY: Use idyayasan first
                if (idyayasan) {
                    return idyayasan.toLowerCase().replace(/[^a-z0-9]/g, '') + '@smkdata.sch.id';
                }
                // FALLBACK: Use nama if idyayasan is empty
                else if (nama) {
                    return nama.toLowerCase().replace(/ /g, '.').replace(/[^a-z0-9.]/g, '') + '@smkdata.sch.id';
                }
                return '';
            }

            // Event listeners - prioritize idyayasan changes
            idyayasanInput.addEventListener('input', generateEmailPreview);
            namaInput.addEventListener('input', generateEmailPreview);

            // Initial generation - prioritize idyayasan
            if (idyayasanInput.value || namaInput.value) {
                generateEmailPreview();
            }

            // Form submission validation
            document.getElementById('siswa-form').addEventListener('submit', function(e) {
                if (!emailInput.value || emailInput.value === 'Generating...') {
                    e.preventDefault();
                    alert('Please wait for email generation to complete.');
                }
            });
        });
    </script>
@endsection
