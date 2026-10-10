@extends('layouts.app')

@section('title', $team->name)

@section('header')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $team->name }}</h2>
            <div class="text-sm text-gray-500">Owned by {{ $team->owner->name }}</div>
        </div>

        @if ($canManage)
            <form method="POST" action="{{ route('teams.destroy', $team) }}"
                onsubmit="return confirm('Delete this team?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                    Delete
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-flash-message />
            <x-flash-message type="error" />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">About</h3>

                        @if ($canManage)
                            <form method="POST" action="{{ route('teams.update', $team) }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <x-input-label for="name" value="Name" />

                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                        :value="old('name', $team->name)" required />

                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="description" value="Description" />

                                    <textarea id="description" name="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $team->description) }}</textarea>

                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>

                                <x-primary-button>Save changes</x-primary-button>
                            </form>
                        @else
                            <p class="text-gray-700">{{ $team->description ?? 'No description.' }}</p>
                        @endif
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Projects</h3>

                        @if ($team->projects->isEmpty())
                            <p class="text-sm text-gray-500">No projects assigned yet.</p>
                        @else
                            <ul class="divide-y divide-gray-200">
                                @foreach ($team->projects as $project)
                                    <li class="flex items-center justify-between py-3">
                                        <a href="{{ route('projects.show', $project) }}"
                                            class="font-medium text-gray-900 hover:text-indigo-600">{{ $project->name }}</a>
                                        <x-status-badge :status="$project->status" />
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Members ({{ $team->members->count() }})</h3>
                    </div>

                    @if ($canManage && $users->isNotEmpty())
                        <form method="POST" action="{{ route('teams.update', $team) }}" class="mb-4 flex gap-2">
                            @csrf
                            @method('PUT')

                            <select name="members[]"
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Choose a user...</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>

                            <x-primary-button>Add</x-primary-button>
                        </form>
                    @endif

                    <ul class="space-y-3">
                        @foreach ($team->members as $member)
                            <li class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $member->name }}</span>

                                @if ($canManage && $member->id !== $team->owner_id)
                                    <form method="POST" action="{{ route('teams.update', $team) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="remove" value="{{ $member->id }}">

                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700">Remove</button>
                                    </form>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection