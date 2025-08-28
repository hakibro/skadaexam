<!-- filepath: resources\views\features\data\siswa\partials\table.blade.php -->

@if ($siswas->count() > 0)
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Recommendation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment
                        Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Sync
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($siswas as $siswa)
                    <tr class="hover:bg-gray-50">
                        <!-- Student Info -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                {{ $siswa->nama ?: $siswa->idyayasan }}
                            </div>
                            <div class="text-sm text-gray-500">ID: {{ $siswa->idyayasan }}</div>
                            @if ($siswa->email)
                                <div class="text-sm text-gray-500">{{ $siswa->email }}</div>
                            @endif
                        </td>

                        <!-- Class -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $siswa->kelas ?: '-' }}
                        </td>

                        <!-- Recommendation -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if ($siswa->rekomendasi === 'ya')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fa-solid fa-check-circle mr-1"></i>
                                    Yes
                                </span>
                            @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fa-solid fa-times-circle mr-1"></i>
                                    No
                                </span>
                            @endif
                        </td>

                        <!-- Payment Status -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = match ($siswa->status_pembayaran) {
                                    'Lunas' => 'bg-green-100 text-green-800',
                                    'Belum Lunas' => 'bg-red-100 text-red-800',
                                    'Cicilan' => 'bg-yellow-100 text-yellow-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusIcon = match ($siswa->status_pembayaran) {
                                    'Lunas' => 'fa-check-circle',
                                    'Belum Lunas' => 'fa-times-circle',
                                    'Cicilan' => 'fa-clock',
                                    default => 'fa-question-circle',
                                };
                            @endphp
                            <div class="flex items-center space-x-2">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                    <i class="fa-solid {{ $statusIcon }} mr-1"></i>
                                    {{ $siswa->status_pembayaran ?: 'Unknown' }}
                                </span>

                                <!-- Individual Sync Button -->
                                <button class="sync-payment-btn text-blue-600 hover:text-blue-800 text-xs"
                                    data-siswa-id="{{ $siswa->id }}" title="Sync Payment Status">
                                    <i class="fa-solid fa-sync-alt"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Last Sync -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if ($siswa->payment_last_check)
                                <div class="text-xs">{{ $siswa->payment_last_check->format('d M Y') }}</div>
                                <div class="text-xs text-gray-400">{{ $siswa->payment_last_check->format('H:i') }}</div>
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif

                            <!-- Sync Status Indicator -->
                            @if ($siswa->sync_status === 'synced')
                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full ml-1" title="Synced"></span>
                            @elseif($siswa->sync_status === 'failed')
                                <span class="inline-block w-2 h-2 bg-red-500 rounded-full ml-1"
                                    title="Failed: {{ $siswa->sync_error }}"></span>
                            @else
                                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full ml-1"
                                    title="Pending"></span>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <!-- View -->
                                <a href="{{ route('data.siswa.show', $siswa) }}"
                                    class="text-blue-600 hover:text-blue-900" title="View">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <!-- Edit -->
                                <a href="{{ route('data.siswa.edit', $siswa) }}"
                                    class="text-green-600 hover:text-green-900" title="Edit">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('data.siswa.destroy', $siswa) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Delete {{ $siswa->nama ?: $siswa->idyayasan }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
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
    <div class="text-center py-12">
        <i class="fa-solid fa-user-graduate text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No students found</h3>
        <p class="text-gray-500 mb-6">Try adjusting your search terms or add new students</p>
        <a href="{{ route('data.siswa.create') }}"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fa-solid fa-plus mr-1"></i>Add Student
        </a>
    </div>
@endif
