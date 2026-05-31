@extends('layouts.admin')

@section('title', 'Edit Paket Ujian')
@section('page-title', 'Edit Paket Ujian')
@section('page-description', $paketUjian->nama)

@section('content')
    <form method="POST" action="{{ route('naskah.paket-ujian.update', $paketUjian) }}">
        @method('PUT')
        @include('features.naskah.paket._form', ['activeYear' => $paketUjian->tahunAjaran])
    </form>
@endsection
