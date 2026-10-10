<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        $commentable = fake()->randomElement([
            'App\Models\Project',
            'App\Models\Task',
        ]);

        return [
            'user_id' => User::factory(),
            'commentable_type' => $commentable,
            'commentable_id' => $commentable === 'App\Models\Project' ? Project::factory() : Task::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}