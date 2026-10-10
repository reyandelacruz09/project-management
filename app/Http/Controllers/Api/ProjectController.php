<?php

namespace App\Http\Controllers\Api;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->with(['owner:id,name', 'team:id,name', 'members:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($projects);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', ProjectStatus::values())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]);

        $project = Project::create([
            ...$data,
            'owner_id' => $request->user()->id,
        ]);

        $project->members()->attach($request->user()->id, ['role' => 'manager']);

        return response()->json($project->load('owner:id,name', 'team:id,name'), 201);
    }

    public function show(Project $project): JsonResponse
    {
        return response()->json($project->load('owner:id,name', 'team', 'members:id,name', 'tasks.assignee:id,name'));
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', ProjectStatus::values())],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]);

        $project->update($data);

        return response()->json($project->load('owner:id,name', 'team'));
    }

    public function destroy(Project $project): JsonResponse
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
