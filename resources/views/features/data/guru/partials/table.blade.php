<!-- filepath: resources\views\features\data\guru\partials\table.blade.php -->
@if ($gurus->count() > 0)
    <div class="border-t border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" id="select-all"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($gurus as $guru)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_gurus[]" value="{{ $guru->id }}"
                                class="guru-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fa-solid fa-chalkboard-user text-blue-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 search-highlight">{{ $guru->nama }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 search-highlight">{{ $guru->nip ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 search-highlight">{{ $guru->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                // Get user's roles from Spatie if available
$userRoles = $guru->user ? $guru->user->roles->pluck('name')->toArray() : [];
$displayRole = !empty($userRoles) ? $userRoles[0] : 'guru';

$roleColors = [
    'guru' => 'bg-green-100 text-green-800',
    'data' => 'bg-blue-100 text-blue-800',
    'naskah' => 'bg-purple-100 text-purple-800',
    'pengawas' => 'bg-yellow-100 text-yellow-800',
    'koordinator' => 'bg-red-100 text-red-800',
    'ruangan' => 'bg-gray-100 text-gray-800',
];
$colorClass = $roleColors[$displayRole] ?? 'bg-gray-100 text-gray-800';

$roleLabels = [
    'guru' => 'Guru (Default)',
    'data' => 'Data Management',
    'naskah' => 'Naskah Management',
    'pengawas' => 'Pengawas',
    'koordinator' => 'Koordinator',
    'ruangan' => 'Ruangan Management',
                                ];
                                $roleLabel = $roleLabels[$displayRole] ?? ucfirst($displayRole);
                            @endphp
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $guru->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('data.guru.show', $guru) }}"
                                    class="text-blue-600 hover:text-blue-900">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('data.guru.edit', $guru) }}"
                                    class="text-green-600 hover:text-green-900">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                <form action="{{ route('data.guru.destroy', $guru) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Yakin ingin hapus guru {{ $guru->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Empty State -->
    <div class="px-4 py-8 text-center">
        <div class="text-center">
            <i class="fa-solid fa-search text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg mb-2">No guru found</p>
            <p class="text-gray-400 text-sm">Try adjusting your search terms</p>
            <a href="{{ route('data.guru.create') }}"
                class="text-blue-600 hover:text-blue-800 font-medium mt-2 inline-block">
                <i class="fa-solid fa-plus mr-1"></i>Add new guru instead
            </a>
        </div>
    </div>
@endif
