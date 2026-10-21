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
            'completedProjects' => 'Completed Projects',
            'overdueTasks' => 'Overdue Tasks',
        ];
    @endphp

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <span>Dashboard updates automatically every 30 seconds.</span>
                <span id="dashboard-updated-at" aria-live="polite">Updated {{ now()->format('g:i:s A') }}</span>
            </div>

            <div data-dashboard-data>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
                    @foreach ($statCards as $key => $label)
                        <div class="rounded-lg bg-white p-5 shadow-sm">
                            <div class="text-sm font-medium text-gray-500">{{ $label }}</div>
                            <div class="mt-1 text-3xl font-bold {{ $key === 'overdueTasks' && $stats[$key] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $stats[$key] }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    @foreach ([['title' => 'Projects by status', 'chart' => $projectStatusChart], ['title' => 'Tasks by status', 'chart' => $taskStatusChart]] as $statusSummary)
                        <section class="rounded-lg bg-white p-6 shadow-sm" aria-label="{{ $statusSummary['title'] }} chart">
                            <h3 class="mb-5 text-lg font-semibold text-gray-900">{{ $statusSummary['title'] }}</h3>

                            <div class="space-y-4">
                                @foreach ($statusSummary['chart'] as $status)
                                    <div>
                                        <div class="mb-1 flex items-center justify-between text-sm">
                                            <span class="font-medium text-gray-700">{{ $status['label'] }}</span>
                                            <span class="text-gray-500">{{ $status['count'] }} <span class="sr-only">items,</span>({{ $status['percentage'] }}%)</span>
                                        </div>
                                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100"
                                            role="progressbar"
                                            aria-label="{{ $status['label'] }}"
                                            aria-valuemin="0"
                                            aria-valuemax="100"
                                            aria-valuenow="{{ $status['percentage'] }}">
                                            <div class="h-full rounded-full {{ $status['color'] }} transition-all duration-500"
                                                style="width: {{ $status['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
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
                                    <x-status-badge :status="$task->priority" size="md" />
                                </li>
                            @empty
                                <li class="py-3 text-sm text-gray-500">You have no open tasks.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dashboardInterval = 30_000;

        window.setInterval(async () => {
            if (document.hidden) return;

            try {
                const response = await fetch(window.location.href, {
                    headers: {
                        'X-Dashboard-Refresh': '1',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) return;

                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const currentData = document.querySelector('[data-dashboard-data]');
                const refreshedData = page.querySelector('[data-dashboard-data]');

                if (currentData && refreshedData) {
                    currentData.innerHTML = refreshedData.innerHTML;
                    document.getElementById('dashboard-updated-at').textContent =
                        `Updated ${new Date().toLocaleTimeString()}`;
                }
            } catch (error) {
                console.error('Dashboard refresh failed.', error);
            }
        }, dashboardInterval);
    </script>
@endsection
