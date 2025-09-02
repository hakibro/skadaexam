@extends('layouts.admin')

@section('title', 'Detail Bank Soal')
@section('page-title', 'Detail Bank Soal')
@section('page-description', 'Informasi lengkap dan soal dalam bank soal')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                                class="inline-flex rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-600">
                                <span class="sr-only">Dismiss</span>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                                class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                                <span class="sr-only">Dismiss</span>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Terjadi kesalahan:</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button"
                                onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                                class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600">
                                <span class="sr-only">Dismiss</span>
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('naskah.banksoal.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 active:bg-gray-300 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('naskah.banksoal.edit', $banksoal) }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <i class="fa-solid fa-edit mr-2"></i> Edit
                </a>
                <form action="{{ route('naskah.banksoal.destroy', $banksoal) }}" method="POST" class="inline"
                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus bank soal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                        <i class="fa-solid fa-trash mr-2"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Bank Soal Info -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-5">
                <h3 class="text-lg font-medium text-gray-900">Informasi Bank Soal</h3>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Judul</h4>
                    <p class="text-base text-gray-900">{{ $banksoal->judul }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Kode Bank</h4>
                    <p class="text-base text-gray-900">
                        <span class="font-mono">{{ $banksoal->kode_bank ?? 'Tidak ada kode bank' }}</span>
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Status</h4>
                    <p>
                        @if ($banksoal->status === 'aktif')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @elseif ($banksoal->status === 'draft')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Draft
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Arsip
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Tingkat</h4>
                    <p class="text-base text-gray-900">Kelas {{ $banksoal->tingkat }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Soal</h4>
                    <p class="text-base text-gray-900">{{ $banksoal->total_soal }} soal</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Tipe Soal</h4>
                    <p class="text-base text-gray-900">
                        @if ($banksoal->jenis_soal === 'pilihan_ganda')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Pilihan Ganda
                            </span>
                        @elseif($banksoal->jenis_soal === 'essay')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Essay
                            </span>
                        @elseif($banksoal->jenis_soal === 'campuran')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                Campuran
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($banksoal->jenis_soal ?? 'Tidak Ditentukan') }}
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Jenis Soal</h4>
                    <p class="text-base text-gray-900">
                        @if ($banksoal->jenis_soal === 'uts')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                UTS
                            </span>
                        @elseif($banksoal->jenis_soal === 'uas')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                UAS
                            </span>
                        @elseif($banksoal->jenis_soal === 'ulangan')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Ulangan
                            </span>
                        @elseif($banksoal->jenis_soal === 'latihan')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Latihan
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ ucfirst($banksoal->jenis_soal ?? 'Tidak ada') }}
                            </span>
                        @endif
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Mata Pelajaran</h4>
                    <p class="text-base text-gray-900">
                        @if ($banksoal->mapel)
                            {{ $banksoal->mapel->nama }}
                        @else
                            <span class="text-gray-500 italic">Tidak ada</span>
                        @endif
                    </p>
                </div>

                <div class="md:col-span-2">
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Deskripsi</h4>
                    <p class="text-base text-gray-900">{{ $banksoal->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Dibuat pada</h4>
                    <p class="text-base text-gray-900">{{ $banksoal->created_at->format('d M Y, H:i') }}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Terakhir diupdate</h4>
                    <p class="text-base text-gray-900">{{ $banksoal->updated_at->format('d M Y, H:i') }}</p>
                </div>

                @if (isset($banksoal->pengaturan['source_file']))
                    <div class="md:col-span-2">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">File Sumber</h4>
                        <p class="text-base text-gray-900">
                            <a href="{{ asset('storage/bank-soal/sources/' . $banksoal->pengaturan['source_file']) }}"
                                target="_blank" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                                <i class="fa-solid fa-download mr-2"></i> {{ $banksoal->pengaturan['source_file'] }}
                            </a>
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Import Controls -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-medium text-gray-900">Import Soal</h3>
            </div>
            <div class="px-6 py-4">
                <div class="flex flex-col md:flex-row md:items-start">
                    <div class="flex-1 mb-4 md:mb-0 md:mr-6">
                        <p class="text-sm text-gray-600 mb-2">
                            Import soal dari file dokumen (.docx) ke dalam bank soal ini.
                        </p>

                        <div class="flex space-x-4 mb-3">
                            <a href="{{ asset('templates/template_import_soal.docx') }}"
                                class="text-blue-600 hover:text-blue-800 inline-flex items-center text-sm">
                                <i class="fa-solid fa-file-download mr-1"></i> Download Template Impor
                            </a>

                            <a href="{{ route('panduan.format-docx') }}" target="_blank"
                                class="text-blue-600 hover:text-blue-800 inline-flex items-center text-sm">
                                <i class="fa-solid fa-circle-info mr-1"></i> Tutorial Format Soal
                            </a>
                        </div>

                        <div class="rounded-md bg-blue-50 p-3 mt-3">
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-blue-800 mb-2">Format Template:
                            </h4>
                            <ul class="list-disc text-xs text-blue-700 pl-5 space-y-1">
                                <li>Soal harus dimulai dengan nomor diikuti titik dan spasi (contoh: <strong>1. Soal
                                        pertama</strong>)</li>
                                <li>Opsi jawaban harus diformat dengan huruf kapital diikuti titik (contoh: <strong>A.
                                        Pilihan A</strong>)</li>
                                <li>Untuk kunci jawaban, tambahkan tanda [*] di akhir (contoh: <strong>C. Pilihan C
                                        [*]</strong>)</li>
                                <li>Untuk opsi jawaban berupa gambar, tulis opsi kosong (contoh: <strong>B.</strong>) dan
                                    masukkan gambar setelahnya</li>
                                <li>Pembahasan soal diawali dengan "Pembahasan:" (contoh: <strong>Pembahasan: Penjelasan
                                        jawaban</strong>)</li>
                                <li>Gambar dalam soal, opsi jawaban, atau pembahasan akan otomatis dideteksi</li>
                            </ul>
                        </div>
                    </div>

                    <div class="md:w-64">
                        <form action="{{ route('naskah.banksoal.update', $banksoal) }}" method="POST"
                            enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-lg p-4">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="judul" value="{{ $banksoal->judul }}">
                            <input type="hidden" name="deskripsi" value="{{ $banksoal->deskripsi }}">
                            <input type="hidden" name="tingkat" value="{{ $banksoal->tingkat }}">
                            <input type="hidden" name="status" value="{{ $banksoal->status }}">
                            <input type="hidden" name="mapel_id" value="{{ $banksoal->mapel_id }}">
                            <input type="hidden" name="jenis_soal" value="{{ $banksoal->jenis_soal }}">

                            <div class="mb-4">
                                <div
                                    class="flex flex-col items-center text-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-50 transition-colors relative">
                                    <i class="fa-solid fa-file-import text-2xl text-gray-400 mb-2"></i>
                                    <span class="block text-sm font-medium text-gray-700">
                                        Unggah file DOCX
                                    </span>
                                    <span class="block text-xs text-gray-500 mt-1">
                                        Maksimum 10MB
                                    </span>
                                    <input type="file" name="docx_file" id="docx_file" accept=".docx"
                                        class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                        onchange="updateFileName()">
                                </div>
                                <p id="file-name" class="mt-2 text-sm text-center text-gray-600 truncate">
                                    Tidak ada file dipilih
                                </p>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fa-solid fa-file-import mr-2"></i> Import Soal
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                function updateFileName() {
                    const fileInput = document.getElementById('docx_file');
                    const fileNameElem = document.getElementById('file-name');
                    if (fileInput.files.length > 0) {
                        const fileName = fileInput.files[0].name;
                        const fileSize = (fileInput.files[0].size / 1024).toFixed(1);
                        fileNameElem.textContent = `${fileName} (${fileSize} KB)`;
                        fileNameElem.classList.add('text-blue-600');
                    } else {
                        fileNameElem.textContent = 'Tidak ada file dipilih';
                        fileNameElem.classList.remove('text-blue-600');
                    }
                }
            </script>
        </div>

        <!-- Soal List -->
        <div id="soal-list" class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Daftar Soal</h3>
                <a href="{{ route('naskah.soal.create', ['bank_soal_id' => $banksoal->id]) }}"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fa-solid fa-plus mr-2"></i> Tambah Soal
                </a>
            </div>

            @if ($banksoal->soals->count() > 0)
                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <button id="tab-table" onclick="showTab('table')"
                                class="inline-block p-4 border-b-2 border-blue-600 rounded-t-lg active text-blue-600">
                                <i class="fa-solid fa-table-list mr-2"></i> Tabel
                            </button>
                        </li>
                        <li class="mr-2">
                            <button id="tab-cards" onclick="showTab('cards')"
                                class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300">
                                <i class="fa-solid fa-table-cells mr-2"></i> Preview
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Table View -->
                <div id="view-table" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No.</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pertanyaan</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Format</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jawaban Benar</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($banksoal->soals as $soal)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $soal->nomor_soal }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-md overflow-hidden text-ellipsis">
                                            {!! Str::limit($soal->pertanyaan, 100) !!}
                                            @if ($soal->tipe_pertanyaan == 'teks_gambar' || $soal->tipe_pertanyaan == 'gambar')
                                                <span class="ml-1 text-xs text-blue-600">[Ada gambar]</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($soal->tipe_soal == 'pilihan_ganda')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Pilihan Ganda
                                            </span>
                                        @elseif($soal->tipe_soal == 'essay')
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Essay
                                            </span>
                                        @else
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ ucfirst($soal->tipe_soal) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($soal->tipe_pertanyaan == 'teks')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Teks
                                            </span>
                                        @elseif($soal->tipe_pertanyaan == 'gambar')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Gambar
                                            </span>
                                        @elseif($soal->tipe_pertanyaan == 'teks_gambar')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                Teks & Gambar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($soal->tipe_soal == 'pilihan_ganda')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $soal->kunci_jawaban }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2 justify-end">
                                            <a href="{{ route('naskah.soal.show', $soal) }}"
                                                class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('naskah.soal.edit', $soal) }}"
                                                class="text-yellow-600 hover:text-yellow-900" title="Edit Soal">
                                                <i class="fa-solid fa-edit"></i>
                                            </a>
                                            <form action="{{ route('naskah.soal.destroy', $soal) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Hapus Soal">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Cards View -->
                <div id="view-cards" class="hidden">
                    @foreach ($banksoal->soals as $soal)
                        <x-soal-card :soal="$soal" />
                    @endforeach
                </div>

                <script>
                    function showTab(tabName) {
                        // Hide all views
                        document.getElementById('view-table').classList.add('hidden');
                        document.getElementById('view-cards').classList.add('hidden');

                        // Remove active class from all tabs
                        document.getElementById('tab-table').classList.remove('border-blue-600', 'text-blue-600');
                        document.getElementById('tab-table').classList.add('border-transparent');
                        document.getElementById('tab-cards').classList.remove('border-blue-600', 'text-blue-600');
                        document.getElementById('tab-cards').classList.add('border-transparent');

                        // Show selected view and activate tab
                        if (tabName === 'table') {
                            document.getElementById('view-table').classList.remove('hidden');
                            document.getElementById('tab-table').classList.add('border-blue-600', 'text-blue-600');
                            document.getElementById('tab-table').classList.remove('border-transparent');
                        } else {
                            document.getElementById('view-cards').classList.remove('hidden');
                            document.getElementById('tab-cards').classList.add('border-blue-600', 'text-blue-600');
                            document.getElementById('tab-cards').classList.remove('border-transparent');
                        }

                        // Save preference to localStorage
                        localStorage.setItem('soalViewPreference', tabName);
                    }

                    // Check if there's a saved preference
                    document.addEventListener('DOMContentLoaded', function() {
                        const savedPreference = localStorage.getItem('soalViewPreference');
                        if (savedPreference) {
                            showTab(savedPreference);
                        }
                    });
                </script>
            @else
                <div class="p-6 text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">
                        <i class="fa-solid fa-file-circle-question text-2xl"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada soal</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Anda dapat menambahkan soal baru atau mengimpor dari file DOCX.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('naskah.soal.create', ['bank_soal_id' => $banksoal->id]) }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fa-solid fa-plus mr-2"></i> Tambah Soal Baru
                        </a>
                    </div>
                </div>
            @endif
        </div>

        @if (isset($banksoal->pengaturan['import_log']) && !empty($banksoal->pengaturan['import_log']))
            <!-- Import Logs -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-medium text-gray-900">Log Import Terakhir</h3>
                </div>
                <div class="px-6 py-4">
                    @if (!empty($banksoal->pengaturan['import_log']['errors']))
                        <div class="rounded-md bg-red-50 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-red-400"></i>
                                </div>
                                <div class="ml-3 flex-1">
                                    <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan dalam proses import
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($banksoal->pengaturan['import_log']['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="mt-3">
                                        <p class="text-sm text-red-700">
                                            Tips: Pastikan file DOCX yang diupload mengikuti format template yang
                                            disediakan.
                                            Jika masalah berlanjut, periksa log aplikasi untuk informasi lebih detail.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div
                        class="rounded-md {{ empty($banksoal->pengaturan['import_log']['errors']) ? 'bg-blue-50' : 'bg-gray-50' }} p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i
                                    class="fa-solid fa-circle-info {{ empty($banksoal->pengaturan['import_log']['errors']) ? 'text-blue-400' : 'text-gray-400' }}"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3
                                    class="text-sm font-medium {{ empty($banksoal->pengaturan['import_log']['errors']) ? 'text-blue-800' : 'text-gray-800' }}">
                                    Import pada
                                    {{ \Carbon\Carbon::parse($banksoal->pengaturan['import_log']['timestamp'])->format('d M Y, H:i') }}
                                </h3>
                                <div
                                    class="mt-2 text-sm {{ empty($banksoal->pengaturan['import_log']['errors']) ? 'text-blue-700' : 'text-gray-700' }}">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Soal berhasil diimpor: {{ $banksoal->pengaturan['import_log']['imported'] }}
                                        </li>
                                        <li>Soal dilewati: {{ $banksoal->pengaturan['import_log']['skipped'] }}</li>
                                        <li>Berkas sumber: {{ $banksoal->pengaturan['source_file'] ?? 'Tidak ada' }}</li>
                                    </ul>
                                </div>

                                @if ($banksoal->pengaturan['import_log']['imported'] > 0)
                                    <div class="mt-4">
                                        <a href="#soal-list"
                                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none">
                                            <i class="fa-solid fa-list-check mr-2"></i> Lihat Soal Terimport
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Memastikan fungsi updateFileName sudah berjalan dengan benar
            updateFileName();
        });
    </script>
@endsection
