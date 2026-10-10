@extends('layouts.guest')

@section('title', 'Log in')

@section('content')
    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />

            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email')" required autofocus autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 block">
            <label class="flex items-center">
                <x-checkbox name="remember" value="1" />
                <span class="ms-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="mt-4 flex items-center justify-end">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Forgot your password?
                </a>
            @endif

            <x-primary-button class="ms-4">Log in</x-primary-button>
        </div>
    </form>
@endsection