@extends('layouts.admin')

@section('title', 'Template Sesi')

@section('content')
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700">
            Manajemen Template Sesi
        </h2>

        <!-- Actions -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 space-y-4 md:space-y-0">
            <div>
                <a href="{{ route('ruangan.template.create') }}"
                    class="px-4 py-2 text-sm font-medium leading-5 text-white transition-colors duration-150 bg-blue-600 border border-transparent rounded-md active:bg-blue-600 hover:bg-blue-700 focus:outline-none focus:shadow-outline-blue">
                    <i class="fas fa-plus mr-1"></i> Tambah Template Sesi
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @include('components.alert')

        <!-- Template List -->
        <div class="w-full overflow-hidden rounded-lg shadow-xs mb-8">
            <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr
                            class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama Template</th>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Penggunaan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y">
                        @forelse($templates as $template)
                            <tr class="text-gray-700">
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $template->kode_sesi }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $template->nama_sesi }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium py-1 px-2 rounded">
                                        {{ \Carbon\Carbon::parse($template->waktu_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($template->waktu_selesai)->format('H:i') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 font-semibold leading-tight rounded-full {{ $template->activeStatusLabel['class'] }}">
                                        {{ $template->activeStatusLabel['text'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 font-semibold leading-tight text-blue-700 bg-blue-100 rounded-full">
                                        {{ $template->sesi_ruangan_count }} Sesi
                                    </span>
                                    <span
                                        class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                        {{ $template->active_count ?? 0 }} Aktif
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- View -->
                                        <a href="{{ route('ruangan.template.show', $template->id) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Apply -->
                                        <a href="{{ route('ruangan.template.show-apply', $template->id) }}"
                                            class="text-green-600 hover:text-green-900" title="Terapkan ke ruangan">
                                            <i class="fas fa-check-circle"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('ruangan.template.edit', $template->id) }}"
                                            class="text-yellow-600 hover:text-yellow-900">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Toggle Active -->
                                        <form method="POST"
                                            action="{{ route('ruangan.template.toggle-active', $template->id) }}"
                                            class="inline-block">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit"
                                                class="{{ $template->is_active ? 'text-gray-600 hover:text-gray-900' : 'text-green-600 hover:text-green-900' }}"
                                                title="{{ $template->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <i
                                                    class="fas {{ $template->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Delete (only if not used) -->
                                        @if ($template->sesi_ruangan_count == 0)
                                            <form method="POST"
                                                action="{{ route('ruangan.template.destroy', $template->id) }}"
                                                class="inline-block delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Force Delete (even if used) -->
                                            <form method="POST"
                                                action="{{ route('ruangan.template.force-delete', $template->id) }}"
                                                class="inline-block force-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Hapus Paksa">
                                                    <i class="fas fa-radiation"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center text-gray-500">
                                    Belum ada template sesi. <a href="{{ route('ruangan.template.create') }}"
                                        class="text-blue-600 hover:underline">Buat template</a> untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'Yakin ingin menghapus template ini? Tindakan ini tidak dapat dibatalkan.'
                        )) {
                        this.submit();
                    }
                });
            });

            // Force Delete confirmation
            const forceDeleteForms = document.querySelectorAll('.force-delete-form');
            forceDeleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (confirm(
                            'PERHATIAN: Anda akan menghapus template ini beserta semua sesi ruangan yang terkait! Tindakan ini TIDAK DAPAT dibatalkan dan dapat menyebabkan kerusakan data. Lanjutkan?'
                        )) {
                        this.submit();
                    }
                });
            });
        });
    </script>
@endsection
