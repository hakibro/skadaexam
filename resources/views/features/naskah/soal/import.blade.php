@extends('layouts.admin')

@section('title', 'Import Soal')
@section('page-title', 'Import Soal')
@section('page-description', 'Impor soal dari file atau format lain')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('naskah.soal.store') }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Import Soal</h2>

                <!-- Pilih Bank Soal -->
                <div>
                    <label for="bank_soal_id" class="block text-sm font-medium text-gray-700">Bank Soal <span
                            class="text-red-500">*</span></label>
                    <select name="bank_soal_id" id="bank_soal_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="">Pilih Bank Soal</option>
                        @foreach ($bankSoals as $bankSoal)
                            <option value="{{ $bankSoal->id }}">{{ $bankSoal->judul }}</option>
                        @endforeach
                    </select>
                    @error('bank_soal_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Upload File -->
                <div class="mt-6">
                    <label for="docx_file" class="block text-sm font-medium text-gray-700">Upload File .docx</label>
                    <input type="file" name="docx_file" id="docx_file" accept=".docx"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="mt-1 text-xs text-gray-500">Pastikan format dokumen sesuai dengan template yang ditentukan.
                    </p>
                    @error('docx_file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Panduan Format Dokumen -->
                <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-info text-yellow-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Panduan Format Dokumen</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Setiap soal dimulai dengan nomor diikuti tanda titik (1., 2., dst)</li>
                                    <li>Jawaban pilihan ganda ditulis dengan format A., B., C., dst</li>
                                    <li>Tandai jawaban benar dengan [*] setelah pilihan (contoh: A. Jawaban benar [*])</li>
                                    <li>Untuk pilihan jawaban berupa gambar, tulis pilihan kosong (contoh: B.)</li>
                                    <li><strong>Penting:</strong> Gambar harus ditambahkan secara manual setelah soal
                                        diimpor (sistem tidak mengekstrak gambar dari DOCX)</li>
                                    <li><strong>Baru:</strong> Pembahasan diawali dengan kata "Pembahasan:" (contoh:
                                        Pembahasan: Jawaban A benar karena...)</li>
                                </ul>
                                <div class="mt-3">
                                    <a href="{{ route('panduan.format-docx') }}"
                                        class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                        <i class="fa-solid fa-file-word mr-1"></i>
                                        Lihat Panduan Lengkap Format DOCX
                                        <i class="fa-solid fa-arrow-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('naskah.soal.index') }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Import Soal
                </button>
            </div>
        </form>
    </div>
@endsection
