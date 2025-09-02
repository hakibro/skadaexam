@extends('layouts.admin')

@section('title', 'Data Fix Results')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Data Fix Results
        </h2>

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <p class="font-bold">Error</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                <p class="font-bold">Success</p>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <!-- Results Card -->
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Status: <span
                        class="{{ $status == 'success' ? 'text-green-500' : 'text-red-500' }}">{{ ucfirst($status) }}</span>
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $message }}
                </p>
            </div>

            @if (isset($output) && is_array($output))
                <div class="overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                            <tr
                                class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800">
                                <th class="px-4 py-3">Section</th>
                                <th class="px-4 py-3">Processed</th>
                                <th class="px-4 py-3">Fixed</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800">
                            @foreach ($output as $key => $value)
                                @if (is_array($value) && !in_array($key, ['error', 'trace']))
                                    <tr class="text-gray-700 dark:text-gray-400">
                                        <td class="px-4 py-3 text-sm">
                                            {{ ucfirst($key) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $value['processed'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ $value['fixed'] ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if (isset($value['processed']) && isset($value['fixed']))
                                                <span
                                                    class="{{ $value['fixed'] > 0 ? 'text-green-500' : 'text-yellow-500' }}">
                                                    {{ $value['fixed'] > 0 ? 'Fixed' : 'No Changes' }}
                                                </span>
                                            @else
                                                <span class="text-gray-500">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (isset($output['error']))
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900 border border-red-300 dark:border-red-700 rounded-md">
                    <h4 class="text-red-700 dark:text-red-300 font-medium">Error Details</h4>
                    <pre class="mt-2 text-sm text-red-600 dark:text-red-400 overflow-x-auto">{{ $output['error'] }}</pre>
                </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('admin.dashboard') }}"
                    class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-purple-600 border border-transparent rounded-lg active:bg-purple-600 hover:bg-purple-700 focus:outline-none focus:shadow-outline-purple">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
