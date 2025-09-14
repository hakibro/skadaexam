@php
    $title = 'Menjalankan Perbaikan Jawaban Siswa';
@endphp

<x-layouts.app :title="$title">
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>

            <div class="mt-6 bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <div class="mb-4">
                        <p class="mb-2">Perbaikan yang akan dijalankan:</p>
                        <ol class="list-decimal list-inside space-y-1 text-sm">
                            <li>Memperbaiki skema database untuk tabel <code>jawaban_siswa</code></li>
                            <li>Memastikan foreign key relasi mengacu ke tabel yang benar</li>
                            <li>Memperbaiki masalah pengumpulan ujian pada SiswaDashboardController</li>
                        </ol>
                    </div>

                    <form action="{{ route('maintenance.run-fix') }}" method="post">
                        @csrf
                        <button type="submit"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Jalankan Migrasi Perbaikan
                        </button>
                    </form>

                    @if (session('message'))
                        <div
                            class="mt-4 p-4 {{ session('success') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} rounded-md">
                            {{ session('message') }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-4">Detail Perubahan</h2>

                    <div class="space-y-4">
                        <div class="bg-gray-50 p-3 rounded">
                            <h3 class="font-semibold text-sm mb-1">1. Perbaikan pada SiswaDashboardController</h3>
                            <p class="text-xs">Memperbaiki penanganan error pada method submitExam dan peningkatan error
                                handling</p>
                        </div>

                        <div class="bg-gray-50 p-3 rounded">
                            <h3 class="font-semibold text-sm mb-1">2. Perbaikan pada Model JawabanSiswa</h3>
                            <p class="text-xs">Memperjelas relasi dengan model SoalUjian dan memastikan penggunaan nama
                                tabel yang benar</p>
                        </div>

                        <div class="bg-gray-50 p-3 rounded">
                            <h3 class="font-semibold text-sm mb-1">3. Migrasi untuk Skema Database</h3>
                            <p class="text-xs">Membuat migrasi untuk memperbaiki foreign key dan memastikan konsistensi
                                skema database</p>
                        </div>

                        <div class="bg-gray-50 p-3 rounded">
                            <h3 class="font-semibold text-sm mb-1">4. Perbaikan Data Hasil Ujian</h3>
                            <p class="text-xs">Memperbaiki data hasil ujian yang memiliki nilai null atau 0 pada kolom
                                penting</p>
                            <a href="/fix-hasil-ujian.php" target="_blank"
                                class="mt-2 inline-block text-xs text-blue-600 hover:underline">
                                Jalankan Perbaikan Data Hasil Ujian &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
