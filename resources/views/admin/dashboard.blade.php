<!-- filepath: resources\views\admin\dashboard.blade.php -->
@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-description', 'Overview of system statistics and quick access to modules')

@section('content')
    <div class="space-y-6">

        <!-- Welcome Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-purple-100 rounded-md p-3">
                            <i class="fa-solid fa-tachometer-alt text-purple-600 text-2xl"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Welcome back!</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ Auth::user()->name }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-users text-blue-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                <dd class="text-lg font-medium text-gray-900">{{ App\Models\User::count() }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            @if (Schema::hasTable('guru'))
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-chalkboard-user text-green-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Guru</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ App\Models\Guru::count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (Schema::hasTable('siswa'))
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-user-graduate text-purple-600 text-2xl"></i>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Total Siswa</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ App\Models\Siswa::count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-cog text-orange-600 text-2xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">System Status</dt>
                                <dd class="text-lg font-medium text-green-600">Online</dd>
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
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Frequently used actions and shortcuts</p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    @if (Auth::user() &&
                            method_exists(Auth::user(), 'hasRole') &&
                            (Auth::user()->hasRole('admin') || Auth::user()->hasRole('data')))
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
                    @endif

                    @if (Auth::user() && method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole('admin'))
                        <a href="{{ route('admin.users.create') }}"
                            class="bg-gray-50 hover:bg-gray-100 rounded-lg p-4 transition-colors">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-user-shield text-gray-600"></i>
                                <span class="text-gray-800 font-medium">Add System User</span>
                            </div>
                        </a>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection
