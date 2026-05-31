@extends('layouts.admin')

@section('title', 'Edit Tahun Ajaran')
@section('page-title', 'Edit Tahun Ajaran')
@section('page-description', $tahunAjaran->nama)

@section('content')
    <form method="POST" action="{{ route('admin.tahun-ajaran.update', $tahunAjaran) }}">
        @method('PUT')
        @include('admin.tahun-ajaran._form')
    </form>
@endsection
