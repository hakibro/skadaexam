@extends('layouts.admin')

@section('title', 'Terapkan Template Sesi')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Terapkan Template Sesi
        </h2>

        <!-- Breadcrumb -->
        <div class="flex text-sm text-gray-600 mb-4">
            <a href="{{ route('ruangan.template.index') }}" class="hover:underline">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar template
            </a>
            <span class="mx-2">|</span>
            <a href="{{ route('ruangan.template.show', $template->id) }}" class="hover:underline">
                Lihat detail template
            </a>
        </div>

        <!-- Template Info -->
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Detail Template</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex">
                            <span class="w-32 font-medium">Nama Template:</span>
                            <span>{{ $template->nama_sesi }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Waktu:</span>
                            <span>{{ \Carbon\Carbon::parse($template->waktu_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($template->waktu_selesai)->format('H:i') }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Durasi:</span>
                            <span>{{ \Carbon\Carbon::parse($template->waktu_mulai)->diffInMinutes(\Carbon\Carbon::parse($template->waktu_selesai)) }}
                                menit</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 font-medium">Status Default:</span>
                            <span
                                class="px-2 py-0.5 text-xs font-medium leading-tight rounded-full {{ $template->statusLabel['class'] }}">
                                {{ $template->statusLabel['text'] }}
                            </span>
                        </div>
                        @if ($template->deskripsi)
                            <div class="flex">
                                <span class="w-32 font-medium">Deskripsi:</span>
                                <span>{{ $template->deskripsi }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                    <h4 class="text-base font-medium text-blue-700 dark:text-blue-300 mb-2">
                        <i class="fas fa-info-circle mr-1"></i> Informasi
                    </h4>
                    <p class="text-sm text-blue-600 dark:text-blue-300 mb-2">
                        Template ini akan diterapkan ke ruangan yang Anda pilih pada tanggal yang ditentukan.
                    </p>
                    <p class="text-sm text-blue-600 dark:text-blue-300">
                        Jika sesi dengan template ini sudah ada pada ruangan dan tanggal yang sama, maka sesi tersebut akan
                        diperbarui.
                    </p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @include('components.alert')

        <!-- Form -->
        <div class="px-4 py-3 mb-8 bg-white rounded-lg shadow-md dark:bg-gray-800">
            <form action="{{ route('ruangan.template.apply', $template->id) }}" method="POST">
                @csrf

                <!-- Date Selection -->
                <div class="mb-6">
                    <label for="date" class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                        min="{{ date('Y-m-d') }}"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                    @error('date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apply to All Rooms Option -->
                <div class="mb-6">
                    <div class="flex items-center">
                        <input id="apply_all" name="apply_all" type="checkbox" value="1"
                            {{ old('apply_all') ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="apply_all" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            Terapkan ke semua ruangan aktif
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Pilih opsi ini untuk menerapkan template ke semua ruangan yang statusnya aktif.
                    </p>
                </div>

                <!-- Room Selection -->
                <div id="room-selection" class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        Pilih Ruangan <span class="text-red-500">*</span>
                    </label>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg max-h-60 overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            @foreach ($rooms as $room)
                                <div class="flex items-start">
                                    <input id="room-{{ $room->id }}" name="ruangan_ids[]" type="checkbox"
                                        value="{{ $room->id }}"
                                        {{ is_array(old('ruangan_ids')) && in_array($room->id, old('ruangan_ids')) ? 'checked' : '' }}
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600 mt-1">
                                    <label for="room-{{ $room->id }}"
                                        class="ml-2 text-sm text-gray-900 dark:text-gray-300">
                                        <span class="font-medium">{{ $room->nama_ruangan }}</span><br>
                                        <span class="text-xs text-gray-500">{{ $room->kode_ruangan }} (Kapasitas:
                                            {{ $room->kapasitas }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @error('ruangan_ids')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end">
                    <button type="submit"
                        class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                        Terapkan Template
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const applyAllCheckbox = document.getElementById('apply_all');
            const roomSelection = document.getElementById('room-selection');
            const roomCheckboxes = document.querySelectorAll('input[name="ruangan_ids[]"]');

            // Toggle room selection visibility
            function toggleRoomSelection() {
                if (applyAllCheckbox.checked) {
                    roomSelection.classList.add('opacity-50', 'pointer-events-none');
                    roomCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                    });
                } else {
                    roomSelection.classList.remove('opacity-50', 'pointer-events-none');
                }
            }

            // Initial toggle
            toggleRoomSelection();

            // Listen for changes
            applyAllCheckbox.addEventListener('change', toggleRoomSelection);
        });
    </script>
@endsection
