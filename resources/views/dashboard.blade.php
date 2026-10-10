@extends('layouts.app')

@section('title', 'Dashboard')

@section('header')
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
@endsection

@section('content')
    @php
        $statCards = [
            'projects' => 'Total Projects',
            'tasks' => 'Total Tasks',
            'myTasks' => 'My Tasks',
            'teams' => 'Teams',
        ];
    @endphp

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                @foreach ($statCards as $key => $label)
                    <div class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ $stats[$key] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Projects</h3>
                        <a href="{{ route('projects.index') }}"
                            class="text-sm font-medium text-indigo-600 hover:underline">View all</a>
                    </div>

                    <ul class="divide-y divide-gray-200">
                        @forelse ($recentProjects as $project)
                            <li class="flex items-center justify-between py-3">
                                <div>
                                    <a href="{{ route('projects.show', $project) }}"
                                        class="font-medium text-gray-900 hover:text-indigo-600">{{ $project->name }}</a>
                                    <div class="text-sm text-gray-500">{{ $project->owner->name }}</div>
                                </div>
                                <x-status-badge :status="$project->status" size="md" />
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500">No projects yet.</li>
                        @endforelse
                    </ul>
                </div>

                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">My Active Tasks</h3>

                    <ul class="divide-y divide-gray-200">
                        @forelse ($myActiveTasks as $task)
                            <li class="flex items-center justify-between py-3">
                                <div class="min-w-0">
                                    <a href="{{ route('projects.show', $task->project_id) }}"
                                        class="truncate font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a>
                                    <div class="text-sm text-gray-500">{{ $task->project->name }}</div>
                                </div>
                                <x-status-badge :status="$task->priority" size="md" :classes="match ($task->priority) {
                                    \App\Enums\TaskPriority::Urgent => 'bg-red-50 text-red-700',
                                    \App\Enums\TaskPriority::High => 'bg-orange-50 text-orange-700',
                                    default => 'bg-gray-100 text-gray-700',
                                }" />
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500">You have no open tasks.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection