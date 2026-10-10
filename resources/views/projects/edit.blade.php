@extends('layouts.app')

@section('title', 'Edit ' . $project->name)

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Project</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <x-project-form :project="$project" :teams="$teams" :members="$members" :statuses="$statuses" />
            </div>
        </div>
    </div>
@endsection