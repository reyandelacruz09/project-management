@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')
    <div class="mb-4 text-sm text-gray-600">
        This is a secure area of the application. Please confirm your password before continuing.
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full"
                required autofocus autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button class="ms-4">Confirm</x-primary-button>
        </div>
    </form>
@endsection