<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('dashboard', [
            'stats' => [
                'projects' => Project::count(),
                'tasks' => Task::count(),
                'myTasks' => Task::where('user_id', $user->id)->count(),
                'teams' => Team::count(),
                'users' => User::count(),
            ],
            'recentProjects' => Project::with('owner')
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'status', 'owner_id', 'updated_at']),
            'myActiveTasks' => Task::with('project')
                ->where('user_id', $user->id)
                ->where('status', '!=', 'done')
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'status', 'priority', 'due_date', 'project_id', 'user_id']),
        ]);
    }
}
