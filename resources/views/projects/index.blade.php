@extends('layouts.app')

@section('title', 'Projects')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Projects</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('projects.index') }}" class="w-full sm:w-64">
                    <x-text-input type="text" name="search" :value="$filters['search'] ?? null" placeholder="Search projects..." />
                </form>

                <a href="{{ route('projects.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    New Project
                </a>
            </div>

            @if ($projects->isEmpty())
                <div class="rounded-lg bg-white p-10 text-center text-gray-500 shadow-sm">
                    No projects found.
                </div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($projects as $project)
                    <a href="{{ route('projects.show', $project) }}"
                        class="group rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $project->name }}</h3>
                            <x-status-badge :status="$project->status" />
                        </div>

                        <p class="mt-2 text-sm text-gray-500">
                            <span class="line-clamp-2">{{ $project->description }}</span>
                        </p>

                        <div class="mt-4 text-xs text-gray-400">{{ $project->owner->name }}</div>
                    </a>
                @endforeach
            </div>

            <div class="flex items-center justify-center gap-1">
                {{ $projects->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection