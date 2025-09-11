{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\import-comprehensive.blade.php --}}
@extends('layouts.admin')

@section('title', 'Import Komprehensif Ruangan')
@section('page-title', 'Import Komprehensif Ruangan')
@section('page-description', 'Import data ruangan, sesi, dan siswa sekaligus dari file Excel')

@section('content')
    <div class="max-w-4xl mx-auto py-4">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Import Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-info-circle text-blue-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Import Komprehensif Ruangan, Sesi, dan Siswa</h3>
                    <div class="text-sm text-blue-700 space-y-2">
                        <p>• Format file yang didukung: Excel (.xlsx, .xls) dan CSV</p>
                        <p>• Import ini memungkinkan Anda menambahkan ruangan, sesi, dan siswa sekaligus dalam satu file</p>
                        <p>• Jika kode ruangan sudah ada, data ruangan akan diperbarui</p>
                        <p>• Jika kode sesi sudah ada, data sesi akan diperbarui</p>
                        <p>• Siswa hanya ditambahkan ke sesi jika belum ada dalam sesi tersebut</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Form -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-file-import text-indigo-600 mr-2"></i>
                        Upload File Import
                    </h3>
                </div>

                <form action="{{ route('ruangan.import.comprehensive.process') }}" method="POST"
                    enctype="multipart/form-data" class="p-6">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">File Excel/CSV</label>
                        <div
                            class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <i class="fa-solid fa-file-excel text-gray-400 text-3xl mb-2"></i>
                                <div class="flex text-sm text-gray-600">
                                    <label for="import_file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                        <span>Upload file</span>
                                        <input id="import_file" name="import_file" type="file" class="sr-only"
                                            accept=".xlsx,.xls,.csv">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    Excel atau CSV hingga 10MB
                                </p>
                                <p id="selected-file" class="text-sm text-green-600 mt-2 hidden"></p>
                            </div>
                        </div>
                        @error('import_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-between">
                        <a href="{{ route('ruangan.index') }}"
                            class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                        </a>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="fa-solid fa-upload mr-1"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- Template Download -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-download text-green-600 mr-2"></i>
                        Template & Contoh
                    </h3>
                </div>

                <div class="p-6 space-y-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Download Template</h4>
                        <div class="space-y-2">
                            <a href="{{ route('ruangan.import.comprehensive.template') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                <i class="fa-solid fa-download text-green-500 mr-2"></i>
                                Template Excel dengan Contoh Data
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Format Data yang Diperlukan</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <ul class="list-disc list-inside text-sm space-y-1 text-gray-700">
                                <li><strong>kode_ruangan</strong> - Kode unik untuk ruangan</li>
                                <li><strong>nama_ruangan</strong> - Nama ruangan</li>
                                <li><strong>kapasitas_ruangan</strong> - Kapasitas ruangan (angka)</li>
                                <li><strong>lokasi_ruangan</strong> - Lokasi ruangan (opsional)</li>
                                <li><strong>status_ruangan</strong> - Status ruangan: aktif, perbaikan, tidak_aktif</li>
                                <li><strong>kode_sesi</strong> - Kode unik untuk sesi</li>
                                <li><strong>nama_sesi</strong> - Nama sesi</li>
                                <li><strong>waktu_mulai_sesi</strong> - Format: HH:MM</li>
                                <li><strong>waktu_selesai_sesi</strong> - Format: HH:MM</li>
                                <li><strong>status_sesi</strong> - Status sesi: belum_mulai, berlangsung, selesai,
                                    dibatalkan</li>
                                <li><strong>idyayasan</strong> - ID Yayasan siswa</li>
                                <li><strong>nama_siswa</strong> - Nama siswa (referensi saja, tidak diimpor)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-lightbulb text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Tips</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Siswa harus sudah terdaftar dalam sistem. Data siswa yang tidak ditemukan berdasarkan
                                        idyayasan akan dilewati.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('import_file');
            const fileLabel = document.getElementById('selected-file');

            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    fileLabel.textContent = 'File dipilih: ' + this.files[0].name;
                    fileLabel.classList.remove('hidden');
                } else {
                    fileLabel.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
