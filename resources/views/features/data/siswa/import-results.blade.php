<!-- filepath: resources\views\features\data\siswa\import-results.blade.php -->

@extends('layouts.admin')

@section('title', 'Import Results')
@section('page-title', 'Import Results - Siswa Data')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Import Results</h3>
                <p class="text-sm text-gray-500">Excel import summary and details</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('data.siswa.import') }}"
                    class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                    <i class="fa-solid fa-file-import mr-2"></i>Import Again
                </a>
                <a href="{{ route('data.siswa.index') }}"
                    class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>

        <!-- Import Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Total Rows -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-file-lines text-blue-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Rows</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ $results['total_rows'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Count -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-check-circle text-green-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Successful</dt>
                                <dd class="text-lg font-medium text-green-600">{{ $results['success_count'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Count -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-times-circle text-red-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Errors</dt>
                                <dd class="text-lg font-medium text-red-600">{{ $results['error_count'] ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-chart-line text-indigo-500 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Success Rate</dt>
                                <dd class="text-lg font-medium text-indigo-600">
                                    @php
                                        $total = $results['total_rows'] ?? 0;
                                        $success = $results['success_count'] ?? 0;
                                        $rate = $total > 0 ? round(($success / $total) * 100, 1) : 0;
                                    @endphp
                                    {{ $rate }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Alert -->
        @if (($results['success_count'] ?? 0) > 0)
            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-green-800">
                            Import Completed Successfully
                        </h3>
                        <div class="mt-2 text-sm text-green-700">
                            <p>{{ $results['success_count'] }} out of {{ $results['total_rows'] }} rows were imported
                                successfully.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Alert -->
        @if (($results['error_count'] ?? 0) > 0)
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            Some Rows Failed to Import
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ $results['error_count'] }} rows encountered errors during import. Please review the
                                details below.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Successfully Created Students -->
        @if (!empty($results['created']) && count($results['created']) > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 bg-green-50 border-b border-green-200">
                    <h3 class="text-lg leading-6 font-medium text-green-800">
                        <i class="fa-solid fa-user-plus mr-2"></i>
                        Successfully Created Students ({{ count($results['created']) }})
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-green-600">
                        New students that were successfully added to the system.
                    </p>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <ul class="divide-y divide-gray-200">
                        @foreach ($results['created'] as $student)
                            <li class="px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fa-solid fa-check text-green-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $student['nama'] ?? 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                ID: {{ $student['idyayasan'] ?? 'N/A' }} |
                                                Email: {{ $student['email'] ?? 'N/A' }} |
                                                Class: {{ $student['kelas'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-green-600">
                                        <i class="fa-solid fa-plus-circle mr-1"></i>Created
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Updated Students -->
        @if (!empty($results['updated']) && count($results['updated']) > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 bg-blue-50 border-b border-blue-200">
                    <h3 class="text-lg leading-6 font-medium text-blue-800">
                        <i class="fa-solid fa-user-edit mr-2"></i>
                        Updated Students ({{ count($results['updated']) }})
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-blue-600">
                        Existing students that were updated with new information.
                    </p>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <ul class="divide-y divide-gray-200">
                        @foreach ($results['updated'] as $student)
                            <li class="px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fa-solid fa-edit text-blue-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $student['nama'] ?? 'N/A' }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                ID: {{ $student['idyayasan'] ?? 'N/A' }} |
                                                Email: {{ $student['email'] ?? 'N/A' }} |
                                                Class: {{ $student['kelas'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-blue-600">
                                        <i class="fa-solid fa-sync-alt mr-1"></i>Updated
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Import Errors -->
        @if (!empty($results['errors']) && count($results['errors']) > 0)
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <div class="px-4 py-5 sm:px-6 bg-red-50 border-b border-red-200">
                    <h3 class="text-lg leading-6 font-medium text-red-800">
                        <i class="fa-solid fa-exclamation-circle mr-2"></i>
                        Import Errors ({{ count($results['errors']) }})
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-red-600">
                        Rows that failed to import due to validation errors or other issues.
                    </p>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <ul class="divide-y divide-gray-200">
                        @foreach ($results['errors'] as $error)
                            <li class="px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                            <i class="fa-solid fa-times text-red-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">
                                                Row {{ $error['row'] ?? 'N/A' }}
                                                @if (!empty($error['data']['idyayasan']))
                                                    - ID: {{ $error['data']['idyayasan'] }}
                                                @endif
                                                @if (!empty($error['data']['nama']))
                                                    - {{ $error['data']['nama'] }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm text-red-600">
                                                <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                                {{ $error['message'] ?? 'Unknown error' }}
                                            </p>
                                            @if (!empty($error['details']))
                                                <div class="mt-2 text-xs text-gray-500 bg-gray-50 rounded p-2">
                                                    @if (is_array($error['details']))
                                                        <ul class="list-disc list-inside space-y-1">
                                                            @foreach ($error['details'] as $field => $messages)
                                                                @if (is_array($messages))
                                                                    @foreach ($messages as $message)
                                                                        <li><strong>{{ $field }}:</strong>
                                                                            {{ $message }}</li>
                                                                    @endforeach
                                                                @else
                                                                    <li><strong>{{ $field }}:</strong>
                                                                        {{ $messages }}</li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        {{ $error['details'] }}
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- No Results -->
        @if (($results['total_rows'] ?? 0) == 0)
            <div class="text-center py-12">
                <i class="fa-solid fa-file-circle-question text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Import Results Found</h3>
                <p class="text-gray-500 mb-6">There are no import results to display. Please perform an import first.</p>
                <a href="{{ route('data.siswa.import') }}"
                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fa-solid fa-file-import mr-1"></i>Start Import
                </a>
            </div>
        @endif

        <!-- Action Buttons -->
        @if (($results['total_rows'] ?? 0) > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h4 class="text-lg font-medium text-gray-900 mb-4">Next Steps</h4>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('data.siswa.index') }}"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        <i class="fa-solid fa-list mr-2"></i>View All Students
                    </a>

                    @if (($results['success_count'] ?? 0) > 0)
                        <button onclick="syncNewStudents()"
                            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                            <i class="fa-solid fa-sync-alt mr-2"></i>Sync Payment Status
                        </button>
                    @endif

                    @if (($results['error_count'] ?? 0) > 0)
                        <a href="{{ route('data.siswa.template') }}"
                            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            <i class="fa-solid fa-download mr-2"></i>Download Template
                        </a>
                    @endif

                    <a href="{{ route('data.siswa.import') }}"
                        class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700">
                        <i class="fa-solid fa-file-import mr-2"></i>Import More Data
                    </a>
                </div>
            </div>
        @endif

    </div>

    <!-- JavaScript for additional functionality -->
    <script>
        function syncNewStudents() {
            if (confirm('Sync payment status for newly imported students?')) {
                // Trigger sync for recently imported students
                fetch('{{ route('data.siswa.sync-all-payments') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            limit: {{ $results['success_count'] ?? 0 }}
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Payment sync initiated for newly imported students!');
                        } else {
                            alert('Error starting sync: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Sync error:', error);
                        alert('Error starting payment sync');
                    });
            }
        }

        // Auto refresh every 30 seconds if there are recent imports
        @if (($results['success_count'] ?? 0) > 0)
            setTimeout(() => {
                if (confirm('Import completed! Would you like to view the updated student list?')) {
                    window.location.href = '{{ route('data.siswa.index') }}';
                }
            }, 3000);
        @endif
    </script>
@endsection
