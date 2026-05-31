@csrf
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="p-4 border-b bg-gray-50">
        <h3 class="font-semibold text-gray-900">Informasi Paket Ujian</h3>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
            <input type="text" value="{{ $activeYear->nama ?? $paketUjian->tahunAjaran->nama ?? '-' }}" readonly
                class="mt-1 w-full rounded border-gray-300 bg-gray-100">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nama Paket</label>
            <input type="text" name="nama" value="{{ old('nama', $paketUjian->nama) }}" required
                class="mt-1 w-full rounded border-gray-300" placeholder="Contoh: UAS Genap 2025/2026">
            @error('nama') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', optional($paketUjian->tanggal_mulai)->format('Y-m-d')) }}"
                class="mt-1 w-full rounded border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($paketUjian->tanggal_selesai)->format('Y-m-d')) }}"
                class="mt-1 w-full rounded border-gray-300">
            @error('tanggal_selesai') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 w-full rounded border-gray-300">
                @foreach (['draft' => 'Draft', 'aktif' => 'Aktif', 'arsip' => 'Arsip'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $paketUjian->status ?: 'draft') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Keterangan</label>
            <textarea name="keterangan" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('keterangan', $paketUjian->keterangan) }}</textarea>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 border-t text-right">
        <a href="{{ route('naskah.paket-ujian.index') }}" class="px-4 py-2 border rounded text-gray-700 bg-white">Batal</a>
        <button type="submit" class="ml-2 px-4 py-2 rounded bg-green-600 text-white">Simpan</button>
    </div>
</div>
