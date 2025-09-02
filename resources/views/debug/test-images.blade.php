@extends('layouts.admin')

@section('title', 'Test Images')
@section('page-title', 'Test Soal Images')
@section('page-description', 'Debug tool to test image storage functionality')

@section('content')
    <div class="bg-white shadow-md rounded-lg overflow-hidden mb-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900">Image Storage Test Results</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($results as $type => $result)
                    <div
                        class="bg-gray-50 rounded-lg p-4 border {{ $result['exists'] ? 'border-green-500' : 'border-red-500' }}">
                        <h4 class="text-md font-bold mb-2">{{ ucfirst($type) }}</h4>

                        @if ($result['filename'])
                            <div class="mb-3">
                                <p class="text-sm font-medium text-gray-700">Filename:</p>
                                <p class="text-sm text-gray-600 mb-2">{{ $result['filename'] }}</p>

                                <p class="text-sm font-medium text-gray-700">Path:</p>
                                <p class="text-sm text-gray-600 mb-2 break-all">{{ $result['full_path'] }}</p>

                                <p class="text-sm font-medium text-gray-700">Exists:</p>
                                <p class="text-sm {{ $result['exists'] ? 'text-green-600' : 'text-red-600' }} mb-2">
                                    {{ $result['exists'] ? 'Yes' : 'No' }}
                                </p>
                            </div>

                            @if ($result['exists'])
                                <div class="border rounded-lg overflow-hidden">
                                    <img src="{{ $result['url'] }}" alt="Test {{ $type }}" class="w-full">
                                </div>
                            @else
                                <div class="bg-red-100 p-3 rounded-lg">
                                    <p class="text-red-700 text-sm">Image file could not be created</p>
                                </div>
                            @endif
                        @else
                            <div class="bg-red-100 p-3 rounded-lg">
                                <p class="text-red-700 text-sm">Failed to generate image</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <h4 class="font-medium text-yellow-800 mb-2">Troubleshooting Information</h4>
                <ul class="list-disc pl-5 space-y-2 text-sm text-yellow-700">
                    <li>Storage Disk: <code class="px-1 bg-yellow-100 rounded">{{ config('filesystems.default') }}</code>
                    </li>
                    <li>Public Disk Path: <code
                            class="px-1 bg-yellow-100 rounded">{{ config('filesystems.disks.public.root') }}</code></li>
                    <li>Storage URL: <code
                            class="px-1 bg-yellow-100 rounded">{{ config('filesystems.disks.public.url') }}</code></li>
                    <li>Symbolic Link: <code class="px-1 bg-yellow-100 rounded">{{ public_path('storage') }}</code> → <code
                            class="px-1 bg-yellow-100 rounded">{{ storage_path('app/public') }}</code></li>
                    <li>Symbolic Link Exists: <code
                            class="px-1 bg-yellow-100 rounded">{{ file_exists(public_path('storage')) ? 'Yes' : 'No' }}</code>
                    </li>
                    <li>PHP GD Enabled: <code
                            class="px-1 bg-yellow-100 rounded">{{ extension_loaded('gd') ? 'Yes' : 'No' }}</code></li>
                    <li>PHP Extension Check: <code
                            class="px-1 bg-yellow-100 rounded">{{ json_encode(get_loaded_extensions()) }}</code></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
