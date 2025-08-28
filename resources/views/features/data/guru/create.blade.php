@extends('layouts.admin')

@section('title', 'Add New Guru')
@section('page-title', 'Add New Guru')
@section('page-description', 'Create a new guru account')

@section('content')
    <div class="max-w-2xl">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('data.guru.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Guru List</span>
            </a>
        </div>

        <!-- Create Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Guru Information</h3>
            </div>

            <form action="{{ route('data.guru.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-user mr-1 text-gray-400"></i>
                        Nama Lengkap *
                    </label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required
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
                    <input type="text" name="nip" id="nip" value="{{ old('nip') }}"
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
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                        placeholder="guru@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-lock mr-1 text-gray-400"></i>
                        Password *
                    </label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                        placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-lock mr-1 text-gray-400"></i>
                        Konfirmasi Password *
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ulangi password">
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
                            <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Role determines what features the guru can access in the system.
                    </p>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('data.guru.index') }}"
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fa-solid fa-save mr-1"></i>
                        Save Guru
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
