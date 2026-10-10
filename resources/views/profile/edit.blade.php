@extends('layouts.app')

@section('title', 'Profile')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Profile</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <x-profile.update-profile-information-form class="max-w-xl" />
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <x-profile.update-password-form class="max-w-xl" />
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <x-profile.delete-user-form class="max-w-xl" />
            </div>
        </div>
    </div>
@endsection