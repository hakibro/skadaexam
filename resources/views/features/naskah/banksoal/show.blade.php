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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

            <!-- Informasi Bank Soal -->
            <div class="bg-white border border-slate-150 shadow-sm rounded-2xl overflow-hidden">

                <!-- Header -->
                <div
                    class="px-6 py-5 border-b border-slate-100 flex items-center justify-between flex-wrap gap-4 bg-slate-50/40">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 tracking-tight">Informasi Bank Soal</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Detail data dan konfigurasi paket soal aktif</p>
                    </div>
                    <div>
                        @if ($banksoal->status === 'aktif')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">Aktif</span>
                        @elseif ($banksoal->status === 'draft')
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-150 text-slate-600 ring-1 ring-slate-600/10">Draft</span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-600/10">Arsip</span>
                        @endif
                    </div>
                </div>

                <!-- Body Grid -->
                <div class="p-6 grid grid-cols-3 gap-y-5 gap-x-6">

                    <!-- Judul -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Judul</span>
                        <span class="text-base font-semibold text-slate-800 block">{{ $banksoal->judul }}</span>
                    </div>

                    <!-- Kode Bank -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Kode Bank</span>
                        <span
                            class="text-sm font-mono text-slate-700 bg-slate-50 px-2 py-1 rounded-md border border-slate-200/60 inline-block">
                            {{ $banksoal->kode_bank ?? 'Tidak ada kode bank' }}
                        </span>
                    </div>

                    <!-- Tingkat -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tingkat</span>
                        <span class="text-base font-medium text-slate-800 block">Kelas {{ $banksoal->tingkat }}</span>
                    </div>

                    <!-- Mata Pelajaran -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Mata Pelajaran</span>
                        <p class="text-base font-medium text-slate-800">
                            @if ($banksoal->mapel)
                                {{ $banksoal->mapel->nama_mapel }}
                            @else
                                <span class="text-slate-400 italic font-normal text-sm">Tidak ada</span>
                            @endif
                        </p>
                    </div>

                    <!-- Total Soal -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Soal</span>
                        <span class="text-base font-semibold text-slate-800 block">
                            {{ $banksoal->total_soal }} <span class="text-slate-400 font-normal text-sm">soal</span>
                        </span>
                    </div>

                    <!-- Tipe Soal Default -->
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Tipe Soal
                            Default</span>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-indigo-700/10">
                            {{ \App\Models\Soal::QUESTION_TYPES[$banksoal->tipe_soal_default] ?? ucfirst(str_replace('_', ' ', $banksoal->tipe_soal_default ?? 'pilihan_ganda')) }}
                        </span>
                    </div>

                    <!-- Paket Ujian -->
                    <div class=" space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Paket Ujian</span>
                        @if ($banksoal->paketUjian)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-purple-50 text-purple-700 ring-1 ring-purple-700/10">{{ $banksoal->paketUjian->nama }}</span>
                        @else
                            <span class="text-slate-400 italic text-sm">Tidak ada paket</span>
                        @endif
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-span-2 space-y-1 pt-4 border-t border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Deskripsi</span>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            {{ $banksoal->deskripsi ?? 'Tidak ada deskripsi untuk bank soal ini.' }}</p>
                    </div>

                    <!-- File Sumber -->
                    @if (isset($banksoal->pengaturan['source_file']))
                        <div
                            class="col-span-2 flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-150 hover:bg-slate-100/70 transition-all">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="p-2 bg-white rounded-lg border border-slate-200 text-slate-500 shadow-sm">
                                    <i class="fa-solid fa-file-lines text-sm"></i>
                                </div>
                                <div class="truncate">
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">File
                                        Sumber</span>
                                    <span
                                        class="text-sm font-medium text-slate-700 truncate block">{{ $banksoal->pengaturan['source_file'] }}</span>
                                </div>
                            </div>
                            <a href="{{ asset('storage/bank-soal/sources/' . $banksoal->pengaturan['source_file']) }}"
                                target="_blank"
                                class="text-xs font-semibold text-slate-700 bg-white hover:text-blue-600 px-3 py-2 rounded-lg border border-slate-200 shadow-sm transition-colors inline-flex items-center shrink-0">
                                <i class="fa-solid fa-download mr-1.5"></i> Unduh
                            </a>
                        </div>
                    @endif

                    <!-- Timestamps -->
                    <div
                        class="col-span-2 grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 text-[11px] text-slate-400 font-medium">
                        <div>Dibuat: {{ $banksoal->created_at->format('d M Y, H:i') }}</div>
                        <div class="text-right">Diperbarui: {{ $banksoal->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Import Soal -->
            <div class="bg-white border border-slate-150 shadow-sm rounded-2xl overflow-hidden">

                <!-- Header -->
                <div class="flex px-6 py-5 border-b border-slate-100 bg-slate-50/40">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-lg font-bold text-slate-800 tracking-tight">Import Soal</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Unggah file DOCX atau gunakan template Excel</p>
                    </div>
                    <!-- Links dipindah ke bawah dalam kolom kanan agar rapi -->
                    <div class="flex flex-wrap gap-3 ml-2 pl-4 border-l border-blue-200">
                        <a href="{{ asset('templates/template_import_soal.docx') }}"
                            class="text-blue-600 hover:text-blue-800 inline-flex items-center text-xs font-medium gap-1">
                            <i class="fa-solid fa-file-download"></i> Template DOCX
                        </a>
                        <a href="{{ route('naskah.soal.import.template') }}"
                            class="text-emerald-600 hover:text-emerald-800 inline-flex items-center text-xs font-medium gap-1">
                            <i class="fa-solid fa-file-excel"></i> Template Excel
                        </a>
                        <a href="{{ route('naskah.panduan.format-docx') }}" target="_blank"
                            class="text-slate-500 hover:text-slate-700 inline-flex items-center text-xs font-medium gap-1">
                            <i class="fa-solid fa-circle-info"></i> Tutorial Format
                        </a>
                    </div>
                </div>

                <div class="p-6 space-y-5">
                    <!-- Upload Form + Format Guide bersebelahan -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kolom Kiri: Upload Form -->
                        <form action="{{ route('naskah.banksoal.update', $banksoal) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="judul" value="{{ $banksoal->judul }}">
                            <input type="hidden" name="deskripsi" value="{{ $banksoal->deskripsi }}">
                            <input type="hidden" name="tingkat" value="{{ $banksoal->tingkat }}">
                            <input type="hidden" name="status" value="{{ $banksoal->status }}">
                            <input type="hidden" name="mapel_id" value="{{ $banksoal->mapel_id }}">
                            <input type="hidden" name="paket_ujian_id" value="{{ $banksoal->paket_ujian_id }}">

                            <!-- Dropzone -->
                            <div
                                class="flex flex-col items-center text-center p-6 border-2 border-dashed border-slate-200 rounded-xl hover:bg-slate-50 transition-colors relative cursor-pointer">
                                <i class="fa-solid fa-file-import text-2xl text-slate-400 mb-2"></i>
                                <span class="text-sm font-semibold text-slate-700">Unggah file DOCX</span>
                                <span class="text-xs text-slate-400 mt-1">Maksimum 10MB</span>
                                <input type="file" name="docx_file" id="docx_file" accept=".docx"
                                    class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"
                                    onchange="updateFileName()">
                            </div>
                            <p id="file-name" class="mt-2 text-xs text-center text-slate-500 truncate">Tidak ada file
                                dipilih</p>

                            <button type="submit"
                                class="mt-4 w-full inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold rounded-xl text-white bg-blue-600 hover:bg-blue-700 transition-colors shadow-sm">
                                <i class="fa-solid fa-file-import mr-2"></i> Import Soal
                            </button>
                        </form>

                        <!-- Kolom Kanan: Format Guide -->
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-800 mb-2">Format Template
                                </h4>
                                <ul class="list-disc text-xs text-blue-700 pl-4 space-y-1.5">
                                    <li>Soal dimulai dengan nomor dan titik — <strong>1. Soal pertama</strong></li>
                                    <li>Opsi jawaban huruf kapital dan titik — <strong>A. Pilihan A</strong></li>
                                    <li>Kunci jawaban tandai <strong>[*]</strong> di akhir — <strong>C. Pilihan C
                                            [*]</strong></li>
                                    <li>Opsi gambar tulis kosong — <strong>B.</strong> lalu sisipkan gambar</li>
                                    <li>Pembahasan diawali — <strong>Pembahasan: ...</strong></li>
                                    <li>Gambar dalam soal/opsi/pembahasan dideteksi otomatis</li>
                                </ul>
                            </div>

                        </div>
                    </div>

                    <!-- (Opsional) Jika masih ingin menampilkan link di bawah juga, silakan, tapi biasanya sudah cukup di kolom kanan -->
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
                                            {{ Str::limit(strip_tags($soal->pertanyaan_html), 100) }}
                                            @if ($soal->tipe_pertanyaan == 'teks_gambar' || $soal->tipe_pertanyaan == 'gambar')
                                                <span class="ml-1 text-xs text-blue-600">[Ada gambar]</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $soal->tipe_soal_label }}
                                        </span>
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
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ \Illuminate\Support\Str::limit($soal->kunci_jawaban_label, 80) }}
                                        </span>
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
