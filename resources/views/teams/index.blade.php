@extends('layouts.app')

@section('title', 'Teams')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Teams</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end">
                <a href="{{ route('teams.create') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    New Team
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($teams as $team)
                    <a href="{{ route('teams.show', $team) }}"
                        class="group rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md">
                        <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600">{{ $team->name }}</h3>

                        @if ($team->description)
                            <p class="mt-2 text-sm text-gray-500">
                                <span class="line-clamp-2">{{ $team->description }}</span>
                            </p>
                        @endif

                        <div class="mt-4 flex items-center justify-between text-xs text-gray-400">
                            <span>{{ $team->owner->name }}</span>
                            <span>{{ $team->members_count }} members · {{ $team->projects_count }} projects</span>
                        </div>
                    </a>
                @empty
                    <div class="rounded-lg bg-white p-10 text-center text-gray-500 shadow-sm">
                        No teams found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection