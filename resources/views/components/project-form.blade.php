@props(['project' => null, 'teams', 'members', 'statuses'])

@php
    $isEditing = $project !== null;
    $oldMembers = collect(old('members', []))
        ->filter(fn ($member) => is_array($member) && ! empty($member['id']))
        ->keyBy('id');
    $selectedMembers = $isEditing
        ? $project->members->mapWithKeys(fn ($member) => [$member->id => $member->pivot?->role ?? 'member'])
        : collect();
    $selected = $oldMembers->isNotEmpty() ? $oldMembers->map(fn ($member) => $member['role'] ?? 'member') : $selectedMembers;
@endphp

<form method="POST" action="{{ $isEditing ? route('projects.update', $project) : route('projects.store') }}" class="space-y-6">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <div>
        <x-input-label for="name" value="Name" />

        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $project?->name)"
            required autofocus />

        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />

        <textarea id="description" name="description" rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project?->description) }}</textarea>

        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="status" value="Status" />

            <select id="status" name="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}"
                        @selected(old('status', $project?->status?->value ?? \App\Enums\ProjectStatus::Planning->value) === $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="team_id" value="Team" />

            <select id="team_id" name="team_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">No team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) old('team_id', $project?->team_id) === (string) $team->id)>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>

            <x-input-error :messages="$errors->get('team_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="start_date" value="Start date" />

            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                :value="old('start_date', $project?->start_date?->format('Y-m-d'))" />

            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="end_date" value="End date" />

            <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                :value="old('end_date', $project?->end_date?->format('Y-m-d'))" />

            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
        </div>
    </div>

    @if ($isEditing)
        <div>
            <x-input-label value="Members" />

            <input type="hidden" name="has_members" value="1">

            <div class="mt-2 space-y-2">
                @foreach ($members as $index => $member)
                    @php $selectedRole = $selected->get($member->id); @endphp

                    <div class="flex items-center justify-between rounded-md border border-gray-200 px-3 py-2">
                        <label class="flex cursor-pointer items-center space-x-2">
                            <input type="checkbox" name="members[{{ $index }}][id]" value="{{ $member->id }}"
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                @checked($selectedRole !== null)>
                            <span class="text-sm text-gray-900">{{ $member->name }}</span>
                        </label>

                        <select name="members[{{ $index }}][role]"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="member" @selected(($selectedRole ?? 'member') === 'member')>Member</option>
                            <option value="manager" @selected($selectedRole === 'manager')>Manager</option>
                        </select>
                    </div>
                @endforeach
            </div>

            <x-input-error :messages="$errors->get('members')" class="mt-2" />
        </div>
    @endif

    <div class="flex items-center gap-4">
        <x-primary-button>{{ $isEditing ? 'Update Project' : 'Create Project' }}</x-primary-button>
    </div>
</form>