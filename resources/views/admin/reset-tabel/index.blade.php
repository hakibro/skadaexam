@extends('layouts.admin')

@section('title', 'Reset Tabel')
@section('page-title', 'Reset Tabel')
@section('page-description', 'Reset bulk data operasional untuk mempercepat pengujian aplikasi')

@section('content')
    <div class="space-y-6">
        @if (session('reset_summary'))
            <div class="rounded-md border border-green-200 bg-green-50 p-4">
                <div class="font-semibold text-green-800">Reset tabel berhasil</div>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2 text-sm text-green-800">
                    @foreach (session('reset_summary') as $table => $count)
                        <div class="flex justify-between rounded bg-white/70 px-3 py-2">
                            <span>{{ $table }}</span>
                            <span class="font-semibold">{{ number_format($count) }} baris</span>
                        </div>
                    @endforeach
                </div>
                @if (session('reset_skipped'))
                    <div class="mt-3 text-sm text-yellow-700">
                        Dilewati: {{ implode(', ', session('reset_skipped')) }}
                    </div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('admin.reset-tabel.reset') }}" id="reset-table-form"
            class="bg-white rounded-lg shadow overflow-hidden">
            @csrf

            <div class="p-6 space-y-6">
                <div class="rounded-md border border-red-200 bg-red-50 p-4">
                    <div class="font-semibold text-red-800">Perhatian</div>
                    <p class="mt-1 text-sm text-red-700">
                        Reset tabel menghapus data permanen untuk tabel yang dipilih. Tabel sistem seperti user, role,
                        tahun ajaran, dan setting sekolah selalu dilindungi.
                    </p>
                    <p class="mt-2 text-xs text-red-700">
                        Tabel diambil dari database aktif project: <span class="font-semibold">{{ $databaseName }}</span>.
                    </p>
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Preset Cepat</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ($presets as $key => $preset)
                            <button type="button" data-preset="{{ $key }}"
                                class="preset-button text-left rounded-md border border-gray-200 p-4 hover:border-blue-400 hover:bg-blue-50">
                                <div class="font-medium text-gray-900">{{ $preset['label'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ count($preset['tables']) }} tabel</div>
                            </button>
                        @endforeach
                        <button type="button" id="clear-selection"
                            class="text-left rounded-md border border-gray-200 p-4 hover:border-gray-400 hover:bg-gray-50">
                            <div class="font-medium text-gray-900">Kosongkan Pilihan</div>
                            <div class="mt-1 text-xs text-gray-500">Mulai ulang checklist</div>
                        </button>
                    </div>
                </div>

                <div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-3">
                        <h3 class="text-base font-semibold text-gray-900">Checklist Tabel</h3>
                        <div class="text-sm text-gray-500">
                            <span id="selected-count">0</span> tabel dipilih
                        </div>
                    </div>

                    @error('selected_tables') <p class="text-sm text-red-600 mb-3">{{ $message }}</p> @enderror

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @foreach ($availableTables as $table)
                            <label class="flex items-center justify-between gap-3 rounded-md border border-gray-200 px-3 py-2">
                                <span class="flex items-center gap-2 min-w-0">
                                    <input type="checkbox" name="selected_tables[]" value="{{ $table }}"
                                        class="table-checkbox rounded border-gray-300"
                                        {{ in_array($table, old('selected_tables', []), true) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-800 truncate">{{ $table }}</span>
                                </span>
                                <span class="text-xs text-gray-500">{{ number_format($tableCounts[$table] ?? 0) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="border-t pt-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ketik RESET</label>
                        <input type="text" name="confirmation" value="{{ old('confirmation') }}"
                            class="mt-1 w-full rounded-md border-gray-300" autocomplete="off">
                        @error('confirmation') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
                    Reset Tabel Terpilih
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.reset-tabel.sesi-duplikat') }}"
            class="bg-white rounded-lg shadow overflow-hidden">
            @csrf
            <div class="p-6 space-y-4">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Hapus Sesi Ruangan Duplikat</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Menghapus sesi ruangan hasil duplikasi jadwal ujian, yaitu sesi dengan kolom
                            <span class="font-mono">sumber</span> berisi kode sesi sumber. Sesi sumber tetap dipertahankan.
                        </p>
                    </div>
                    <div class="text-sm text-gray-500 md:text-right">
                        Relasi peserta, pivot jadwal, berita acara, dan pelanggaran terkait ikut dibersihkan.
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Ketik RESET</label>
                    <input type="text" name="duplicate_confirmation" class="mt-1 w-full rounded-md border-gray-300"
                        autocomplete="off">
                    @error('duplicate_confirmation') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
                <button type="submit" class="px-4 py-2 rounded-md bg-orange-600 text-white hover:bg-orange-700">
                    Hapus Sesi Duplikat
                </button>
            </div>
        </form>
    </div>

    <script>
        const presets = @json(collect($presets)->map(fn ($preset) => $preset['tables']));
        const checkboxes = Array.from(document.querySelectorAll('.table-checkbox'));
        const selectedCount = document.getElementById('selected-count');

        function refreshSelectedCount() {
            selectedCount.textContent = checkboxes.filter((checkbox) => checkbox.checked).length;
        }

        document.querySelectorAll('.preset-button').forEach((button) => {
            button.addEventListener('click', () => {
                const tables = presets[button.dataset.preset] || [];
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = tables.includes(checkbox.value);
                });
                refreshSelectedCount();
            });
        });

        document.getElementById('clear-selection').addEventListener('click', () => {
            checkboxes.forEach((checkbox) => checkbox.checked = false);
            refreshSelectedCount();
        });

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refreshSelectedCount));
        refreshSelectedCount();
    </script>
@endsection
