@extends('layouts.admin')

@section('title', 'Debug Pengawas Query')
@section('content')
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Debug Pengawas Query</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-2">User Info</h2>
            <ul class="list-disc pl-5 mb-4">
                <li>User ID: {{ $user->id }}</li>
                <li>User Name: {{ $user->name }}</li>
                <li>User Email: {{ $user->email }}</li>
                <li>User Roles: {{ implode(', ', $user->roles->pluck('name')->toArray()) }}</li>
                <li>Is Admin: {{ $user->isAdmin() ? 'Yes' : 'No' }}</li>
                <li>Can Supervise: {{ $user->canSupervise() ? 'Yes' : 'No' }}</li>
            </ul>

            @if ($guru)
                <h2 class="text-xl font-bold mb-2">Guru Info</h2>
                <ul class="list-disc pl-5 mb-4">
                    <li>Guru ID: {{ $guru->id }}</li>
                    <li>Guru Name: {{ $guru->nama_lengkap }}</li>
                    <li>Guru NIP: {{ $guru->nip }}</li>
                </ul>
            @else
                <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4">
                    <p class="text-yellow-700">No guru profile found for this user.</p>
                </div>
            @endif

            <h2 class="text-xl font-bold mb-2">Today's Assignments</h2>
            <div class="bg-gray-100 p-4 rounded mb-4">
                <h3 class="text-md font-semibold mb-2">Count: {{ count($assignments) }}</h3>
                <div class="overflow-auto">
                    <pre class="text-sm">{{ json_encode($assignmentsData, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>

            <h2 class="text-xl font-bold mb-2">Direct Assignments (pengawas_id in sesi_ruangan)</h2>
            <div class="bg-gray-100 p-4 rounded mb-4">
                <h3 class="text-md font-semibold mb-2">Count: {{ count($directAssignments) }}</h3>
                <div class="overflow-auto">
                    <pre class="text-sm">{{ json_encode($directAssignments->toArray(), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>

            <h2 class="text-xl font-bold mb-2">Pivot Assignments (pengawas_id in jadwal_ujian_sesi_ruangan)</h2>
            <div class="bg-gray-100 p-4 rounded mb-4">
                <h3 class="text-md font-semibold mb-2">Count: {{ count($pivotAssignments) }}</h3>
                <div class="overflow-auto">
                    <pre class="text-sm">{{ json_encode($pivotAssignments->toArray(), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
@endsection
