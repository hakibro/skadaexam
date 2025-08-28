<!-- filepath: resources\views\features\data\siswa\test-sync.blade.php -->

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Test SISDA Payment API Connection') }}
            </h2>
            <a href="{{ route('data.siswa.index') }}"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fa-solid fa-arrow-left mr-2"></i>Back to Siswa
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- API Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <h3 class="text-lg font-medium text-blue-900 mb-4">
                        <i class="fa-solid fa-cogs mr-2"></i>SISDA API Configuration
                    </h3>

                    <div class="grid md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong>Base URL:</strong> {{ $config['base_url'] }}
                        </div>
                        <div>
                            <strong>Payment Endpoint:</strong> {{ $config['payment_endpoint'] }}
                        </div>
                        <div>
                            <strong>Timeout:</strong> {{ $config['timeout'] }} seconds
                        </div>
                        <div>
                            <strong>Retry Times:</strong> {{ $config['retry_times'] }}
                        </div>
                        <div class="md:col-span-2">
                            <strong>Total Students in DB:</strong> {{ $totalSiswa }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Options -->
            <div class="grid md:grid-cols-3 gap-6">

                <!-- Single Student Test -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fa-solid fa-user mr-2 text-blue-500"></i>Test Single Student
                        </h3>

                        <form id="single-test-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ID Yayasan</label>
                                <input type="text" name="idyayasan" id="single-idyayasan"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
                                    placeholder="e.g., 190013" required>
                            </div>
                            <button type="submit"
                                class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fa-solid fa-play mr-2"></i>Test Single
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Multiple Students Test -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fa-solid fa-users mr-2 text-green-500"></i>Test Multiple Students
                        </h3>

                        <form id="multiple-test-form" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Number of Students</label>
                                <select name="limit"
                                    class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="3">3 students</option>
                                    <option value="5" selected>5 students</option>
                                    <option value="10">10 students</option>
                                    <option value="20">20 students</option>
                                </select>
                            </div>
                            <button type="submit"
                                class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fa-solid fa-play mr-2"></i>Test Multiple
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Cache Management -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            <i class="fa-solid fa-database mr-2 text-red-500"></i>Cache Management
                        </h3>

                        <div class="space-y-3">
                            <button onclick="clearCache()"
                                class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fa-solid fa-trash mr-2"></i>Clear All Cache
                            </button>

                            <div class="flex space-x-2">
                                <input type="text" id="cache-idyayasan"
                                    class="flex-1 border border-gray-300 rounded px-2 py-1 text-sm"
                                    placeholder="ID Yayasan">
                                <button onclick="clearSpecificCache()"
                                    class="bg-orange-500 hover:bg-orange-700 text-white px-3 py-1 rounded text-sm">
                                    Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Area -->
            <div id="test-results" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fa-solid fa-chart-line mr-2"></i>Test Results
                    </h3>
                    <div id="test-content"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Single student test
        document.getElementById('single-test-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const idyayasan = document.getElementById('single-idyayasan').value;
            const resultsDiv = document.getElementById('test-results');
            const contentDiv = document.getElementById('test-content');

            resultsDiv.classList.remove('hidden');
            contentDiv.innerHTML =
                '<div class="flex items-center"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Testing single student...</div>';

            fetch('/data/siswa/test-sync-single', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        idyayasan: idyayasan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contentDiv.innerHTML = `
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-check-circle text-green-500"></i>
                                <span class="font-medium">Test Successful</span>
                                <span class="text-gray-500">(${data.duration}ms)</span>
                            </div>
                            
                            <div class="bg-green-50 p-4 rounded border">
                                <h4 class="font-medium mb-2">Student Information:</h4>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div><strong>ID Yayasan:</strong> ${data.data.idyayasan}</div>
                                    <div><strong>Payment Status:</strong> 
                                        <span class="px-2 py-1 rounded text-xs ${data.data.payment_status === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${data.data.payment_status}
                                        </span>
                                    </div>
                                    <div><strong>In Database:</strong> ${data.data.exists_in_db ? 'Yes' : 'No'}</div>
                                    ${data.data.siswa_data ? `
                                            <div><strong>Name:</strong> ${data.data.siswa_data.nama || 'N/A'}</div>
                                            <div><strong>Class:</strong> ${data.data.siswa_data.kelas || 'N/A'}</div>
                                            <div><strong>Recommendation:</strong> ${data.data.siswa_data.rekomendasi}</div>
                                        ` : ''}
                                </div>
                            </div>
                            
                            ${data.data.payment_summary ? `
                                    <details class="bg-gray-50 p-4 rounded">
                                        <summary class="cursor-pointer font-medium">Payment Details</summary>
                                        <pre class="text-xs mt-2 overflow-auto">${JSON.stringify(data.data.payment_summary, null, 2)}</pre>
                                    </details>
                                ` : ''}
                        </div>
                    `;
                    } else {
                        contentDiv.innerHTML = `
                        <div class="bg-red-50 p-4 rounded border border-red-200">
                            <div class="flex items-center space-x-2 mb-2">
                                <i class="fa-solid fa-times-circle text-red-500"></i>
                                <span class="font-medium text-red-800">Test Failed</span>
                                <span class="text-gray-500">(${data.duration}ms)</span>
                            </div>
                            <p class="text-red-700">${data.message}</p>
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = `<div class="text-red-600">Error: ${error.message}</div>`;
                });
        });

        // Multiple students test
        document.getElementById('multiple-test-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const limit = document.querySelector('[name="limit"]').value;
            const resultsDiv = document.getElementById('test-results');
            const contentDiv = document.getElementById('test-content');

            resultsDiv.classList.remove('hidden');
            contentDiv.innerHTML =
                `<div class="flex items-center"><i class="fa-solid fa-spinner fa-spin mr-2"></i>Testing ${limit} students...</div>`;

            fetch('/data/siswa/test-sync-multiple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        limit: parseInt(limit)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        contentDiv.innerHTML = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-4 gap-4 text-center">
                                <div class="bg-blue-50 p-3 rounded">
                                    <div class="text-2xl font-bold text-blue-600">${data.stats.total_tested}</div>
                                    <div class="text-sm text-blue-800">Tested</div>
                                </div>
                                <div class="bg-green-50 p-3 rounded">
                                    <div class="text-2xl font-bold text-green-600">${data.stats.success_count}</div>
                                    <div class="text-sm text-green-800">Success</div>
                                </div>
                                <div class="bg-red-50 p-3 rounded">
                                    <div class="text-2xl font-bold text-red-600">${data.stats.fail_count}</div>
                                    <div class="text-sm text-red-800">Failed</div>
                                </div>
                                <div class="bg-purple-50 p-3 rounded">
                                    <div class="text-2xl font-bold text-purple-600">${(data.duration/1000).toFixed(1)}s</div>
                                    <div class="text-sm text-purple-800">Total Time</div>
                                </div>
                            </div>
                            
                            <details class="bg-gray-50 p-4 rounded">
                                <summary class="cursor-pointer font-medium">Individual Results</summary>
                                <div class="mt-4 space-y-2">
                                    ${data.sample_data.map(student => `
                                            <div class="flex justify-between items-center py-2 border-b text-sm">
                                                <div>
                                                    <strong>${student.idyayasan}</strong> - ${student.nama || 'Unknown'}
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    ${student.success ? 
                                                        `<span class="px-2 py-1 rounded text-xs bg-green-100 text-green-800">${student.payment_status}</span>` :
                                                        `<span class="px-2 py-1 rounded text-xs bg-red-100 text-red-800">Failed</span>`
                                                    }
                                                    <span class="text-gray-500">${(student.duration/1000).toFixed(2)}s</span>
                                                </div>
                                            </div>
                                        `).join('')}
                                </div>
                            </details>
                        </div>
                    `;
                    } else {
                        contentDiv.innerHTML = `
                        <div class="bg-red-50 p-4 rounded border border-red-200">
                            <p class="text-red-700">${data.message}</p>
                        </div>
                    `;
                    }
                })
                .catch(error => {
                    contentDiv.innerHTML = `<div class="text-red-600">Error: ${error.message}</div>`;
                });
        });

        function clearCache() {
            fetch('/data/siswa-clear-cache', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success ? data.message : 'Error: ' + data.message);
                });
        }

        function clearSpecificCache() {
            const idyayasan = document.getElementById('cache-idyayasan').value;
            if (!idyayasan) {
                alert('Please enter ID Yayasan');
                return;
            }

            fetch('/data/siswa-clear-payment-cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        idyayasan: idyayasan
                    })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.success ? data.message : 'Error: ' + data.message);
                    if (data.success) {
                        document.getElementById('cache-idyayasan').value = '';
                    }
                });
        }
    </script>
</x-app-layout>
