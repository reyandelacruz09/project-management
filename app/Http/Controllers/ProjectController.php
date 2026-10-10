<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with(['owner:id,name', 'team:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('projects.index', [
            'projects' => $projects,
            'filters' => $request->only(['search']),
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

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $project = Project::create([
            ...$data,
            'owner_id' => auth()->id(),
        ]);

        $project->members()->attach(auth()->id(), ['role' => 'manager']);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created.');
    }

    public function show(Project $project): View
    {
        $project->load([
            'owner:id,name',
            'team:id,name,owner_id',
            'members:id,name,role',
            'comments.user:id,name',
        ]);

        $tasks = $project->tasks()
            ->with(['assignee:id,name', 'creator:id,name', 'comments.user:id,name'])
            ->latest()
            ->get();

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'allUsers' => User::orderBy('name')->get(['id', 'name']),
            'canManage' => $this->canManage($project),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function edit(Project $project): View
    {
        abort_unless($this->canManage($project), 403);

        return view('projects.edit', [
            'project' => $project->load(['members:id,name', 'team']),
            'teams' => Team::orderBy('name')->get(['id', 'name']),
            'members' => User::orderBy('name')->get(['id', 'name', 'role']),
            'statuses' => ProjectStatus::cases(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        abort_unless($this->canManage($project), 403);

        $project->update($this->validated($request));

        if ($request->boolean('has_members')) {
            $synced = collect($request->input('members', []))
                ->mapWithKeys(fn ($member) => [$member['id'] => ['role' => $member['role'] ?? 'member']]);

            $project->members()->sync($synced);
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        abort_unless($this->canManage($project), 403);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', ProjectStatus::values())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]);
    }

    protected function canManage(Project $project): bool
    {
        $user = auth()->user();

        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($project->owner_id === $user->id) {
            return true;
        }

        $role = $project->members()->where('users.id', $user->id)->value('project_user.role');

        return $role === 'manager';
    }
}
