<!-- filepath: resources\views\features\data\guru\import.blade.php -->
@extends('layouts.admin')

@section('title', 'Import Guru Excel')
@section('page-title', 'Import Guru')
@section('page-description', 'Upload Excel file to import multiple guru records')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('data.guru.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Guru List</span>
            </a>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <div class="flex items-start space-x-3">
                <i class="fa-solid fa-info-circle text-blue-600 text-xl mt-1"></i>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">Import Instructions</h3>
                    <div class="text-blue-800 space-y-2">
                        <p>1. Download the Excel template below</p>
                        <p>2. Fill in your guru data following the template format</p>
                        <p>3. Upload the completed Excel file using the form below</p>
                        <p class="text-sm font-medium">Supported formats: .xlsx, .xls, .csv (Max: 2MB)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Download Template -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Step 1: Download Template</h3>
                    <p class="text-gray-600">Download the Excel template with sample data and required format.</p>
                </div>
                <a href="{{ route('data.guru.template') }}"
                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 flex items-center space-x-2">
                    <i class="fa-solid fa-download"></i>
                    <span>Download Template</span>
                </a>
            </div>

            <!-- Template Format Info -->
            <div class="mt-4 bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Required Columns:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-red-600">nama*</span> - Full name (required)
                    </div>
                    <div>
                        <span class="font-medium text-blue-600">nip</span> - NIP (optional)
                    </div>
                    <div>
                        <span class="font-medium text-red-600">email*</span> - Email address (required, unique)
                    </div>
                    <div>
                        <span class="font-medium text-red-600">password*</span> - Password (required, min 6 chars)
                    </div>
                    <div>
                        <span class="font-medium text-red-600">role*</span> - Role (guru, data, naskah, pengawas,
                        koordinator, ruangan)
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Step 2: Upload Excel File</h3>
            </div>

            <form action="{{ route('data.guru.import.process') }}" method="POST" enctype="multipart/form-data"
                class="p-6">
                @csrf

                <!-- File Upload -->
                <div class="mb-6">
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-file-excel mr-1 text-green-600"></i>
                        Select Excel File *
                    </label>
                    <div
                        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                viewBox="0 0 48 48">
                                <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file"
                                    class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Upload a file</span>
                                    <input id="file" name="file" type="file" class="sr-only"
                                        accept=".xlsx,.xls,.csv" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">XLSX, XLS, CSV up to 2MB</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('data.guru.index') }}"
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-upload mr-1"></i>
                        Import Excel
                    </button>
                </div>
            </form>
        </div>

        <!-- Import Results -->
        @if (session('importResults'))
            @php $results = session('importResults') @endphp
            <div class="mt-6 bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Import Results</h3>
                </div>
                <div class="p-6">
                    <!-- Summary -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-check-circle text-green-600"></i>
                                <span class="font-medium text-green-900">Successfully Imported</span>
                            </div>
                            <p class="text-2xl font-bold text-green-600">{{ $results['success'] }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="flex items-center space-x-2">
                                <i class="fa-solid fa-exclamation-circle text-red-600"></i>
                                <span class="font-medium text-red-900">Failed</span>
                            </div>
                            <p class="text-2xl font-bold text-red-600">{{ $results['errors'] }}</p>
                        </div>
                    </div>

                    <!-- Error Details -->
                    @if ($results['errors'] > 0 && isset($results['errorDetails']))
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-medium text-red-900 mb-2">Error Details:</h4>
                            <div class="max-h-60 overflow-y-auto space-y-2">
                                @foreach ($results['errorDetails'] as $error)
                                    <div class="bg-white p-3 rounded border-l-4 border-red-400">
                                        <p class="font-medium text-red-800">Row {{ $error['row'] }}:</p>
                                        <ul class="list-disc list-inside text-sm text-red-700 ml-4">
                                            @foreach ($error['errors'] as $errorMsg)
                                                <li>{{ $errorMsg }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <script>
        // File upload drag and drop enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('file');
            const dropZone = fileInput.closest('.border-dashed');

            fileInput.addEventListener('change', function() {
                const fileName = this.files[0]?.name;
                if (fileName) {
                    const fileLabel = this.closest('.space-y-1').querySelector('span');
                    fileLabel.textContent = fileName;
                }
            });
        });
    </script>

@endsection
