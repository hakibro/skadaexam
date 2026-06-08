@extends('layouts.admin')

@section('title', 'Import Komprehensif Naskah')
@section('page-title', 'Import Komprehensif Naskah')
@section('page-description', 'Import mata pelajaran, bank soal, dan jadwal ujian dari satu file')

@section('content')
    <div class="space-y-6">
        @if (session('import_results'))
            @php($results = session('import_results'))
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-3">Ringkasan Import</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                    @foreach ($results as $key => $value)
                        @if ($key !== 'errors')
                            <div class="rounded border p-3">
                                <div class="text-gray-500">{{ str_replace('_', ' ', $key) }}</div>
                                <div class="text-xl font-semibold">{{ $value }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if (!empty($results['errors']))
                    <div class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">
                        @foreach ($results['errors'] as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b">
                <h3 class="text-lg font-medium text-gray-900">Upload File</h3>
                <p class="text-sm text-gray-600 mt-1">Soal tetap diimport dari detail bank soal seperti alur yang sudah ada.</p>
            </div>
            <form method="POST" action="{{ route('naskah.import-komprehensif.process') }}" enctype="multipart/form-data"
                class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                    <div class="mt-1 rounded-md border bg-gray-50 px-3 py-2 text-sm">{{ $activeYear->nama }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Paket Ujian</label>
                    <select name="paket_ujian_id" class="mt-1 w-full rounded-md border-gray-300">
                        <option value="">Paket aktif / kosongkan</option>
                        @foreach ($paketUjians as $paket)
                            <option value="{{ $paket->id }}">{{ $paket->nama }} - {{ ucfirst($paket->status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">File Excel/CSV</label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="mt-1 w-full rounded-md border-gray-300" required>
                    @error('file') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="rounded-md bg-blue-50 p-4 text-sm text-blue-800">
                    Kolom: nama_mapel, tingkat, jurusan, judul_bank_soal, judul_ujian, tanggal, durasi_menit, kelas_target.
                    Jika kelas_target kosong, sistem memakai kelas yang cocok dengan tingkat dan jurusan mapel.
                </div>
                <div class="flex justify-between">
                    <a href="{{ route('naskah.import-komprehensif.template') }}" class="px-4 py-2 rounded-md border text-gray-700">
                        Download Template Excel
                    </a>
                    <button class="px-4 py-2 rounded-md bg-blue-600 text-white">Import Data</button>
                </div>
            </form>
        </div>
    </div>
@endsection
