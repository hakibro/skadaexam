@extends('layouts.admin')

@section('title', 'Update Kelas Target Jadwal')
@section('page-title', 'Update Kelas Target Jadwal')
@section('page-description', 'Update kelas target untuk jadwal ujian yang sudah ada')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900">Update Kelas Target pada Jadwal Ujian</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Tool ini akan mengisi field kelas_target pada jadwal ujian yang masih kosong berdasarkan tingkat dan
                    jurusan pada mata pelajaran terkait.
                </p>
            </div>

            <div class="p-4 sm:p-6 space-y-6">
                <!-- Status Info -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-info text-blue-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Total jadwal ujian:</strong> {{ $totalJadwal }}
                            </p>
                            <p class="text-sm text-blue-700">
                                <strong>Jadwal dengan kelas target kosong:</strong> {{ $emptyTargetCount }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Update Controls -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h4 class="text-base font-medium text-gray-800 mb-3">Opsi Update</h4>

                    <div class="space-y-4">
                        <div>
                            <label for="limit" class="block text-sm font-medium text-gray-700">Batas Jumlah
                                Jadwal</label>
                            <input type="number" id="limit" name="limit" min="1" max="500" value="100"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Jumlah maksimal jadwal yang akan diproses dalam satu kali
                                update</p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" id="dry_run" name="dry_run" value="1" checked
                                class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="dry_run" class="ml-2 block text-sm text-gray-700">
                                Dry Run (tidak mengubah data, hanya simulasi)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('naskah.jadwal.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <button id="btnStartUpdate" type="button"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <i class="fa-solid fa-play mr-2"></i> Mulai Update
                    </button>
                </div>

                <!-- Results Section -->
                <div id="resultsSection" class="hidden">
                    <h4 class="text-base font-medium text-gray-800 mb-3">Hasil Update</h4>

                    <div id="stats" class="bg-gray-50 p-4 rounded-md mb-4">
                        <!-- Stats will be populated by JavaScript -->
                    </div>

                    <div class="border rounded-md">
                        <div class="bg-gray-50 px-4 py-2 border-b">
                            <h5 class="text-sm font-medium text-gray-700">Log</h5>
                        </div>
                        <div id="logContainer" class="p-4 bg-gray-900 text-white text-sm font-mono overflow-y-auto"
                            style="max-height: 300px;">
                            <!-- Log entries will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btnStartUpdate = document.getElementById('btnStartUpdate');
                const limitInput = document.getElementById('limit');
                const dryRunCheckbox = document.getElementById('dry_run');
                const resultsSection = document.getElementById('resultsSection');
                const statsContainer = document.getElementById('stats');
                const logContainer = document.getElementById('logContainer');

                btnStartUpdate.addEventListener('click', async function() {
                    btnStartUpdate.disabled = true;
                    btnStartUpdate.innerHTML =
                        '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Processing...';

                    const limit = limitInput.value;
                    const dryRun = dryRunCheckbox.checked;

                    // Add debug output to console
                    console.log('Sending request to:',
                        '{{ route('naskah.jadwal.batch-update-kelas-target.update') }}');
                    console.log('Request payload:', {
                        limit,
                        dry_run: dryRun
                    });

                    try {
                        const response = await fetch(
                            '{{ route('naskah.jadwal.batch-update-kelas-target.update') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    limit: parseInt(limit),
                                    dry_run: dryRun
                                })
                            });

                        console.log('Response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! Status: ${response.status}`);
                        }

                        const data = await response.json();
                        console.log('Response data:', data);

                        // Display results
                        resultsSection.classList.remove('hidden');

                        // Populate stats
                        const statsHtml = `
                            <div class="flex flex-wrap gap-4">
                                <div>
                                    <div class="text-gray-600 text-xs">Mode</div>
                                    <div class="font-medium">${data.stats.dry_run ? 'Dry Run (Simulasi)' : 'Update Langsung'}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 text-xs">Jadwal Diproses</div>
                                    <div class="font-medium">${data.stats.processed}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 text-xs">Berhasil</div>
                                    <div class="font-medium text-green-600">${data.stats.updated}</div>
                                </div>
                                <div>
                                    <div class="text-gray-600 text-xs">Gagal</div>
                                    <div class="font-medium text-red-600">${data.stats.failed}</div>
                                </div>
                            </div>
                        `;
                        statsContainer.innerHTML = statsHtml;

                        // Populate logs
                        logContainer.innerHTML = '';
                        data.log.forEach(logEntry => {
                            const logLine = document.createElement('div');

                            if (logEntry.includes('UPDATED')) {
                                logLine.classList.add('text-green-400');
                            } else if (logEntry.includes('SKIPPED') || logEntry.includes('ERROR')) {
                                logLine.classList.add('text-red-400');
                            } else if (logEntry.includes('DRY RUN')) {
                                logLine.classList.add('text-yellow-400');
                            }

                            logLine.textContent = logEntry;
                            logContainer.appendChild(logLine);
                        });

                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat melakukan update. Silakan coba lagi.');
                    } finally {
                        btnStartUpdate.disabled = false;
                        btnStartUpdate.innerHTML = '<i class="fa-solid fa-play mr-2"></i> Mulai Update';
                    }
                });
            });
        </script>
    @endpush
@endsection
