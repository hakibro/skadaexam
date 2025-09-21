@extends('layouts.admin')

@section('title', 'Test Violations API')
@section('page-title', 'Test Violations API')

@section('content')
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold mb-4">Test Violations API</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Session ID to test:</label>
                <select id="test-session" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                    <option value="all">All Sessions</option>
                    <option value="2">Session 2</option>
                </select>
            </div>

            <button id="test-button" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Test Get Violations
            </button>

            <div id="test-results" class="mt-4 p-4 bg-gray-100 rounded-md">
                <h3 class="font-bold mb-2">Results:</h3>
                <pre id="results-content" class="text-sm"></pre>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('test-button').addEventListener('click', function() {
            const sessionId = document.getElementById('test-session').value;
            const resultsContent = document.getElementById('results-content');

            resultsContent.textContent = 'Loading...';

            const url =
                `{{ url('/features/pengawas/get-violations') }}${sessionId !== 'all' ? '/' + sessionId : ''}`;

            console.log('Testing URL:', url);

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    resultsContent.textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsContent.textContent = 'Error: ' + error.message;
                });
        });
    </script>
@endsection
