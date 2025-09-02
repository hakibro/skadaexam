<!-- filepath: resources\views\features\data\dashboard.blade.php -->
@extends('layouts.admin')

@section('title', 'Data Management Dashboard')
@section('page-title', 'Data Management')
@section('page-description', 'Manage guru, siswa, and kelas data')

@section('content')
    <div class="space-y-6">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-chalkboard-user text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Guru</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ App\Models\Guru::count() ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-user-graduate text-green-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ App\Models\Siswa::count() ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-door-open text-purple-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Kelas</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ App\Models\Kelas::count() ?? 0 }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('data.guru.create') }}"
                        class="bg-blue-50 hover:bg-blue-100 rounded-lg p-4 transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-plus-circle text-blue-600"></i>
                            <span class="text-blue-800 font-medium">Add New Guru</span>
                        </div>
                    </a>
                    {{-- <a href="{{ route('data.siswa.create') }}"
                        class="bg-green-50 hover:bg-green-100 rounded-lg p-4 transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-user-plus text-green-600"></i>
                            <span class="text-green-800 font-medium">Add New Siswa</span>
                        </div>
                    </a>
                    <a href="{{ route('data.kelas.create') }}"
                        class="bg-purple-50 hover:bg-purple-100 rounded-lg p-4 transition-colors">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-door-open text-purple-600"></i>
                            <span class="text-purple-800 font-medium">Add New Kelas</span>
                        </div>
                    </a> --}}
                </div>
            </div>
        </div>

    </div>
@endsection
