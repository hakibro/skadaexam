<!-- filepath: resources\views\features\data\guru\show.blade.php -->
@extends('layouts.admin')

@section('title', 'Guru Detail')
@section('page-title', 'Guru Detail')
@section('page-description', 'View guru account information')

@section('content')
    <div class="max-w-4xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('data.guru.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Guru List</span>
            </a>
        </div>

        <!-- Guru Detail Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                <div class="flex items-center space-x-4">
                    <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-chalkboard-user text-blue-600 text-2xl"></i>
                    </div>
                    <div class="text-white">
                        <h1 class="text-2xl font-bold">{{ $guru->nama }}</h1>
                        <p class="text-blue-100">{{ $guru->email }}</p>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Basic Information</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-user text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">Full Name</p>
                                    <p class="font-medium">{{ $guru->nama }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-id-card text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">NIP</p>
                                    <p class="font-medium">{{ $guru->nip ?: '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-envelope text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">Email</p>
                                    <p class="font-medium">{{ $guru->email }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-user-tag text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">Role</p>
                                    @php
                                        $roleColors = [
                                            'guru' => 'bg-green-100 text-green-800',
                                            'data' => 'bg-blue-100 text-blue-800',
                                            'naskah' => 'bg-purple-100 text-purple-800',
                                            'pengawas' => 'bg-yellow-100 text-yellow-800',
                                            'koordinator' => 'bg-red-100 text-red-800',
                                            'ruangan' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $colorClass = $roleColors[$guru->role] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $colorClass }}">
                                        {{ $guru->role_label }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Account Information</h3>

                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-calendar-plus text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">Created At</p>
                                    <p class="font-medium">{{ $guru->created_at->format('d M Y, H:i') }}</p>
                                    <p class="text-xs text-gray-400">{{ $guru->created_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-clock text-gray-400"></i>
                                <div>
                                    <p class="text-sm text-gray-500">Last Updated</p>
                                    <p class="font-medium">{{ $guru->updated_at->format('d M Y, H:i') }}</p>
                                    <p class="text-xs text-gray-400">{{ $guru->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>

                            @if ($guru->created_at != $guru->updated_at)
                                <div class="bg-yellow-50 p-3 rounded-lg">
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-exclamation-triangle text-yellow-600"></i>
                                        <p class="text-sm text-yellow-700">This account has been modified since creation</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-4">
                <a href="{{ route('data.guru.edit', $guru) }}"
                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fa-solid fa-edit mr-1"></i>
                    Edit Guru
                </a>
                <form action="{{ route('data.guru.destroy', $guru) }}" method="POST" class="inline"
                    onsubmit="return confirm('Are you sure you want to delete guru {{ $guru->nama }}? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                        <i class="fa-solid fa-trash mr-1"></i>
                        Delete Guru
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection
