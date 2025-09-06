@extends('layouts.admin')

@section('title', 'Mata Pelajaran')
@section('page-title', 'Daftar Mata Pelajaran')
@section('page-description', 'Kelola data mata pelajaran untuk ujian')

@section('content')
    <div class="space-y-6">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Daftar Mata Pelajaran</h3>
                <div class="flex space-x-2">
                    <!-- Bulk Actions -->
                    <div class="relative" id="bulk-action-dropdown" style="display: none;">
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150"
                            onclick="toggleBulkDropdown()">
                            <i class="fa-solid fa-tasks mr-2"></i> Aksi Terpilih
                            <i class="fa-solid fa-chevron-down ml-2"></i>
                        </button>
                        <div id="bulk-dropdown-menu"
                            class="hidden absolute right-0 mt-2 w-56 bg-white border border-gray-300 rounded-md shadow-lg z-10">
                            <div class="py-1">
                                <button type="button" onclick="bulkStatusChange('status_aktif')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-check-circle mr-2"></i> Set Aktif
                                </button>
                                <button type="button" onclick="bulkStatusChange('status_nonaktif')"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fa-solid fa-times-circle mr-2"></i> Set Nonaktif
                                </button>
                                <hr class="my-1">
                                <button type="button" onclick="bulkDelete('delete')"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fa-solid fa-trash mr-2"></i> Hapus Terpilih
                                </button>
                                <button type="button" onclick="bulkDelete('force_delete')"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-800 hover:bg-red-50">
                                    <i class="fa-solid fa-skull mr-2"></i> Hapus Paksa
                                </button>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('naskah.mapel.trashed') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition duration-150">
                        <i class="fa-solid fa-trash-can mr-2"></i> Mapel Terhapus
                    </a>
                    <a href="{{ route('naskah.mapel.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition duration-150">
                        <i class="fa-solid fa-plus mr-2"></i> Tambah Mapel
                    </a>
                </div>
            </div>

            <div class="p-4 bg-gray-50">
                <form action="{{ route('naskah.mapel.index') }}" method="GET" class="flex flex-wrap gap-4">
                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kata Kunci</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-input w-full md:w-64 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Cari nama atau kode mapel...">
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif
                            </option>
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                        <select name="tingkat"
                            class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Tingkat</option>
                            @foreach ($tingkats as $tingkat)
                                <option value="{{ $tingkat }}" {{ request('tingkat') == $tingkat ? 'selected' : '' }}>
                                    {{ $tingkat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-auto">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                        <select name="jurusan"
                            class="form-select w-full rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Semua Jurusan</option>
                            @foreach ($jurusans as $jurusan)
                                <option value="{{ $jurusan }}" {{ request('jurusan') == $jurusan ? 'selected' : '' }}>
                                    {{ $jurusan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-auto flex items-end space-x-2">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-search mr-2"></i> Filter
                        </button>
                        <a href="{{ route('naskah.mapel.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md transition duration-150">
                            <i class="fa-solid fa-times mr-2"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
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
                                                <div class="text-sm font-medium text-gray-900">{{ $mapel->nama_mapel }}
                                                </div>
                                                @if ($mapel->deskripsi)
                                                    <div class="text-xs text-gray-500 truncate max-w-xs">
                                                        {{ $mapel->deskripsi }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mapel->tingkat }}
                                    </td>
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
                <input type="hidden" id="session_mapel_id" value="{{ session('mapel_id') }}">
            </div>
        </div>
    </div>

    <!-- Bulk Action Form -->
    <form id="bulkActionForm" action="{{ route('naskah.mapel.bulk-action') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="action" id="bulk_action_type" value="">
        <div id="bulk_mapel_ids_container"></div>
    </form>

    <!-- Delete Form -->
    <form id="deleteMainForm" method="POST" action="" style="display: none;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="force" id="forceDeleteMain" value="0">
    </form>

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
                    <button type="button" onclick="executeDelete(false)"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Hapus
                    </button>
                    <button type="button" onclick="executeDelete(true)" id="forceDeleteButton"
                        class="mt-2 sm:mt-0 w-full justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-800 text-base font-medium text-white hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm hidden">
                        Hapus Paksa
                    </button>
                    <button type="button" onclick="hideDeleteModal()"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set up initial bulk action visibility
            updateBulkActionVisibility();
        });

        function toggleAllCheckboxes(source) {
            const checkboxes = document.getElementsByClassName('mapel-checkbox');
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
            updateBulkActionVisibility();
        }

        function updateBulkActionVisibility() {
            const checkboxes = document.getElementsByClassName('mapel-checkbox');
            let checkedCount = 0;

            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    checkedCount++;
                }
            }

            const bulkActionDropdown = document.getElementById('bulk-action-dropdown');
            if (checkedCount > 0) {
                bulkActionDropdown.style.display = 'block';
            } else {
                bulkActionDropdown.style.display = 'none';

                // Also hide the dropdown menu if it's open
                const dropdownMenu = document.getElementById('bulk-dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.add('hidden');
                }
            }
        }

        function toggleBulkDropdown() {
            const dropdownMenu = document.getElementById('bulk-dropdown-menu');
            dropdownMenu.classList.toggle('hidden');
        }

        function bulkStatusChange(status) {
            console.log('Performing bulk status change to:', status);
            const form = document.getElementById('bulkActionForm');
            const actionInput = document.getElementById('bulk_action_type');
            const container = document.getElementById('bulk_mapel_ids_container');

            if (!form || !actionInput || !container) {
                console.error('Required form elements not found!', {
                    form: !!form,
                    actionInput: !!actionInput,
                    container: !!container
                });
                return;
            }

            actionInput.value = status;
            container.innerHTML = '';

            // Log the form action
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);

            // Get all checked checkboxes and add them to the form
            const checkboxes = document.getElementsByClassName('mapel-checkbox');
            console.log('Found', checkboxes.length, 'checkboxes');
            let checkedCount = 0;

            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    checkedCount++;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'mapel_ids[]';
                    input.value = checkboxes[i].value;
                    container.appendChild(input);
                }
            }

            console.log('Adding', checkedCount, 'selected mapel IDs to form');

            if (checkedCount > 0) {
                form.submit();
            } else {
                alert('Tidak ada mata pelajaran yang dipilih');
            }
        }

        function bulkDelete(action) {
            if (!confirm(
                    `Apakah Anda yakin ingin menghapus semua mata pelajaran yang dipilih${action === 'force_delete' ? ' beserta bank soal dan soal terkait' : ''}?`
                )) {
                return;
            }

            console.log('Performing bulk delete with action:', action);
            const form = document.getElementById('bulkActionForm');
            const actionInput = document.getElementById('bulk_action_type');
            const container = document.getElementById('bulk_mapel_ids_container');

            console.log('Form action:', form.action);

            actionInput.value = action;
            container.innerHTML = '';

            // Get all checked checkboxes and add them to the form
            const checkboxes = document.getElementsByClassName('mapel-checkbox');
            let checkedCount = 0;

            for (let i = 0; i < checkboxes.length; i++) {
                if (checkboxes[i].checked) {
                    checkedCount++;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'mapel_ids[]';
                    input.value = checkboxes[i].value;
                    container.appendChild(input);
                }
            }

            if (checkedCount === 0) {
                alert('Tidak ada mata pelajaran yang dipilih');
                return;
            }

            form.submit();
        }

        function confirmDelete(id, name) {
            console.log('Confirming delete for id:', id, 'name:', name);
            const modal = document.getElementById('deleteModal');
            const deleteForm = document.getElementById('deleteMainForm');
            const deleteMessage = document.getElementById('delete-message');
            const forceDeleteButton = document.getElementById('forceDeleteButton');
            const sessionMapelId = document.getElementById('session_mapel_id')?.value || '';

            console.log('Session mapel_id:', sessionMapelId);

            // Set the form action correctly
            const route = "{{ route('naskah.mapel.destroy', ':id') }}".replace(':id', id);
            deleteForm.action = route;
            console.log('Set delete form action to:', route);

            // Store the ID in a data attribute for later use
            modal.dataset.mapelId = id;

            deleteMessage.textContent = `Apakah Anda yakin ingin menghapus mata pelajaran "${name}"?`;

            // Show or hide force delete button based on if there's a warning in the session
            if (sessionMapelId == id) {
                console.log('This mapel has related bank soal, showing force delete button');
                forceDeleteButton.classList.remove('hidden');
                deleteMessage.textContent =
                    `Mata pelajaran "${name}" memiliki bank soal terkait. Gunakan hapus paksa untuk menghapus mata pelajaran beserta bank soal dan soal terkait.`;
            } else {
                console.log('No bank soal for this mapel, hiding force delete button');
                forceDeleteButton.classList.add('hidden');
            }

            // Make sure modal is visible
            modal.classList.remove('hidden');
        }

        function hideDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
        }

        function executeDelete(force) {
            console.log('Executing delete with force:', force);
            const forceInput = document.getElementById('forceDeleteMain');

            // Set the force value (convert to string to ensure it works with Laravel)
            forceInput.value = force ? '1' : '0';

            // Make sure the form is properly submitting
            const form = document.getElementById('deleteMainForm');

            if (!form) {
                console.error('Delete form not found!');
                alert('Terjadi kesalahan: Form hapus tidak ditemukan');
                return;
            }

            if (!form.action) {
                console.error('Delete form action not set!');
                alert('Terjadi kesalahan: URL tujuan hapus tidak ditemukan');
                return;
            }

            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            console.log('Force value:', forceInput.value);

            // Hide modal before submitting
            hideDeleteModal();

            // Submit the form
            form.submit();
        }
    </script>
@endsection
