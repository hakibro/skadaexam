@extends('layouts.admin')

@section('title', 'Buat Ujian')
@section('page-title', 'Buat Ujian')
@section('page-description', 'Buat paket ujian kosong pada tahun ajaran aktif')

@section('content')
    <form method="POST" action="{{ route('naskah.paket-ujian.store') }}">
        @include('features.naskah.paket._form')
    </form>
@endsection
