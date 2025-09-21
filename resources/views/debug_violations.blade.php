@extends('layouts.admin')

@section('title', 'Violation System Debug')
@section('page-title', 'Violation System Debug')

@section('content')
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Violation System Debug Dashboard</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- User Info -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">Current User Info</h3>
                <div class="text-sm space-y-1">
                    <div><strong>Name:</strong> {{ auth()->user()->name }}</div>
                    <div><strong>ID:</strong> {{ auth()->user()->id }}</div>
                    <div><strong>Can Supervise:</strong> {{ auth()->user()->canSupervise() ? 'Yes' : 'No' }}</div>
                    <div><strong>Is Admin:</strong> {{ auth()->user()->isAdmin() ? 'Yes' : 'No' }}</div>
                    <div><strong>Roles:</strong> {{ auth()->user()->roles->pluck('name')->join(', ') ?: 'None' }}</div>
                </div>
            </div>

            <!-- Violations Count -->
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">Violations Summary</h3>
                <div class="text-sm space-y-1">
                    <div><strong>Total:</strong> <span id="total-violations">Loading...</span></div>
                    <div><strong>Today:</strong> <span id="today-violations">Loading...</span></div>
                    <div><strong>Session 2:</strong> <span id="session2-violations">Loading...</span></div>
                    <div><strong>Pending:</strong> <span id="pending-violations">Loading...</span></div>
                </div>
            </div>
        </div>

        <!-- Test Buttons -->
        <div class="mt-6 space-y-4">
            <div class="flex space-x-4">
                <button id="test-all-violations"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Test Get All Violations
                </button>
                <button id="test-session2-violations"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Test Get Session 2 Violations
                </button>
                <button id="create-test-violation"
                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Create Test Violation
                </button>
            </div>
        </div>

        <!-- Results Display -->
        <div class="mt-6">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">Test Results</h3>
                <div class="max-h-96 overflow-y-auto">
                    <pre id="test-results" class="text-xs whitespace-pre-wrap">Ready for testing...</pre>
                </div>
            </div>
        </div>

        <!-- Live Violations Display -->
        <div class="mt-6">
            <div class="bg-gray-100 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">Live Violations (Auto-refresh)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody id="live-violations-body" class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Loading violations...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let refreshInterval;
        let testResults = document.getElementById('test-results');

        function log(message) {
            console.log(message);
            testResults.textContent += new Date().toLocaleTimeString() + ': ' + message + '\n';
            testResults.scrollTop = testResults.scrollHeight;
        }

        // Load violation counts
        async function loadViolationCounts() {
            try {
                const response = await fetch('{{ route('test.direct.violations') }}');
                const data = await response.json();

                document.getElementById('total-violations').textContent = data.violations_count || '0';
                // We would need separate endpoints for these counts
            } catch (error) {
                log('Error loading violation counts: ' + error.message);
            }
        }

        // Test get all violations
        document.getElementById('test-all-violations').addEventListener('click', async function() {
            log('Testing get all violations...');
            try {
                const response = await fetch('{{ url('/features/pengawas/get-violations') }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                log('Response status: ' + response.status);

                const data = await response.json();
                log('Response: ' + JSON.stringify(data, null, 2));

                if (data.success && data.violations) {
                    log('Found ' + data.violations.length + ' violations');
                    loadLiveViolations();
                }
            } catch (error) {
                log('Error: ' + error.message);
            }
        });

        // Test get session 2 violations
        document.getElementById('test-session2-violations').addEventListener('click', async function() {
            log('Testing get session 2 violations...');
            try {
                const response = await fetch('{{ url('/features/pengawas/get-violations/2') }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                log('Response status: ' + response.status);

                const data = await response.json();
                log('Response: ' + JSON.stringify(data, null, 2));

                if (data.success && data.violations) {
                    log('Found ' + data.violations.length + ' violations for session 2');
                    loadLiveViolations();
                }
            } catch (error) {
                log('Error: ' + error.message);
            }
        });

        // Create test violation
        document.getElementById('create-test-violation').addEventListener('click', async function() {
            log('Creating test violation...');
            try {
                // This would need a test endpoint to create violations
                log('Test violation creation would need backend endpoint');
            } catch (error) {
                log('Error: ' + error.message);
            }
        });

        // Load live violations
        async function loadLiveViolations() {
            try {
                const response = await fetch('{{ url('/features/pengawas/get-violations/2') }}', {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success && data.violations) {
                    const tbody = document.getElementById('live-violations-body');

                    if (data.violations.length === 0) {
                        tbody.innerHTML =
                            '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No violations found</td></tr>';
                    } else {
                        tbody.innerHTML = '';
                        data.violations.forEach(violation => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                            <td class="px-6 py-4 text-sm text-gray-900">${violation.id}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${violation.siswa ? violation.siswa.nama : 'N/A'}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${violation.jenis_pelanggaran}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">${new Date(violation.waktu_pelanggaran).toLocaleString()}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 text-xs rounded ${violation.is_dismissed ? 'bg-gray-200' : (violation.is_finalized ? 'bg-blue-200' : 'bg-yellow-200')}">
                                    ${violation.is_dismissed ? 'Dismissed' : (violation.is_finalized ? 'Processed' : 'Pending')}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                ${!violation.is_dismissed && !violation.is_finalized ? 
                                    '<button class="text-blue-600 hover:text-blue-800 text-xs">Process</button>' : 
                                    '<span class="text-gray-400 text-xs">Handled</span>'
                                }
                            </td>
                        `;
                            tbody.appendChild(row);
                        });
                    }
                }
            } catch (error) {
                console.error('Error loading live violations:', error);
            }
        }

        // Initial load
        document.addEventListener('DOMContentLoaded', function() {
            loadViolationCounts();
            loadLiveViolations();

            // Auto-refresh every 10 seconds
            refreshInterval = setInterval(loadLiveViolations, 10000);
        });

        // Cleanup
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>
@endsection
