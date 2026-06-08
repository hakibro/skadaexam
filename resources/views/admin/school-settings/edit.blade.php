@extends('layouts.admin')

@section('title', 'Setting Sekolah')
@section('page-title', 'Setting Sekolah')
@section('page-description', 'Logo dan identitas sekolah untuk dokumen ujian')

@section('content')
    <form method="POST" action="{{ route('admin.school-settings.update') }}" enctype="multipart/form-data"
        class="bg-white rounded-lg shadow overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $settings['nama_sekolah']) }}"
                    class="mt-1 w-full rounded-md border-gray-300" required>
                @error('nama_sekolah') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Logo</label>
                <input type="file" name="logo" accept="image/*" class="mt-1 w-full rounded-md border-gray-300">
                @if (!empty($settings['logo_path']))
                    <div class="mt-3 flex items-center gap-3">
                        <img src="{{ asset('storage/' . $settings['logo_path']) }}" class="h-14 w-14 object-contain border rounded">
                        <label class="text-sm text-gray-700">
                            <input type="checkbox" name="hapus_logo" value="1" class="rounded border-gray-300">
                            Hapus logo
                        </label>
                    </div>
                @endif
                @error('logo') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Alamat</label>
                <textarea name="alamat" rows="3" class="mt-1 w-full rounded-md border-gray-300">{{ old('alamat', $settings['alamat']) }}</textarea>
            </div>

            @foreach ([
                'npsn' => 'NPSN',
                'nss' => 'NSS',
                'kode_pos' => 'Kode Pos',
                'telepon' => 'Telepon',
                'email' => 'Email',
                'website' => 'Website',
                'kepala_sekolah' => 'Kepala Sekolah',
            ] as $key => $label)
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                    <input type="{{ $key === 'email' ? 'email' : 'text' }}" name="{{ $key }}"
                        value="{{ old($key, $settings[$key]) }}" class="mt-1 w-full rounded-md border-gray-300">
                    @error($key) <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            @endforeach

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Info Lain</label>
                <textarea name="info_lain" rows="4" class="mt-1 w-full rounded-md border-gray-300">{{ old('info_lain', $settings['info_lain']) }}</textarea>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Simpan Setting</button>
        </div>
    </form>
@endsection
