<x-guest-layout>
    <!-- Status Session -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login.siswa.submit') }}">
        @csrf

        <!-- ID Yayasan -->
        <div>
            <x-input-label for="idyayasan" :value="__('ID Yayasan')" />
            <x-text-input id="idyayasan" class="block mt-1 w-full" type="text" name="idyayasan" :value="old('idyayasan')"
                required autofocus />
            <x-input-error :messages="$errors->get('idyayasan')" class="mt-2" />
        </div>

        <!-- Token -->
        <div class="mt-4">
            <x-input-label for="token" :value="__('Token')" />
            <x-text-input id="token" class="block mt-1 w-full" type="text" name="token" required />
            <x-input-error :messages="$errors->get('token')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Login Siswa') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
