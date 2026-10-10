<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'in:'.implode(',', TaskStatus::values())],
            'priority' => ['required', 'string', 'in:'.implode(',', TaskPriority::values())],
            'user_id' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        $project->tasks()->create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Task created.');
    }

    public function update(Request $request, Project $project, Task $task): RedirectResponse
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

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Project $project, Task $task): RedirectResponse
    {
        abort_unless($task->project_id === $project->id, 404);

        abort_unless($this->canManage($project), 403);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    protected function canManage(Project $project): bool
    {
        return app(ProjectController::class)->canManage($project) ||
            $project->tasks()->where('id', request()->route('task'))->value('created_by') === auth()->id();
    }
}
