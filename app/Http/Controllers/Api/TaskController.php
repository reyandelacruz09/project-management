<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Project $project): JsonResponse
    {
        return response()->json(
            $project->tasks()->with('assignee:id,name', 'creator:id,name')->latest()->get()
        );
    }

    public function store(Request $request, Project $project): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', TaskStatus::values())],
            'priority' => ['required', 'string', 'in:'.implode(',', TaskPriority::values())],
            'user_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task = $project->tasks()->create([
            ...$data,
            'created_by' => $request->user()->id,
        ]);

        return response()->json($task->load('assignee:id,name', 'creator:id,name'), 201);
    }

    public function show(Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        return response()->json($task->load('assignee:id,name', 'creator:id,name', 'comments.user:id,name'));
    }

    public function update(Request $request, Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:'.implode(',', TaskStatus::values())],
            'priority' => ['sometimes', 'string', 'in:'.implode(',', TaskPriority::values())],
            'user_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $task->update($data);

        return response()->json($task->load('assignee:id,name', 'creator:id,name'));
    }

    public function destroy(Project $project, Task $task): JsonResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $task->delete();

        return response()->json(null, 204);
    }
}
