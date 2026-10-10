@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <div>
            <x-input-label for="email" value="Email" />

            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $email)" required autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                required autofocus autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Confirm Password" />

            <x-text-input id="password_confirmation" name="password_confirmation" type="password"
                class="mt-1 block w-full" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button class="ms-4">Reset Password</x-primary-button>
        </div>
    </form>
@endsection