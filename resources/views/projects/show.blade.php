@extends('layouts.app')

@section('title', $project->name)

@section('header')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $project->name }}</h2>
            @if ($project->team)
                <div class="text-sm text-gray-500">{{ $project->team->name }}</div>
            @endif
        </div>

        @if ($canManage)
            <div class="flex gap-2">
                <a href="{{ route('projects.edit', $project) }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Edit
                </a>

                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                    onsubmit="return confirm('Delete this project? All its tasks will be removed.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                        Delete
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection

@section('content')
    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-status-badge :status="$project->status" size="md" />
                            <span class="text-sm text-gray-500">Owned by {{ $project->owner->name }}</span>
                            @if ($project->start_date)
                                <span class="text-sm text-gray-500">
                                    {{ $project->start_date->format('Y-m-d') }} →
                                    {{ $project->end_date?->format('Y-m-d') ?? 'ongoing' }}
                                </span>
                            @endif
                        </div>

                        @if ($project->description)
                            <p class="mt-4 whitespace-pre-wrap text-gray-700">{{ $project->description }}</p>
                        @endif

                        <div class="mt-6">
                            <x-comment-section commentable-type="App\Models\Project" :commentable-id="$project->id"
                                :comments="$project->comments" />
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                            <h3 class="text-lg font-semibold text-gray-900">Tasks ({{ $tasks->count() }})</h3>

                            <form method="GET" action="{{ route('projects.show', $project) }}" class="flex flex-wrap items-center gap-2">
                                <select name="task_status" onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All statuses</option>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected(($taskFilters['task_status'] ?? null) === $status->value)>
                                            {{ $status->label() }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="task_priority" onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All priorities</option>
                                    @foreach ($priorities as $priority)
                                        <option value="{{ $priority->value }}" @selected(($taskFilters['task_priority'] ?? null) === $priority->value)>
                                            {{ $priority->label() }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="assignee" onchange="this.form.submit()"
                                    class="rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All assignees</option>
                                    <option value="unassigned" @selected(($taskFilters['assignee'] ?? null) === 'unassigned')>Unassigned</option>
                                    @foreach ($allUsers as $user)
                                        <option value="{{ $user->id }}" @selected((string) ($taskFilters['assignee'] ?? '') === (string) $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if (! empty(array_filter($taskFilters ?? [])))
                                    <a href="{{ route('projects.show', $project) }}" class="text-xs text-gray-500 hover:text-gray-700">Reset</a>
                                @endif
                            </form>
                        </div>

                        <div class="space-y-4">
                            <form method="POST" action="{{ route('tasks.store', $project) }}"
                                class="space-y-3 rounded-lg bg-gray-50 p-4">
                                @csrf

                                <div class="grid gap-3">
                                    <x-text-input name="title" type="text" placeholder="Task title" :value="old('title')"
                                        required />
                                    <x-input-error :messages="$errors->get('title')" />

                                    <textarea name="description" rows="2" placeholder="Description (optional)"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" />
                                </div>

                                <div class="grid gap-3 sm:grid-cols-4">
                                    <select name="status"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" @selected($status === \App\Enums\TaskStatus::Todo)>
                                                {{ $status->label() }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="priority"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->value }}" @selected($priority === \App\Enums\TaskPriority::Medium)>
                                                {{ $priority->label() }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="user_id"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Unassigned</option>
                                        @foreach ($allUsers as $user)
                                            <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <x-primary-button>Add Task</x-primary-button>
                            </form>

                            @if ($tasks->isEmpty())
                                <p class="text-sm text-gray-500">No tasks yet. Add one above.</p>
                            @endif

                            <ul class="space-y-4">
                                @foreach ($tasks as $task)
                                    <li class="rounded-lg border border-gray-200 bg-white p-4">
                                        <div class="flex flex-col gap-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <x-status-badge :status="$task->priority" />
                                                        <x-status-badge :status="$task->status" />

                                                        @if ($task->due_date)
                                                            <span class="text-xs text-gray-400">
                                                                Due {{ $task->due_date->format('Y-m-d') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <div class="mt-1 font-medium text-gray-900">{{ $task->title }}</div>

                                                    @if ($task->description)
                                                        <p class="mt-1 text-sm text-gray-500">{{ $task->description }}</p>
                                                    @endif

                                                    <div class="mt-1 text-xs text-gray-400">
                                                        Assigned to {{ $task->assignee->name ?? 'nobody' }}
                                                        @if ($task->creator)
                                                            · created by {{ $task->creator->name }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex shrink-0 items-center gap-2">
                                                    <form method="POST" action="{{ route('tasks.update', [$project, $task]) }}"
                                                        onchange="this.submit()">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="status"
                                                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            @foreach ($statuses as $status)
                                                                <option value="{{ $status->value }}"
                                                                    @selected($status === $task->status)>{{ $status->label() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>

                                                    <form method="POST" action="{{ route('tasks.update', [$project, $task]) }}"
                                                        onchange="this.submit()">
                                                        @csrf
                                                        @method('PATCH')
                                                        <select name="user_id"
                                                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">Nobody</option>
                                                            @foreach ($allUsers as $user)
                                                                <option value="{{ $user->id }}"
                                                                    @selected($task->user_id === $user->id)>{{ $user->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </form>

                                                    @if ($canManage || $task->created_by === auth()->id())
                                                        <form method="POST" action="{{ route('tasks.destroy', [$project, $task]) }}"
                                                            onsubmit="return confirm('Delete this task? Its comments will also be removed.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-sm text-red-500 hover:text-red-700">Delete</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>

                                            <x-comment-section commentable-type="App\Models\Task" :commentable-id="$task->id"
                                                :comments="$task->comments" />
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Members ({{ $project->members->count() }})</h3>

                        <ul class="space-y-3">
                            @foreach ($project->members as $member)
                                <li class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ $member->name }}</span>

                                    @php $role = $member->pivot?->role ?? 'member'; @endphp

                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $role === 'manager' ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $role }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection