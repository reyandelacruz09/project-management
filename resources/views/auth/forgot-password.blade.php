@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div class="mb-4 text-sm text-gray-600">
        Forgot your password? No problem. Just let us know your email address and we will email you a password
        reset link that will allow you to choose a new one.
    </div>

    @if (session('status'))
        <div class="mb-4 text-sm font-medium text-green-600">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')"
            required autofocus />

        <x-input-error :messages="$errors->get('email')" class="mt-2" />

        <div class="mt-4 flex items-center justify-end">
            <x-primary-button class="ms-4">Email Password Reset Link</x-primary-button>
        </div>
    </form>
@endsection