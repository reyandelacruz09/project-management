<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json(
            $project->tasks()->with('assignee:id,name', 'creator:id,name')->latest()->get()
        );
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $task = $project->tasks()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json($task->load('assignee:id,name', 'creator:id,name'), 201);
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        return response()->json($task->load('assignee:id,name', 'creator:id,name', 'comments.user:id,name'));
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $task->update($request->validated());

        return response()->json($task->load('assignee:id,name', 'creator:id,name'));
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json(null, 204);
    }
}
