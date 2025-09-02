<!-- filepath: resources\views\features\data\siswa\show.blade.php -->

@extends('layouts.admin')

@section('title', 'View Siswa: ' . ($siswa->nama ?: $siswa->idyayasan))
@section('page-title', 'View Siswa')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        Student Details: {{ $siswa->nama ?: $siswa->idyayasan }}
                    </h3>
                    <div class="flex space-x-2">
                        <a href="{{ route('data.siswa.edit', $siswa) }}"
                            class="bg-green-600 text-white px-3 py-2 rounded text-sm hover:bg-green-700">
                            <i class="fa-solid fa-edit mr-1"></i>Edit
                        </a>
                        <a href="{{ route('data.siswa.index') }}"
                            class="bg-gray-600 text-white px-3 py-2 rounded text-sm hover:bg-gray-700">
                            <i class="fa-solid fa-arrow-left mr-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-6">
                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID Yayasan</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $siswa->idyayasan }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->nama ?: '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($siswa->email)
                                <a href="mailto:{{ $siswa->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $siswa->email }}
                                </a>
                            @else
                                -
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Kelas</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->kelas ? $siswa->kelas->nama_kelas : '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status Pembayaran</dt>
                        <dd class="mt-1">
                            @php
                                $statusClass = match ($siswa->status_pembayaran) {
                                    'Lunas' => 'bg-green-100 text-green-800',
                                    'Belum Lunas' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                                $statusIcon = match ($siswa->status_pembayaran) {
                                    'Lunas' => 'fa-check-circle',
                                    'Belum Lunas' => 'fa-times-circle',
                                    default => 'fa-question-circle',
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                <i class="fa-solid {{ $statusIcon }} mr-1"></i>
                                {{ $siswa->status_pembayaran ?: 'Unknown' }}
                            </span>
                        </dd>
                    </div>

                    <!-- Password Info -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Login Password</dt>
                        <dd class="mt-1 text-sm text-gray-600">
                            <div class="flex items-center space-x-2">
                                <code class="bg-gray-100 px-2 py-1 rounded">Default: password</code>
                                <span class="text-xs text-gray-500">(Can be changed by student)</span>
                            </div>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->created_at->format('d M Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $siswa->updated_at->format('d M Y H:i') }}</dd>
                    </div>
                </dl>

                <!-- Account Status -->
                <div class="mt-8 pt-6 border-t">
                    <h4 class="text-sm font-medium text-gray-900 mb-4">Account Information</h4>
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                            <dd class="mt-1">
                                @if ($siswa->isActive())
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fa-solid fa-check-circle mr-1"></i>Active
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fa-solid fa-times-circle mr-1"></i>Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Can Take Exam</dt>
                            <dd class="mt-1">
                                @if ($siswa->canTakeExam())
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        <i class="fa-solid fa-check-circle mr-1"></i>Yes
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        <i class="fa-solid fa-times-circle mr-1"></i>No (Payment required)
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                <div class="flex justify-between">
                    <div class="flex space-x-3">
                        <a href="{{ route('data.siswa.edit', $siswa) }}"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            <i class="fa-solid fa-edit mr-2"></i>Edit Student
                        </a>

                        <button onclick="resetPassword({{ $siswa->id }})"
                            class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                            <i class="fa-solid fa-key mr-2"></i>Reset Password
                        </button>
                    </div>

                    <div class="flex space-x-3">
                        <a href="{{ route('data.siswa.index') }}"
                            class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                            <i class="fa-solid fa-list mr-2"></i>Back to List
                        </a>

                        <form action="{{ route('data.siswa.destroy', $siswa) }}" method="POST" class="inline"
                            onsubmit="return confirm('Are you sure you want to delete this student?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                                <i class="fa-solid fa-trash mr-2"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for additional functionality -->
    <script>
        function resetPassword(siswaId) {
            if (confirm('Reset password to default "password" for this student?')) {
                // You can implement password reset functionality here
                fetch(`/data/siswa/${siswaId}/reset-password`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Password reset successfully!');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Reset error:', error);
                        alert('Error resetting password');
                    });
            }
        }
    </script>
@endsection
