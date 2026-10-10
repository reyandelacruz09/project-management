@extends('layouts.app')

@section('title', 'New Team')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">New Team</h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <form method="POST" action="{{ route('teams.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Name" />

                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')"
                            required autofocus />

                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description" />

                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <x-input-label value="Members" />

                        <div class="mt-2 max-h-64 space-y-2 overflow-y-auto">
                            @foreach ($users as $index => $user)
                                <label
                                    class="flex cursor-pointer items-center space-x-3 rounded-md border border-gray-200 px-3 py-2">
                                    <input type="checkbox" name="members[]" value="{{ $user->id }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @checked(in_array($user->id, (array) old('members', [])))>
                                    <span class="text-sm text-gray-900">{{ $user->name }}</span>
                                </label>
                            @endforeach
                        </div>

                        <x-input-error :messages="$errors->get('members')" class="mt-2" />
                    </div>

                    <x-primary-button>Create Team</x-primary-button>
                </form>
            </div>
        </div>
    </div>
@endsection