@extends('layouts.admin')

@section('title', 'Pengaturan Mode Kiosk')
@section('page-title', 'Pengaturan Mode Kiosk')
@section('page-description', 'Kelola password exit dan masa berlaku untuk mode kiosk')

@section('content')
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form method="POST" action="{{ route('admin.kiosk-settings.update') }}" id="kioskSettingsForm">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fa-solid fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Tentang Mode Kiosk</p>
                            <p>Mode kiosk digunakan untuk mengunci aplikasi ujian pada perangkat siswa. Password exit
                                diperlukan untuk keluar dari mode kiosk. Password akan otomatis kadaluarsa sesuai waktu yang
                                ditentukan.</p>
                        </div>
                    </div>
                </div>

                <!-- Exit Password -->
                <div>
                    <label for="exit_password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password Exit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="exit_password" id="exit_password"
                        value="{{ old('exit_password', $settings['exit_password']) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Masukkan password exit" required minlength="4" maxlength="50">
                    @error('exit_password')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Password harus minimal 4 karakter dan maksimal 50 karakter</p>
                </div>

                <!-- Password Expiry -->
                <div>
                    <label for="password_expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                        Masa Berlaku Password <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="password_expires_at" id="password_expires_at"
                        value="{{ old('password_expires_at', $settings['password_expires_at'] ? \Carbon\Carbon::parse($settings['password_expires_at'])->format('Y-m-d\TH:i') : '') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                    @error('password_expires_at')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Pilih tanggal dan waktu ketika password akan kadaluarsa</p>
                </div>

                <!-- Current Status -->
                @if ($settings['exit_password'])
                    <div class="border-t pt-6">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3">Status Saat Ini</h3>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Password Exit:</span>
                                <span
                                    class="text-sm font-mono bg-white px-3 py-1 rounded border">{{ $settings['exit_password'] }}</span>
                            </div>
                            @if ($settings['password_expires_at'])
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Berlaku Hingga:</span>
                                    <span class="text-sm font-medium">
                                        {{ \Carbon\Carbon::parse($settings['password_expires_at'])->format('d M Y, H:i') }}
                                        @if (\Carbon\Carbon::parse($settings['password_expires_at'])->isPast())
                                            <span
                                                class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded">Kadaluarsa</span>
                                        @else
                                            <span
                                                class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">Aktif</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
                <button type="button" onclick="testKioskAPI()"
                    class="px-4 py-2 rounded-md bg-gray-600 text-white hover:bg-gray-700 transition-colors">
                    <i class="fa-solid fa-flask mr-2"></i>
                    Test API
                </button>
                <button type="submit"
                    class="px-6 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-save mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- API Test Result Modal -->
    <div id="apiTestModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Hasil Test API</h3>
                <button onclick="closeApiTestModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
            </div>
            <div id="apiTestResult" class="text-sm"></div>
            <div class="mt-4 flex justify-end">
                <button onclick="closeApiTestModal()" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Tutup
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function testKioskAPI() {
            const password = document.getElementById('exit_password').value;
            const expiresAt = document.getElementById('password_expires_at').value;

            if (!password || !expiresAt) {
                alert('Mohon isi password dan masa berlaku terlebih dahulu');
                return;
            }

            // Show loading
            const modal = document.getElementById('apiTestModal');
            const resultDiv = document.getElementById('apiTestResult');
            resultDiv.innerHTML =
                '<div class="text-center py-4"><i class="fa-solid fa-spinner fa-spin text-2xl text-blue-600"></i><p class="mt-2 text-gray-600">Mengirim request ke API...</p></div>';
            modal.classList.remove('hidden');

            // Send API request
            fetch('/api/kiosk/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        exit_password: password,
                        password_expires_at: expiresAt
                    })
                })
                .then(response => response.json())
                .then(data => {
                    let html = '<div class="space-y-3">';
                    html += '<div class="bg-green-50 border border-green-200 rounded p-3">';
                    html +=
                        '<p class="font-semibold text-green-800 mb-2"><i class="fa-solid fa-check-circle mr-2"></i>Request Berhasil</p>';
                    html += '<pre class="text-xs bg-white p-3 rounded overflow-x-auto">' + JSON.stringify(data, null,
                        2) + '</pre>';
                    html += '</div>';
                    html += '</div>';
                    resultDiv.innerHTML = html;
                })
                .catch(error => {
                    let html = '<div class="bg-red-50 border border-red-200 rounded p-3">';
                    html +=
                        '<p class="font-semibold text-red-800 mb-2"><i class="fa-solid fa-exclamation-circle mr-2"></i>Request Gagal</p>';
                    html += '<p class="text-sm text-red-700">' + error.message + '</p>';
                    html += '</div>';
                    resultDiv.innerHTML = html;
                });
        }

        function closeApiTestModal() {
            document.getElementById('apiTestModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('apiTestModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeApiTestModal();
            }
        });
    </script>
@endsection
