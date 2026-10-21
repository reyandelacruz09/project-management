<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_cannot_fetch_users(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_authenticated_user_can_fetch_users_list_and_search(): void
    {
        $user1 = User::factory()->create(['name' => 'Alice Adams', 'email' => 'alice@example.com']);
        $user2 = User::factory()->create(['name' => 'Bob Baker', 'email' => 'bob@example.com']);

        $token = $user1->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(2);

        $response->assertJsonFragment(['name' => 'Alice Adams', 'email' => 'alice@example.com']);

        $searchResponse = $this->withToken($token)
            ->getJson('/api/users?search=Bob')
            ->assertOk()
            ->assertJsonCount(1);

        $searchResponse->assertJsonFragment(['name' => 'Bob Baker']);
    }
}
