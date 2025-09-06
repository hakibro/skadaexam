@if (count($mapels) > 0)
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-3 text-left">
                    <input type="checkbox" id="select-all"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        onchange="toggleAllCheckboxes(this)">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Mata Pelajaran</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tingkat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Jurusan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Aksi</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($mapels as $mapel)
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-4 whitespace-nowrap">
                        <input type="checkbox" name="mapel_ids[]" value="{{ $mapel->id }}"
                            class="mapel-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            onchange="updateBulkActionVisibility()">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $mapel->kode_mapel }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-md flex items-center justify-center text-blue-600">
                                <i class="fa-solid fa-book-open text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $mapel->nama_mapel }}</div>
                                @if ($mapel->deskripsi)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $mapel->deskripsi }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapel->tingkat }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $mapel->jurusan ?? 'Umum' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span
                            class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full {{ $mapel->status == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i
                                class="fa-solid {{ $mapel->status == 'aktif' ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                            {{ ucfirst($mapel->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('naskah.mapel.show', $mapel->id) }}"
                            class="text-blue-600 hover:text-blue-900 mr-3">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('naskah.mapel.edit', $mapel->id) }}"
                            class="text-yellow-600 hover:text-yellow-900 mr-3">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <button type="button"
                            onclick="confirmDelete('{{ $mapel->id }}', '{{ $mapel->nama_mapel }}')"
                            class="text-red-600 hover:text-red-900 border-none bg-transparent p-0">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="p-4">
        {{ $mapels->appends(request()->query())->links() }}
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="flex items-center justify-center min-h-screen">
            <div class="relative bg-white rounded-lg max-w-md w-full mx-auto shadow-xl z-10">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                    <div class="sm:flex sm:items-start">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fa-solid fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Hapus
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="delete-message">
                                    Apakah Anda yakin ingin menghapus mapel ini?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                    <form id="deleteForm" method="POST" action="" class="inline">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="force" id="forceDelete" value="0">
                        <button type="button" onclick="executeDelete(false)"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Hapus
                        </button>
                        <button type="button" onclick="executeDelete(true)" id="forceDeleteButton"
                            class="mt-2 sm:mt-0 w-full justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-800 text-base font-medium text-white hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm hidden">
                            Hapus Paksa
                        </button>
                    </form>
                    <button type="button" onclick="hideDeleteModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="text-center py-10">
        <i class="fa-solid fa-book text-gray-300 text-5xl mb-3"></i>
        <p class="text-gray-500 text-lg">Belum ada data mata pelajaran</p>
        <p class="text-gray-400 mb-4">Tambahkan mata pelajaran untuk memulai</p>
        <a href="{{ route('naskah.mapel.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
            <i class="fa-solid fa-plus mr-2"></i> Tambah Mata Pelajaran
        </a>
    </div>
@endif

<!-- Hidden input for session data -->
<input type="hidden" id="session_mapel_id" value="{{ $mapel_id ?? '' }}">
