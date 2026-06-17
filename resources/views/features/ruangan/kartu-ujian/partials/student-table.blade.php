<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="w-12 px-4 py-2 text-center">
                    <input type="checkbox" id="check-current-page" class="rounded border-gray-300 text-blue-600">
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Siswa</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID Yayasan</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($students as $siswa)
                @php($record = $siswa->tahunAjaranRecords->first())
                <tr>
                    <td class="px-4 py-2 text-center">
                        <input type="checkbox" value="{{ $siswa->id }}"
                            class="student-check rounded border-gray-300 text-blue-600">
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $siswa->nama }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $siswa->idyayasan }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $record?->kelas?->nama_kelas ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                        Tidak ada siswa yang sesuai dengan filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="p-4">{{ $students->links() }}</div>
