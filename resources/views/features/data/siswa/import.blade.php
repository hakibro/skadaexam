{{-- filepath: resources\views\features\data\siswa\import.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Import Data Siswa dari Excel') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('data.siswa.template') }}"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fa-solid fa-download mr-2"></i>Download Template
                </a>
                <a href="{{ route('data.siswa.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Current Statistics -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-blue-50 border-b border-blue-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-blue-900">Current Database Status</h3>
                            <p class="text-sm text-blue-700">Total siswa in database:
                                <strong>{{ $totalSiswa }}</strong></p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-blue-600">{{ $totalSiswa }}</div>
                            <div class="text-sm text-blue-500">Students</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Instructions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fa-solid fa-info-circle mr-2 text-blue-500"></i>Petunjuk Import Excel
                    </h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-800 mb-2">Format File yang Didukung:</h4>
                            <ul class="text-sm text-gray-600 space-y-1 mb-4">
                                <li>• <strong>Excel:</strong> .xlsx, .xls</li>
                                <li>• <strong>CSV:</strong> .csv</li>
                                <li>• <strong>Ukuran maksimal:</strong> 10MB</li>
                            </ul>

                            <h4 class="font-medium text-gray-800 mb-2">Kolom yang Diperlukan:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• <strong>idyayasan</strong> (wajib) - ID unik siswa</li>
                                <li>• <strong>nama</strong> (opsional) - Nama lengkap siswa</li>
                                <li>• <strong>kelas</strong> (opsional) - Kelas siswa</li>
                                <li>• <strong>rekomendasi</strong> (wajib) - ya/tidak</li>
                                <li>• <strong>catatan_rekomendasi</strong> (opsional)</li>
                                <li>• <strong>email</strong> (opsional) - akan digenerate otomatis</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-800 mb-2">Proses Import:</h4>
                            <ol class="text-sm text-gray-600 space-y-1 mb-4">
                                <li>1. Download template Excel</li>
                                <li>2. Isi data siswa sesuai format</li>
                                <li>3. Simpan file</li>
                                <li>4. Upload file di form di bawah</li>
                                <li>5. Sistem akan memproses data</li>
                                <li>6. Lihat hasil import</li>
                            </ol>

                            <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                                <p class="text-sm text-yellow-800">
                                    <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                                    <strong>Catatan:</strong> Jika ID Yayasan sudah ada, data akan diupdate.
                                    Jika belum ada, akan dibuat data baru.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Import Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Upload File Excel</h3>

                    <form action="{{ route('data.siswa.import.process') }}" method="POST" enctype="multipart/form-data"
                        id="import-form">
                        @csrf

                        <div class="mb-6">
                            <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih File Excel/CSV
                            </label>

                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                        viewBox="0 0 48 48">
                                        <path
                                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload a file</span>
                                            <input id="file" name="file" type="file" accept=".xlsx,.xls,.csv"
                                                class="sr-only" onchange="showFileName(this)" required>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        Excel, CSV up to 10MB
                                    </p>
                                </div>
                            </div>

                            <div id="file-info" class="mt-2 text-sm text-gray-600 hidden"></div>

                            @error('file')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded flex items-center"
                                id="submit-btn">
                                <i class="fa-solid fa-upload mr-2"></i>
                                <span>Upload & Import</span>
                            </button>

                            <a href="{{ route('data.siswa.index') }}"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone - Clear All Data -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border-l-4 border-red-500">
                <div class="p-6 bg-red-50">
                    <h3 class="text-lg font-medium text-red-900 mb-4">
                        <i class="fa-solid fa-exclamation-triangle mr-2"></i>Danger Zone
                    </h3>
                    
                    <div class="bg-white border border-red-200 rounded p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-red-800">Clear All Student Data</h4>
                                <p class="text-sm text-red-600">
                                    This will permanently delete ALL student records from database. 
                                    Current count: <strong>{{ $totalSiswa }}</strong>
                                </p>
                            </div>
                            <button onclick="clearAllSiswaData()" 
                                    id="clear-all-btn"
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                <i class="fa-solid fa-trash mr-2"></i>Clear All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function showFileName(input) {
            const fileInfo = document.getElementById('file-info');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const size = (file.size / 1024 / 1024).toFixed(2);

                fileInfo.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-file-excel text-green-600"></i>
                        <span><strong>${file.name}</strong> (${size} MB)</span>
                        <i class="fa-solid fa-check-circle text-green-600"></i>
                    </div>
                `;
                fileInfo.classList.remove('hidden');
            } else {
                fileInfo.classList.add('hidden');
            }
        }

        // Show loading state on form submit
        document.getElementById('import-form').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin mr-2"></i>
                <span>Processing...</span>
            `;
        });

        function clearAllSiswaData() {
            const totalSiswa = {{ $totalSiswa }};
            
            if (totalSiswa === 0) {
                alert('No student data to clear.');
                return;
            }
            
            // Multiple confirmations for safety
            if (!confirm(`This will DELETE ALL ${totalSiswa} student records. Are you absolutely sure?`)) {
                return;
            }
            
            if (!confirm('This action CANNOT be undone. Continue?')) {
                return;
            }
            
            const confirmation = prompt('Type "CLEAR ALL SISWA" to confirm:');
            if (confirmation !== 'CLEAR ALL SISWA') {
                alert('Confirmation failed. Operation cancelled.');
                return;
            }
            
            const clearBtn = document.getElementById('clear-all-btn');
            const originalContent = clearBtn.innerHTML;
            
            clearBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Clearing...';
            clearBtn.disabled = true;
            
            fetch('/data/siswa-clear-all-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    confirm: 'CLEAR_ALL_SISWA'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Success! Deleted ${data.deleted_count} student records.`);
                    location.reload(); // Refresh to show 0 count
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error clearing data: ' + error.message);
                console.error('Clear error:', error);
            })
            .finally(() => {
                clearBtn.innerHTML = originalContent;
                clearBtn.disabled = false;
            });
        }
    </script>
</x-app-layout>
