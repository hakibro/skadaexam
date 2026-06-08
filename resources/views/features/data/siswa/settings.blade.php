@extends('layouts.admin')

@section('title', 'Settings Siswa')
@section('page-title', 'Settings Siswa')
@section('page-description', 'Pengaturan sinkronisasi otomatis data siswa dari API SIKEU')

@section('content')
    <form method="POST" action="{{ route('data.siswa.settings.update') }}" class="bg-white rounded-lg shadow overflow-hidden">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Sinkronisasi Siswa</h3>
                    <p class="text-sm text-gray-500">Atur quick sync otomatis dan jendela waktu scheduler.</p>
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

        <div class="px-6 py-4 bg-gray-50 border-t flex justify-between gap-3">
            <a href="{{ route('data.siswa.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">
                Kembali
            </a>
            <button class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Simpan Settings</button>
        </div>
    </form>
@endsection
