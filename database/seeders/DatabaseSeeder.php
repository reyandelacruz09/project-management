<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $manager = User::factory()->create([
            'name' => 'Project Manager',
            'email' => 'manager@example.com',
            'role' => UserRole::Manager,
        ]);

        $members = User::factory()->count(6)->create([
            'role' => UserRole::Member,
        ]);

        $allUsers = [$admin, $manager, ...$members];

        $teams = collect();
        foreach ([1, 2, 3] as $index) {
            $owner = $allUsers[array_rand($allUsers)];
            $team = Team::factory()->create([
                'name' => ['Platform', 'Design', 'Marketing'][$index - 1] . ' Crew',
                'owner_id' => $owner->id,
            ]);

            $team->members()->attach($allUsers);

            $teams->push($team);
        }

        $projects = collect();
        foreach ($teams as $team) {
            foreach (range(1, 2) as $i) {
                $owner = $team->members->random();

                $project = Project::factory()->create([
                    'name' => $team->name . ' ' . ['Site Redesign', 'Mobile App', 'API Platform', 'Internal Tools', 'Launch Campaign', 'Research'][random_int(0, 5)],
                    'team_id' => $team->id,
                    'owner_id' => $owner->id,
                ]);

                $project->members()->attach(
                    $team->members->mapWithKeys(fn ($member) => [
                        $member->id => ['role' => $member->id === $owner->id ? 'manager' : 'member'],
                    ])
                );

                foreach (range(1, random_int(3, 5)) as $j) {
                    $task = Task::factory()->create([
                        'project_id' => $project->id,
                        'user_id' => $project->members->random()->id,
                        'created_by' => $project->owner_id,
                    ]);

                    Comment::factory()->count(2)->create([
                        'user_id' => $project->members->random()->id,
                        'commentable_type' => 'App\Models\Task',
                        'commentable_id' => $task->id,
                    ]);
                }

                Comment::factory()->count(2)->create([
                    'user_id' => $project->members->random()->id,
                    'commentable_type' => 'App\Models\Project',
                    'commentable_id' => $project->id,
                ]);

                $projects->push($project);
            }
        }
    }
}