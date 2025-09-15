{{-- filepath: c:\laragon\www\skadaexam\resources\views\features\data\siswa\partials\table.blade.php --}}
@if ($siswas->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="relative px-6 py-3">
                        <input type="checkbox" id="select-all"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Yayasan
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Student Info
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kelas
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Payment Status
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rekomendasi
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($siswas as $siswa)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox"
                                class="siswa-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                value="{{ $siswa->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $siswa->idyayasan }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fa-solid fa-user text-gray-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $siswa->nama ?? 'No Name' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $siswa->email ?? 'No Email' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $siswa->kelas ? $siswa->kelas->nama_kelas : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $siswa->status_pembayaran === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span
                                    class="w-1.5 h-1.5 mr-1.5 rounded-full
                                    {{ $siswa->status_pembayaran === 'Lunas' ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                {{ $siswa->status_pembayaran }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $siswa->rekomendasi === 'ya' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($siswa->rekomendasi ?? 'tidak') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('data.siswa.show', $siswa) }}"
                                    class="text-blue-600 hover:text-blue-900 transition-colors duration-150">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('data.siswa.edit', $siswa) }}"
                                    class="text-indigo-600 hover:text-indigo-900 transition-colors duration-150">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                @role('admin')
                                    <button onclick="deleteSiswa({{ $siswa->id }})"
                                        class="text-red-600 hover:text-red-900 transition-colors duration-150">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                @endrole
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-12">
        <div class="mx-auto h-16 w-16 text-gray-400 mb-4">
            <i class="fa-solid fa-users text-6xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500">Try adjusting your search filters or import students from API</p>
    </div>
@endif

<script>
    function deleteSiswa(id) {
        if (confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
            fetch(`{{ route('data.siswa.index') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success || data.message) {
                        // Show success message
                        const message = data.message || 'Student deleted successfully';
                        showToast(message, 'success');

                        // Refresh the page or perform search to update the table
                        if (typeof performSearch === 'function') {
                            performSearch();
                        } else {
                            location.reload();
                        }
                    } else {
                        showToast('Error: ' + (data.error || 'Unknown error'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showToast('Error: ' + error.message, 'error');
                });
        }
    }
</script>
