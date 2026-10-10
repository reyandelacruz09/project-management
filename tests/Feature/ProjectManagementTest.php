<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_index_renders_for_a_user(): void
    {
        $user = User::factory()->create();
        Project::factory()->count(3)->create(['owner_id' => $user->id]);

        $this->actingAs($user)->get('projects')->assertOk();
    }

    public function test_project_show_page_renders(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);
        $project->members()->attach($user->id, ['role' => 'manager']);

        $this->actingAs($user)->get("projects/{$project->id}")->assertOk();
    }

    public function test_project_is_created_via_the_web_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('projects', [
                'name' => 'Website Launch',
                'description' => 'Build and launch the marketing site.',
                'status' => 'planning',
                'start_date' => '2026-10-01',
                'end_date' => '2026-12-31',
                'team_id' => null,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', ['name' => 'Website Launch']);

        $project = Project::where('name', 'Website Launch')->first();

        $this->assertSame($user->id, $project->owner_id);
        $this->assertTrue($project->members()->where('users.id', $user->id)->exists());
    }

    public function test_a_member_can_create_and_update_a_task(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($user)
            ->post("projects/{$project->id}/tasks", [
                'title' => 'Write README',
                'description' => null,
                'status' => 'todo',
                'priority' => 'medium',
                'user_id' => null,
                'due_date' => null,
            ])
            ->assertRedirect();

        $task = Task::first();

        $this->assertSame($project->id, $task->project_id);
        $this->assertSame($user->id, $task->created_by);

        $this->actingAs($user)
            ->patch("projects/{$project->id}/tasks/{$task->id}", ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertSame('in_progress', $task->fresh()->status->value);
    }

    public function test_teams_index_renders_and_team_is_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('teams')->assertOk();

        $this->actingAs($user)
            ->post('teams', [
                'name' => 'Squad X',
                'description' => 'Platform sprint team.',
                'members' => [],
            ])
            ->assertRedirect();

        $team = Team::where('name', 'Squad X')->first();

        $this->assertNotNull($team);
        $this->assertSame($user->id, $team->owner_id);
    }

    public function test_project_edits_are_forbidden_for_non_managers(): void
    {
        $user = User::factory()->create(['role' => UserRole::Member]);
        $owner = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($user->id, ['role' => 'member']);

        $this->actingAs($user)
            ->put("projects/{$project->id}", [
                'name' => 'Renamed',
                'description' => null,
                'status' => 'active',
                'start_date' => null,
                'end_date' => null,
                'team_id' => null,
            ])
            ->assertForbidden();
    }

    public function test_project_members_are_synced_from_the_edit_form(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)
            ->put("projects/{$project->id}", [
                'name' => $project->name,
                'status' => $project->status->value,
                'has_members' => 1,
                'members' => [
                    ['id' => $member->id, 'role' => 'manager'],
                ],
            ])
            ->assertRedirect("projects/{$project->id}");

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'manager',
        ]);
    }

    public function test_unchecking_every_member_clears_the_project_member_list(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $project->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($owner)
            ->put("projects/{$project->id}", [
                'name' => $project->name,
                'status' => $project->status->value,
                'has_members' => 1,
            ])
            ->assertRedirect("projects/{$project->id}");

        $this->assertCount(0, $project->fresh()->members);
    }

    public function test_project_edit_form_offers_a_role_for_every_member(): void
    {
        $owner = User::factory()->create();
        User::factory()->count(2)->create();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->get("projects/{$project->id}/edit");

        $response->assertOk();

        preg_match_all('/name="members\[\d+\]\[role\]"/', $response->getContent(), $matches);

        $this->assertCount(User::count(), $matches[0]);
    }

    public function test_a_comment_is_added_and_removed_from_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['owner_id' => $user->id]);

        $this->actingAs($user)
            ->post('comments', [
                'commentable_type' => Project::class,
                'commentable_id' => $project->id,
                'body' => 'Looks good to me.',
            ])
            ->assertRedirect();

        $comment = Comment::first();

        $this->assertNotNull($comment);
        $this->assertSame($user->id, $comment->user_id);
        $this->assertTrue($comment->commentable->is($project));

        $this->actingAs($user)->delete("comments/{$comment->id}")->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_team_members_are_added_and_removed(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $this->actingAs($owner)
            ->put("teams/{$team->id}", ['members' => [$member->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('team_user', ['team_id' => $team->id, 'user_id' => $member->id]);

        $this->actingAs($owner)
            ->put("teams/{$team->id}", ['remove' => $member->id])
            ->assertRedirect();

        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $member->id]);
    }

    public function test_web_links_are_not_shadowed_by_the_json_api_routes(): void
    {
        $this->assertSame(url('projects'), route('projects.index'));
        $this->assertSame(url('projects/1'), route('projects.show', 1));
        $this->assertSame(url('teams/1'), route('teams.show', 1));
        $this->assertSame(url('api/projects/1'), route('api.projects.show', 1));
    }

    public function test_projects_are_exposed_over_the_json_api_with_a_token(): void
    {
        $user = User::factory()->create();
        Project::factory()->create(['owner_id' => $user->id]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
