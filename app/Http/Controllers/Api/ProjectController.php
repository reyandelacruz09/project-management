<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->with(['owner:id,name', 'team:id,name', 'members:id,name'])
            ->when($request->search, function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($projects);
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        $project->members()->attach($request->user()->id, ['role' => 'manager']);

        return response()->json($project->load('owner:id,name', 'team:id,name'), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('owner:id,name', 'team', 'members:id,name', 'tasks.assignee:id,name'));
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $project->update($request->safe()->except(['has_members', 'members']));

        if ($request->boolean('has_members')) {
            $synced = collect($request->input('members', []))
                ->filter(fn ($member): bool => is_array($member) && ! empty($member['id']))
                ->mapWithKeys(fn ($member): array => [(int) $member['id'] => ['role' => $member['role'] ?? 'member']]);

            $project->members()->sync($synced);
        }

        return response()->json($project->load('owner:id,name', 'team'));
    }

    public function destroy(Project $project): JsonResponse
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return response()->json(null, 204);
    }
}
