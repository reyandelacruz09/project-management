<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->catchPhrase(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::values()),
            'start_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'owner_id' => User::factory(),
        ];
    }
}