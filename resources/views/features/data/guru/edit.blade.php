@extends('layouts.admin')

@section('title', 'Edit Guru')
@section('page-title', 'Edit Guru')
@section('page-description', 'Update guru account information')

@section('content')
    <div class="max-w-2xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('data.guru.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Guru List</span>
            </a>
        </div>

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Guru Information</h3>
                <p class="text-sm text-gray-600">Editing: {{ $guru->nama }}</p>
            </div>

            <form action="{{ route('data.guru.update', $guru) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-user mr-1 text-gray-400"></i>
                        Nama Lengkap *
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama', $guru->nama) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                        placeholder="Masukkan nama lengkap guru">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- NIP -->
                <div>
                    <label for="nip" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-id-card mr-1 text-gray-400"></i>
                        NIP (Opsional)
                    </label>
                    <input type="text" name="nip" id="nip" value="{{ old('nip', $guru->nip) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('nip') border-red-500 @enderror"
                        placeholder="Masukkan NIP guru">
                    @error('nip')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-envelope mr-1 text-gray-400"></i>
                        Email *
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $guru->email) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        placeholder="guru@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password (Optional for Edit) -->
                <div class="bg-yellow-50 p-4 rounded-md">
                    <h4 class="text-sm font-medium text-yellow-800 mb-3">
                        <i class="fa-solid fa-lock mr-1"></i>
                        Change Password (Optional)
                    </h4>
                    <p class="text-sm text-yellow-700 mb-4">Leave blank if you don't want to change the password.</p>

                    <div class="space-y-4">
                        <!-- New Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                New Password
                            </label>
                            <input type="password" name="password" id="password"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                                placeholder="Enter new password (min 8 characters)">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                Confirm New Password
                            </label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Repeat new password">
                        </div>
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-user-tag mr-1 text-gray-400"></i>
                        Role/Access Level *
                    </label>
                    <select name="role" id="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror">
                        <option value="">Pilih Role</option>
                        @foreach ($roleOptions as $value => $label)
                            <option value="{{ $value }}"
                                {{ old('role', $guru->role) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Current role: <span class="font-medium text-blue-600">{{ $guru->role_label }}</span>
                    </p>
                </div>

                <!-- Account Info -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h4 class="text-sm font-medium text-gray-800 mb-2">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Account Information
                    </h4>
                    <div class="text-sm text-gray-600 space-y-1">
                        <p><strong>Created:</strong> {{ $guru->created_at->format('d M Y H:i') }}</p>
                        <p><strong>Last Updated:</strong> {{ $guru->updated_at->format('d M Y H:i') }}</p>
                        @if ($guru->created_at != $guru->updated_at)
                            <p class="text-yellow-600"><i class="fa-solid fa-clock mr-1"></i>This account has been modified
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('data.guru.index') }}"
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </a>
                    <a href="{{ route('data.guru.show', $guru) }}"
                        class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 transition-colors">
                        <i class="fa-solid fa-eye mr-1"></i>
                        View Detail
                    </a>
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i>
                        Update Guru
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
