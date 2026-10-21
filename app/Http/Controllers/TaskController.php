<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function store(StoreTaskRequest $request, Project $project): RedirectResponse
    {
        $project->tasks()->create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Task created.');
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        $task->update($request->validated());

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        Gate::authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    public function canManage(Project $project, ?Task $task = null): bool
    {
        if ($task !== null) {
            return Gate::allows('delete', $task);
        }

        return Gate::allows('update', $project);
    }
}
