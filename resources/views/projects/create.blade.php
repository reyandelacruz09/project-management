@extends('layouts.app')

@section('title', 'New Project')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">New Project</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <x-project-form :teams="$teams" :members="$members" :statuses="$statuses" />
            </div>
        </div>
    </div>
@endsection