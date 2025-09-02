{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\ruangan\import.blade.php --}}
@extends('layouts.admin')

@section('title', 'Import Ruangan')
@section('page-title', 'Import Ruangan')
@section('page-description', 'Import data ruangan dari file Excel atau CSV')

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
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Petunjuk Import</h3>
                    <div class="text-sm text-blue-700 space-y-2">
                        <p>• Format file yang didukung: Excel (.xlsx, .xls) dan CSV</p>
                        <p>• Pastikan data sesuai dengan format template yang tersedia</p>
                        <p>• Kode ruangan harus unik dan belum ada di sistem</p>
                        <p>• Kapasitas ruangan harus berupa angka (1-1000)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Manual Input Form -->
            <div class="bg-white rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fa-solid fa-keyboard text-indigo-600 mr-2"></i>
                        Input Manual
                    </h3>
                </div>

                <form action="{{ route('ruangan.import.process') }}" method="POST" class="p-6">
                    @csrf

                    <div id="ruangan-forms">
                        <div class="ruangan-form border rounded-lg p-4 mb-4 bg-gray-50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan *</label>
                                    <input type="text" name="ruangan_data[0][kode_ruangan]"
                                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan *</label>
                                    <input type="text" name="ruangan_data[0][nama_ruangan]"
                                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                                    <input type="number" name="ruangan_data[0][kapasitas]" min="1" max="1000"
                                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                                    <input type="text" name="ruangan_data[0][lokasi]"
                                        class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Ruangan</label>
                                    <select name="ruangan_data[0][jenis_ruangan]"
                                        class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="kelas">Kelas</option>
                                        <option value="laboratorium">Laboratorium</option>
                                        <option value="aula">Aula</option>
                                        <option value="perpustakaan">Perpustakaan</option>
                                        <option value="ruang_ujian">Ruang Ujian</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select name="ruangan_data[0][status]"
                                        class="w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="aktif">Aktif</option>
                                        <option value="perbaikan">Perbaikan</option>
                                        <option value="tidak_aktif">Tidak Aktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                <textarea name="ruangan_data[0][keterangan]" rows="2" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <button type="button" onclick="addRuanganForm()"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Form
                        </button>

                        <div class="space-x-2">
                            <a href="{{ route('ruangan.index') }}"
                                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                Batal
                            </a>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                                <i class="fa-solid fa-save mr-2"></i> Import Data
                            </button>
                        </div>
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
                            <a href="#"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-file-excel text-green-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Template Excel</div>
                                        <div class="text-sm text-gray-500">Format .xlsx</div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-download text-gray-400"></i>
                            </a>

                            <a href="#"
                                class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-file-csv text-blue-600 mr-3"></i>
                                    <div>
                                        <div class="font-medium text-gray-900">Template CSV</div>
                                        <div class="text-sm text-gray-500">Format .csv</div>
                                    </div>
                                </div>
                                <i class="fa-solid fa-download text-gray-400"></i>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-medium text-gray-900 mb-3">Format Data yang Diperlukan</h4>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b">
                                        <th class="text-left p-2">Field</th>
                                        <th class="text-left p-2">Wajib</th>
                                        <th class="text-left p-2">Contoh</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600">
                                    <tr class="border-b">
                                        <td class="p-2">kode_ruangan</td>
                                        <td class="p-2">Ya</td>
                                        <td class="p-2">R001</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-2">nama_ruangan</td>
                                        <td class="p-2">Ya</td>
                                        <td class="p-2">Ruang Kelas A</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-2">kapasitas</td>
                                        <td class="p-2">Ya</td>
                                        <td class="p-2">30</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-2">lokasi</td>
                                        <td class="p-2">Tidak</td>
                                        <td class="p-2">Lantai 1</td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="p-2">jenis_ruangan</td>
                                        <td class="p-2">Tidak</td>
                                        <td class="p-2">kelas</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2">status</td>
                                        <td class="p-2">Tidak</td>
                                        <td class="p-2">aktif</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex">
                            <i class="fa-solid fa-exclamation-triangle text-yellow-400 mr-2 mt-1"></i>
                            <div class="text-sm">
                                <p class="font-medium text-yellow-800 mb-1">Catatan Penting:</p>
                                <ul class="text-yellow-700 space-y-1">
                                    <li>• Pastikan kode ruangan unik</li>
                                    <li>• Kapasitas harus berupa angka 1-1000</li>
                                    <li>• Jenis ruangan: kelas, laboratorium, aula, perpustakaan, ruang_ujian</li>
                                    <li>• Status: aktif, perbaikan, tidak_aktif</li>
                                </ul>
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
        let formCount = 1;

        function addRuanganForm() {
            const formsContainer = document.getElementById('ruangan-forms');
            const newForm = document.createElement('div');
            newForm.className = 'ruangan-form border rounded-lg p-4 mb-4 bg-gray-50 relative';
            newForm.innerHTML = `
            <button type="button" onclick="removeForm(this)" 
                class="absolute top-2 right-2 text-red-600 hover:text-red-800">
                <i class="fa-solid fa-times"></i>
            </button>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Ruangan *</label>
                    <input type="text" name="ruangan_data[${formCount}][kode_ruangan]" 
                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ruangan *</label>
                    <input type="text" name="ruangan_data[${formCount}][nama_ruangan]" 
                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas *</label>
                    <input type="number" name="ruangan_data[${formCount}][kapasitas]" min="1" max="1000"
                        class="w-full rounded-md border-gray-300 shadow-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input type="text" name="ruangan_data[${formCount}][lokasi]" 
                        class="w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Ruangan</label>
                    <select name="ruangan_data[${formCount}][jenis_ruangan]" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="kelas">Kelas</option>
                        <option value="laboratorium">Laboratorium</option>
                        <option value="aula">Aula</option>
                        <option value="perpustakaan">Perpustakaan</option>
                        <option value="ruang_ujian">Ruang Ujian</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="ruangan_data[${formCount}][status]" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="aktif">Aktif</option>
                        <option value="perbaikan">Perbaikan</option>
                        <option value="tidak_aktif">Tidak Aktif</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                <textarea name="ruangan_data[${formCount}][keterangan]" rows="2" 
                    class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
            </div>
        `;

            formsContainer.appendChild(newForm);
            formCount++;
        }

        function removeForm(button) {
            const form = button.closest('.ruangan-form');
            form.remove();
        }
    </script>
@endsection
