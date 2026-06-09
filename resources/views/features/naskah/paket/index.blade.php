@extends('layouts.admin')

@section('title', 'Paket Ujian')
@section('page-title', 'Paket Ujian')
@section('page-description', 'Wadah pelaksanaan ujian per tahun ajaran')

@section('content')
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b bg-gray-50">
            <div>
                <h3 class="font-semibold text-gray-900">Daftar Paket Ujian</h3>
                <p class="text-xs text-gray-500">Default menampilkan tahun ajaran aktif.</p>
            </div>
            <a href="{{ route('naskah.paket-ujian.create') }}"
                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                <i class="fa-solid fa-plus mr-2"></i> Buat Ujian
            </a>
        </div>

        <div class="p-4 border-b">
            <form method="GET" class="flex flex-wrap gap-2" data-auto-submit>
                <select name="tahun_ajaran_id" class="rounded border-gray-300 text-sm">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach ($tahunAjarans as $tahunAjaran)
                        <option value="{{ $tahunAjaran->id }}" {{ (string) $tahunAjaranId === (string) $tahunAjaran->id ? 'selected' : '' }}>
                            {{ $tahunAjaran->nama }}{{ $tahunAjaran->is_active ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                <button class="px-3 py-2 bg-blue-600 text-white text-sm rounded">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Paket</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tahun Ajaran</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500 uppercase">Jadwal</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($paketUjians as $paket)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $paket->nama }}</td>
                            <td class="px-4 py-3">{{ $paket->tahunAjaran->nama ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ $paket->tanggal_mulai?->format('d/m/Y') ?? '-' }} -
                                {{ $paket->tanggal_selesai?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('naskah.paket-ujian.status', $paket) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="status"
                                        onchange="this.form.submit()"
                                        @disabled($paket->tahunAjaran?->isReadOnly())
                                        class="rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500
                                            {{ $paket->status === 'aktif' ? 'bg-green-50 text-green-800 border-green-200' : '' }}
                                            {{ $paket->status === 'draft' ? 'bg-gray-50 text-gray-800 border-gray-200' : '' }}
                                            {{ $paket->status === 'arsip' ? 'bg-yellow-50 text-yellow-800 border-yellow-200' : '' }}">
                                        <option value="draft" @selected($paket->status === 'draft')>Draft</option>
                                        <option value="aktif" @selected($paket->status === 'aktif')>Aktif</option>
                                        <option value="arsip" @selected($paket->status === 'arsip')>Arsip</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="font-medium text-gray-900">{{ $paket->jadwal_ujian_count }}</div>
                                @php
                                    $relatedCount = $paket->jadwal_ujian_count
                                        + $paket->bank_soals_count
                                        + $paket->ruangans_count
                                        + $paket->sesi_ruangans_count;
                                @endphp
                                @if ($relatedCount > 0)
                                    <div class="text-[11px] text-gray-500">
                                        {{ $paket->bank_soals_count }} bank,
                                        {{ $paket->ruangans_count }} ruang,
                                        {{ $paket->sesi_ruangans_count }} sesi
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <a href="{{ route('naskah.paket-ujian.show', $paket) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-blue-200 text-blue-600 hover:bg-blue-50"
                                        title="Lihat">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('naskah.paket-ujian.edit', $paket) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded border border-amber-200 text-amber-600 hover:bg-amber-50"
                                        title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form method="POST" action="{{ route('naskah.paket-ujian.destroy', $paket) }}"
                                        onsubmit="return confirm('Hapus paket ujian ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded border border-red-200 text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50"
                                            title="Hapus"
                                            @disabled($paket->tahunAjaran?->isReadOnly() || $paket->jadwal_ujian_count > 0)>
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                    @if (!$paket->tahunAjaran?->isReadOnly() && $relatedCount > 0)
                                        <form method="POST" action="{{ route('naskah.paket-ujian.force-destroy', $paket) }}"
                                            data-force-delete-paket
                                            data-paket-name="{{ $paket->nama }}"
                                            data-impact="Menghapus {{ $paket->jadwal_ujian_count }} jadwal, {{ $paket->bank_soals_count }} bank soal, {{ $paket->ruangans_count }} ruangan, {{ $paket->sesi_ruangans_count }} sesi, serta hasil/enrollment/pengawas/berita acara yang terkait.">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="force_delete" value="1">
                                            <input type="hidden" name="confirmation_name" value="">
                                            <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded border border-red-700 bg-red-50 text-red-700 hover:bg-red-100"
                                                title="Hapus paksa beserta data terkait">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada paket ujian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">{{ $paketUjians->links() }}</div>
    </div>

    <script>
        document.querySelectorAll('[data-force-delete-paket]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                const paketName = form.dataset.paketName;
                const impact = form.dataset.impact;
                const typedName = prompt(`${impact}\n\nAksi ini permanen. Ketik nama paket untuk konfirmasi:\n${paketName}`);

                if (typedName !== paketName) {
                    event.preventDefault();
                    alert('Hapus paksa dibatalkan. Nama paket tidak sesuai.');
                    return;
                }

                form.querySelector('input[name="confirmation_name"]').value = typedName;
            });
        });
    </script>
@endsection
