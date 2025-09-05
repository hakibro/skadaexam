@extends('layouts.admin')

@section('title', 'Panduan Format DOCX')
@section('page-title', 'Panduan Format DOCX')
@section('page-description', 'Cara membuat file DOCX untuk impor soal')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8">
        <!-- Intro Card -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white">Panduan Impor Soal dari DOCX</h2>
                <p class="mt-1 text-blue-100">Ikuti panduan ini untuk membuat file DOCX yang dapat diimpor dengan benar</p>
            </div>
            <div class="p-6">
                <div class="prose max-w-none">
                    <p>Skada Exam mendukung impor soal dari file Microsoft Word (.docx). Dengan fitur ini, Anda dapat
                        membuat soal menggunakan Microsoft Word dengan format yang ditentukan, lalu mengimpornya ke sistem
                        dengan mudah.</p>

                    <p>Dokumen DOCX harus mengikuti format tertentu agar sistem dapat mengenali soal, pilihan jawaban, kunci
                        jawaban, dan pembahasan dengan benar.</p>
                </div>
            </div>
        </div>

        <!-- Basic Format -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Format Dasar</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-blue-500 text-white">
                            <i class="fa-solid fa-list-ol"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Format Soal</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p>Soal harus dimulai dengan nomor diikuti titik dan spasi.</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                1. Siapakah presiden pertama Indonesia?
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-blue-500 text-white">
                            <i class="fa-solid fa-list-ul"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Format Pilihan Jawaban</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p>Pilihan jawaban harus diformat dengan huruf kapital diikuti titik. Kunci jawaban ditandai
                                dengan [*] di akhir.</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                A. Soekarno [*]<br>
                                B. Soeharto<br>
                                C. BJ Habibie<br>
                                D. Megawati Soekarnoputri<br>
                                E. Susilo Bambang Yudhoyono
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-blue-500 text-white">
                            <i class="fa-solid fa-comment-dots"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Format Pembahasan</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p>Pembahasan soal diawali dengan "Pembahasan:".</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                Pembahasan: Presiden pertama Indonesia adalah Ir. Soekarno yang menjabat pada tahun
                                1945-1967.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Format -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Soal dengan Gambar</h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-500 text-white">
                            <i class="fa-solid fa-image"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Soal dengan Gambar</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p><strong>PENTING:</strong> Gambar harus ditambahkan secara manual setelah soal diimpor
                                menggunakan fitur Edit Soal. Sistem tidak akan mengekstrak gambar dari file DOCX.</p>
                            <p class="mt-2">Format teks untuk soal yang akan memiliki gambar:</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                1. Perhatikan gambar berikut. Bangunan ini terletak di kota...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-500 text-white">
                            <i class="fa-solid fa-images"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Pilihan Jawaban Berupa Gambar</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p><strong>PENTING:</strong> Untuk pilihan berupa gambar, tulis opsi kosong (misal: B.) sebagai
                                penanda bahwa pilihan ini akan berisi gambar. Gambar harus ditambahkan secara manual setelah
                                soal diimpor.</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                A. Jakarta<br>
                                B.<br>
                                C. Surabaya<br>
                                D. Bandung [*]
                            </div>
                            <p class="mt-2 text-yellow-600">Pilihan B kosong akan ditandai sebagai pilihan yang memerlukan
                                gambar dan akan otomatis diisi dengan "[Perlu Tambahkan Gambar Secara Manual]" saat impor.
                                <strong>Perhatian:</strong> Fitur ekstraksi gambar telah dinonaktifkan, sehingga semua
                                gambar harus ditambahkan secara manual setelah impor.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Format Item -->
                <div class="flex">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-10 w-10 rounded-md bg-indigo-500 text-white">
                            <i class="fa-solid fa-photo-film"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-medium text-gray-900">Pembahasan dengan Gambar</h4>
                        <div class="mt-2 text-sm text-gray-500">
                            <p><strong>PENTING:</strong> Gambar untuk pembahasan juga harus ditambahkan secara manual
                                setelah soal diimpor.</p>
                            <div class="mt-3 bg-gray-50 rounded-md p-3 font-mono text-sm">
                                Pembahasan: Bangunan tersebut adalah Gedung Sate yang merupakan ikon kota Bandung.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Example -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Contoh Lengkap</h3>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 rounded-md p-6 font-mono text-sm whitespace-pre-line">
                    1. Apa ibukota Indonesia?
                    A. Jakarta [*]
                    B. Bandung
                    C. Surabaya
                    D. Medan
                    E. Makassar
                    Pembahasan: Jakarta adalah ibukota Indonesia yang terletak di pulau Jawa.

                    2. Perhatikan gambar berikut. Hewan ini termasuk dalam famili?
                    [GAMBAR HEWAN]
                    A. Felidae [*]
                    B. Canidae
                    C. Ursidae
                    D. Bovidae
                    E.
                    [GAMBAR]
                    Pembahasan: Hewan tersebut adalah kucing yang termasuk dalam famili Felidae.
                </div>

                <div class="mt-6">
                    <a href="{{ asset('templates/template_import_soal.docx') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fa-solid fa-download mr-2"></i>
                        Download Template Contoh
                    </a>
                </div>
            </div>
        </div>

        <!-- Tips & Troubleshooting -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Tips & Pemecahan Masalah</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-blue-50 rounded-lg p-5">
                        <h4 class="font-medium text-blue-800 mb-3">Tips Impor</h4>
                        <ul class="space-y-2 text-sm text-blue-700">
                            <li class="flex">
                                <i class="fa-solid fa-check-circle mt-1 mr-2"></i>
                                <span>Gunakan template yang disediakan</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-check-circle mt-1 mr-2"></i>
                                <span>Kompres gambar untuk mempercepat proses</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-check-circle mt-1 mr-2"></i>
                                <span>Pastikan nomor soal berurutan</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-check-circle mt-1 mr-2"></i>
                                <span>Jangan gunakan tabel atau header/footer</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-check-circle mt-1 mr-2"></i>
                                <span>Simpan file sebagai .docx (bukan .doc)</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-red-50 rounded-lg p-5">
                        <h4 class="font-medium text-red-800 mb-3">Pemecahan Masalah</h4>
                        <ul class="space-y-2 text-sm text-red-700">
                            <li class="flex">
                                <i class="fa-solid fa-exclamation-circle mt-1 mr-2"></i>
                                <span>Jika soal tidak terimpor, periksa format penomoran</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-exclamation-circle mt-1 mr-2"></i>
                                <span>Jika gambar tidak muncul, pastikan gambar tertanam dalam dokumen</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-exclamation-circle mt-1 mr-2"></i>
                                <span>Jika kunci jawaban tidak terdeteksi, periksa format [*]</span>
                            </li>
                            <li class="flex">
                                <i class="fa-solid fa-exclamation-circle mt-1 mr-2"></i>
                                <span>File terlalu besar? Bagi menjadi beberapa file yang lebih kecil</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
