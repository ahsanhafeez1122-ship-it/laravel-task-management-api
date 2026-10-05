<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_projects(): void
    {
        $this->getJson('/api/projects')->assertStatus(401);
    }

    public function test_user_can_see_their_own_projects(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Project::create(['user_id' => $user->id, 'name' => 'My Project']);

        $this->getJson('/api/projects')
            ->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create(['user_id' => $owner->id, 'name' => 'Owner Only Project']);

        Sanctum::actingAs($intruder);

        $this->getJson("/api/projects/{$project->id}")
            ->assertStatus(404);
    }

    public function test_user_cannot_create_a_task_in_another_users_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create(['user_id' => $owner->id, 'name' => 'Owner Only Project']);

        Sanctum::actingAs($intruder);

        $this->postJson("/api/projects/{$project->id}/tasks", [
            'title' => 'Sneaky task',
        ])->assertStatus(404);
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create(['user_id' => $owner->id, 'name' => 'Owner Only Project']);

        Sanctum::actingAs($intruder);

        $this->deleteJson("/api/projects/{$project->id}")->assertStatus(404);

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
