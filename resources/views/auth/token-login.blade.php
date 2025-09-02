@extends('layouts.auth')

@section('title', 'Login Ujian')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-100">
        <div class="w-full max-w-md p-8 space-y-8 bg-white rounded-lg shadow-md">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-900">SKADA EXAM</h1>
                <p class="mt-2 text-gray-600">Login dengan Token Ujian</p>
            </div>

            @if (session('error'))
                <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <form class="mt-8 space-y-6" method="POST" action="{{ route('ujian.login') }}">
                @csrf

                <div class="rounded-md shadow-sm">
                    <div>
                        <label for="token" class="sr-only">Token Ujian</label>
                        <input id="token" name="token" type="text" required
                            class="appearance-none rounded-lg relative block w-full px-3 py-4 border @error('token') border-red-500 @else border-gray-300 @enderror placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-xl uppercase text-center tracking-widest"
                            placeholder="Masukkan Token" value="{{ old('token') }}" maxlength="6" autofocus>

                        @error('token')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-lg font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Token diberikan oleh pengawas ujian
                </p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto format to uppercase
        document.getElementById('token').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
@endpush
