@csrf
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="p-4 border-b bg-gray-50">
        <h3 class="font-semibold text-gray-900">Data Tahun Ajaran</h3>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Kode</label>
            <input type="text" name="kode" value="{{ old('kode', $tahunAjaran->kode) }}" required
                class="mt-1 w-full rounded border-gray-300">
            @error('kode') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Nama</label>
            <input type="text" name="nama" value="{{ old('nama', $tahunAjaran->nama) }}" required
                class="mt-1 w-full rounded border-gray-300">
            @error('nama') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', optional($tahunAjaran->tanggal_mulai)->format('Y-m-d')) }}"
                class="mt-1 w-full rounded border-gray-300">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', optional($tahunAjaran->tanggal_selesai)->format('Y-m-d')) }}"
                class="mt-1 w-full rounded border-gray-300">
            @error('tanggal_selesai') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="mt-1 w-full rounded border-gray-300">
                @foreach (['draft' => 'Draft', 'aktif' => 'Aktif', 'arsip' => 'Arsip'] as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $tahunAjaran->status ?: 'draft') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300"
                    {{ old('is_active', $tahunAjaran->is_active) ? 'checked' : '' }}>
                <span class="ml-2 text-sm text-gray-700">Jadikan tahun ajaran aktif</span>
            </label>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Keterangan</label>
            <textarea name="keterangan" rows="3" class="mt-1 w-full rounded border-gray-300">{{ old('keterangan', $tahunAjaran->keterangan) }}</textarea>
        </div>
    </div>
    <div class="px-4 py-3 bg-gray-50 border-t text-right">
        <a href="{{ route('admin.tahun-ajaran.index') }}" class="px-4 py-2 border rounded text-gray-700 bg-white">Batal</a>
        <button type="submit" class="ml-2 px-4 py-2 rounded bg-purple-600 text-white">Simpan</button>
    </div>
</div>
