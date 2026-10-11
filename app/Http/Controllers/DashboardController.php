<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|Response
    {
        $user = auth()->user();
        $cacheKey = "dashboard:user:{$user->id}";

        if ($request->hasHeader('X-Dashboard-Refresh')) {
            Cache::forget($cacheKey);
        }

        $dashboard = Cache::remember($cacheKey, now()->addMinute(), function () use ($user) {
            return [
                'stats' => [
                    'projects' => Project::count(),
                    'tasks' => Task::count(),
                    'myTasks' => Task::where('user_id', $user->id)->count(),
                    'teams' => Team::count(),
                    'users' => User::count(),
                    'completedProjects' => Project::where('status', ProjectStatus::Completed->value)->count(),
                    'overdueTasks' => Task::where('status', '!=', TaskStatus::Done->value)
                        ->whereDate('due_date', '<', today())
                        ->count(),
                ],
                'projectStatusChart' => $this->statusChart(
                    ProjectStatus::cases(),
                    Project::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
                    [
                        'planning' => 'bg-gray-500',
                        'active' => 'bg-green-500',
                        'on_hold' => 'bg-amber-500',
                        'completed' => 'bg-indigo-500',
                    ],
                ),
                'taskStatusChart' => $this->statusChart(
                    TaskStatus::cases(),
                    Task::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
                    [
                        'todo' => 'bg-gray-500',
                        'in_progress' => 'bg-blue-500',
                        'review' => 'bg-purple-500',
                        'done' => 'bg-green-500',
                    ],
                ),
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
            ];
        });

        return view('dashboard', $dashboard);
    }

    private function statusChart(array $statuses, Collection $counts, array $colors): array
    {
        $total = (int) $counts->sum();

        return collect($statuses)->map(function ($status) use ($counts, $colors, $total) {
            $count = (int) $counts->get($status->value, 0);

            return [
                'label' => $status->label(),
                'count' => $count,
                'percentage' => $total > 0 ? (int) round($count / $total * 100) : 0,
                'color' => $colors[$status->value],
            ];
        })->all();
    }
}
