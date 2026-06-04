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

            <div class="md:col-span-2 border-t pt-6 mt-2">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Sinkronisasi Siswa</h3>
                        <p class="text-sm text-gray-500">Atur quick sync otomatis dari API SIKEU.</p>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                        <input type="checkbox" name="sync_siswa_enabled" value="1" class="rounded border-gray-300"
                            {{ old('sync_siswa_enabled', $settings['sync_siswa_enabled'] ?? '0') == '1' ? 'checked' : '' }}>
                        Aktifkan sync otomatis
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Interval Sinkronisasi (menit)</label>
                        <input type="number" name="sync_siswa_interval_minutes" min="1" max="1440"
                            value="{{ old('sync_siswa_interval_minutes', $settings['sync_siswa_interval_minutes'] ?? 15) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                        @error('sync_siswa_interval_minutes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3">
                        <div class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Sync Terakhir</div>
                        <div class="mt-1 text-sm text-gray-800">
                            {{ $settings['sync_siswa_last_run_at'] ?? 'Belum pernah berjalan' }}
                        </div>
                        @if (!empty($settings['sync_siswa_last_status']) || !empty($settings['sync_siswa_last_message']))
                            <div class="mt-1 text-xs text-gray-600">
                                {{ $settings['sync_siswa_last_status'] ?? '-' }} - {{ $settings['sync_siswa_last_message'] ?? '-' }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="sync_siswa_date_start"
                            value="{{ old('sync_siswa_date_start', $settings['sync_siswa_date_start'] ?? null) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                        @error('sync_siswa_date_start') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                        <input type="date" name="sync_siswa_date_end"
                            value="{{ old('sync_siswa_date_end', $settings['sync_siswa_date_end'] ?? null) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                        @error('sync_siswa_date_end') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                        <input type="time" name="sync_siswa_time_start"
                            value="{{ old('sync_siswa_time_start', $settings['sync_siswa_time_start'] ?? null) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                        @error('sync_siswa_time_start') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                        <input type="time" name="sync_siswa_time_end"
                            value="{{ old('sync_siswa_time_end', $settings['sync_siswa_time_end'] ?? null) }}"
                            class="mt-1 w-full rounded-md border-gray-300">
                        @error('sync_siswa_time_end') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Simpan Setting</button>
        </div>
    </form>
@endsection
