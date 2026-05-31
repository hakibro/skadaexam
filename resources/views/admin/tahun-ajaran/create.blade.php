@extends('layouts.admin')

@section('title', 'Tambah Tahun Ajaran')
@section('page-title', 'Tambah Tahun Ajaran')
@section('page-description', 'Buat tahun ajaran baru')

@section('content')
    <form method="POST" action="{{ route('admin.tahun-ajaran.store') }}">
        @include('admin.tahun-ajaran._form')
    </form>
@endsection
