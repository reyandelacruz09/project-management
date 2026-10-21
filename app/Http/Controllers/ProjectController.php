<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['owner:id,name', 'team:id,name'])
            ->when($request->search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status']),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('projects.create', [
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'members' => User::orderBy('name')->get(['id', 'name', 'role']),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'owner_id' => auth()->id(),
        ]);

        $project->members()->attach(auth()->id(), ['role' => 'manager']);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Request $request, Project $project): View
    {
        $project->load([
            'owner:id,name',
            'team:id,name,owner_id',
            'members:id,name,role',
            'comments.user:id,name',
        ]);

        $tasks = $project->tasks()
            ->with(['assignee:id,name', 'creator:id,name', 'comments.user:id,name'])
            ->when($request->task_status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->when($request->task_priority, function ($query, $priority): void {
                $query->where('priority', $priority);
            })
            ->when($request->assignee, function ($query, $assignee): void {
                if ($assignee === 'unassigned') {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $assignee);
                }
            })
            ->latest()
            ->get();

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'taskFilters' => $request->only(['task_status', 'task_priority', 'assignee']),
            'allUsers' => User::orderBy('name')->get(['id', 'name']),
            'canManage' => $this->canManage($project),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('projects.edit', [
            'project' => $project->load(['members:id,name', 'team']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'members' => User::orderBy('name')->get(['id', 'name', 'role']),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->safe()->except(['has_members', 'members']));

        if ($request->boolean('has_members')) {
            $synced = collect($request->input('members', []))
                ->filter(fn ($member): bool => is_array($member) && ! empty($member['id']))
                ->mapWithKeys(fn ($member): array => [(int) $member['id'] => ['role' => $member['role'] ?? 'member']]);

            $project->members()->sync($synced);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    public function canManage(Project $project): bool
    {
        return Gate::allows('update', $project);
    }
}
