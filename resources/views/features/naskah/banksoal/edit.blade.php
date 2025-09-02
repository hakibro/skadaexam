@extends('layouts.admin')

@section('title', 'Edit Bank Soal')
@section('page-title', 'Edit Bank Soal')
@section('page-description', 'Perbarui informasi bank soal')

@section('content')
    <div class="max-w-3xl mx-auto">
        <form action="{{ route('naskah.banksoal.update', $banksoal) }}" method="POST" enctype="multipart/form-data"
            class="bg-white shadow-md rounded-lg overflow-hidden">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <h2 class="text-lg font-medium text-gray-900">Informasi Bank Soal</h2>

                <!-- Judul -->
                <div>
                    <label for="judul" class="block text-sm font-medium text-gray-700">Judul Bank Soal <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="judul"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('judul', $banksoal->judul) }}" required>
                    @error('judul')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="deskripsi" id="deskripsi" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('deskripsi', $banksoal->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mata Pelajaran -->
                    <div>
                        <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran <span
                                class="text-red-500">*</span></label>
                        <select name="mapel_id" id="mapel_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}"
                                    {{ old('mapel_id', $banksoal->mapel_id) == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})
                                </option>
                            @endforeach
                        </select>
                        @error('mapel_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tingkat -->
                    <div>
                        <label for="tingkat" class="block text-sm font-medium text-gray-700">Tingkat Kelas <span
                                class="text-red-500">*</span></label>
                        <select name="tingkat" id="tingkat"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                            <option value="X" {{ old('tingkat', $banksoal->tingkat) == 'X' ? 'selected' : '' }}>Kelas
                                X</option>
                            <option value="XI" {{ old('tingkat', $banksoal->tingkat) == 'XI' ? 'selected' : '' }}>Kelas
                                XI</option>
                            <option value="XII" {{ old('tingkat', $banksoal->tingkat) == 'XII' ? 'selected' : '' }}>
                                Kelas XII</option>
                        </select>
                        @error('tingkat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status <span
                            class="text-red-500">*</span></label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required>
                        <option value="draft" {{ old('status', $banksoal->status) == 'draft' ? 'selected' : '' }}>Draft
                        </option>
                        <option value="aktif" {{ old('status', $banksoal->status) == 'aktif' ? 'selected' : '' }}>Aktif
                        </option>
                        <option value="arsip" {{ old('status', $banksoal->status) == 'arsip' ? 'selected' : '' }}>Arsip
                        </option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Soal -->
                <div>
                    <label for="jenis_soal" class="block text-sm font-medium text-gray-700">Jenis Soal</label>
                    <select name="jenis_soal" id="jenis_soal"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="uts" {{ old('jenis_soal', $banksoal->jenis_soal) == 'uts' ? 'selected' : '' }}>
                            UTS</option>
                        <option value="uas" {{ old('jenis_soal', $banksoal->jenis_soal) == 'uas' ? 'selected' : '' }}>
                            UAS</option>
                        <option value="ulangan"
                            {{ old('jenis_soal', $banksoal->jenis_soal) == 'ulangan' ? 'selected' : '' }}>Ulangan</option>
                        <option value="latihan"
                            {{ old('jenis_soal', $banksoal->jenis_soal) == 'latihan' ? 'selected' : '' }}>Latihan</option>
                    </select>
                    @error('jenis_soal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informasi tambahan -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Soal: <span
                                    class="font-semibold">{{ $banksoal->total_soal }}</span></p>
                            <p class="text-sm text-gray-500">Dibuat oleh: <span
                                    class="font-semibold">{{ $banksoal->creator->name ?? 'Unknown' }}</span></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Dibuat pada: <span
                                    class="font-semibold">{{ $banksoal->created_at->format('d M Y H:i') }}</span></p>
                            <p class="text-sm text-gray-500">Terakhir diperbarui: <span
                                    class="font-semibold">{{ $banksoal->updated_at->format('d M Y H:i') }}</span></p>
                        </div>
                    </div>
                </div>

                <!-- Import Soal dari File -->
                <div class="mt-6">
                    <h3 class="text-md font-medium text-gray-900">Import Soal dari File</h3>
                    <p class="text-sm text-gray-500 mb-3">Format file yang didukung: .docx</p>

                    <div class="mt-2">
                        <label for="docx_file" class="block text-sm font-medium text-gray-700">Upload File .docx</label>
                        <input type="file" name="docx_file" id="docx_file" accept=".docx"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0 file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-xs text-gray-500">Pastikan format dokumen sesuai dengan template yang
                            ditentukan.</p>
                        @error('docx_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                                    <li>Gambar akan otomatis dideteksi dan disimpan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 text-right">
                <a href="{{ route('naskah.banksoal.show', $banksoal) }}"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </a>
                <button type="submit"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        <!-- Daftar Soal Section -->
        <div class="mt-8 bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Daftar Soal</h2>
                    <a href="{{ route('naskah.soal.create', ['bank_soal_id' => $banksoal->id]) }}"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                        <i class="fa-solid fa-plus mr-1"></i>Tambah Soal Baru
                    </a>
                </div>
                <p class="mt-1 text-sm text-gray-500">
                    Total <span class="font-medium">{{ $banksoal->total_soal }}</span> soal dalam bank soal ini
                </p>
            </div>

            @if (isset($soals) && count($soals) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No. Soal
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pertanyaan
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kunci Jawaban
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($soals as $soal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $soal->nomor_soal }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="max-w-xs">
                                            @if ($soal->tipe_pertanyaan === 'teks')
                                                <div class="text-sm text-gray-900">
                                                    {{ Str::limit(html_entity_decode($soal->pertanyaan), 100) }}
                                                </div>
                                            @elseif($soal->tipe_pertanyaan === 'gambar')
                                                <div class="flex items-center">
                                                    <i class="fa-solid fa-image text-green-500 mr-2"></i>
                                                    <span class="text-sm text-gray-500">Soal Gambar</span>
                                                </div>
                                                @if ($soal->gambar_pertanyaan)
                                                    <img src="{{ asset('storage/soal/pertanyaan/' . $soal->gambar_pertanyaan) }}"
                                                        alt="Preview" class="mt-1 h-12 w-auto rounded border">
                                                @endif
                                            @else
                                                <div class="text-sm text-gray-900 mb-1">
                                                    {{ Str::limit($soal->pertanyaan, 80) }}
                                                </div>
                                                <div class="flex items-center">
                                                    <i class="fa-solid fa-image text-green-500 mr-1"></i>
                                                    <span class="text-xs text-gray-500">+ Gambar</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($soal->tipe_soal === 'pilihan_ganda')
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Pilihan Ganda
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                Essay
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if ($soal->tipe_soal === 'pilihan_ganda')
                                            <span class="font-medium">{{ $soal->kunci_jawaban ?? '-' }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right space-x-2">
                                        <a href="{{ route('naskah.soal.show', $soal) }}"
                                            class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('naskah.soal.edit', $soal) }}"
                                            class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <button onclick="deleteSoal({{ $soal->id }})"
                                            class="text-red-600 hover:text-red-900" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- Pagination for Soals -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $soals->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fa-solid fa-question-circle text-gray-400 text-6xl mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Soal</h3>
                    <p class="text-gray-500 mb-6">Mulai buat soal untuk bank soal ini.</p>
                    <a href="{{ route('naskah.soal.create', ['bank_soal_id' => $banksoal->id]) }}"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fa-solid fa-plus mr-1"></i>Tambah Soal Baru
                    </a>
                </div>
            @endif
        </div>
    </div>

    <script>
        function deleteSoal(id) {
            if (confirm('Yakin ingin menghapus soal ini?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url('naskah/soal') }}/' + id;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endsection
