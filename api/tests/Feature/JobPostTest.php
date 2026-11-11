<?php

namespace Tests\Feature;

use App\Models\JobPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JobPostTest extends TestCase
{
    use RefreshDatabase; // fresh tables for every test

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title'        => 'Laravel Developer',
            'company_name' => 'Acme',
            'description'  => 'Build APIs',
            'location'     => 'Colombo',
            'type'         => 'full-time',
        ], $overrides);
    }

    // ---------- Public browsing ----------

    public function test_guest_can_list_open_jobs(): void
    {
        JobPost::factory()->count(3)->create();

        $this->getJson('/api/job-posts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_closed_jobs_are_not_listed(): void
    {
        JobPost::factory()->create(['status' => 'closed']);

        $this->getJson('/api/job-posts')->assertJsonCount(0, 'data');
    }

    public function test_jobs_can_be_filtered_by_type(): void
    {
        JobPost::factory()->create(['type' => 'internship']);
        JobPost::factory()->create(['type' => 'full-time']);

        $this->getJson('/api/job-posts?type=internship')
            ->assertJsonCount(1, 'data');
    }

    // ---------- Creating ----------

    public function test_employer_can_create_job(): void
    {
        $employer = User::factory()->employer()->create();
        Sanctum::actingAs($employer);

        $this->postJson('/api/job-posts', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.title', 'Laravel Developer');

        $this->assertDatabaseHas('job_posts', [
            'title'   => 'Laravel Developer',
            'user_id' => $employer->id,
        ]);
    }

    public function test_seeker_cannot_create_job(): void
    {
        Sanctum::actingAs(User::factory()->seeker()->create());

        $this->postJson('/api/job-posts', $this->payload())->assertForbidden();
    }

    public function test_guest_cannot_create_job(): void
    {
        $this->postJson('/api/job-posts', $this->payload())->assertUnauthorized();
    }

    public function test_create_requires_valid_data(): void
    {
        Sanctum::actingAs(User::factory()->employer()->create());

        $this->postJson('/api/job-posts', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'company_name', 'description', 'location', 'type']);
    }

    public function test_user_id_in_request_body_is_ignored(): void
    {
        $employer = User::factory()->employer()->create();
        $other    = User::factory()->employer()->create();
        Sanctum::actingAs($employer);

        $this->postJson('/api/job-posts', $this->payload(['user_id' => $other->id]))
            ->assertCreated();

        $this->assertDatabaseHas('job_posts', ['user_id' => $employer->id]);
        $this->assertDatabaseMissing('job_posts', ['user_id' => $other->id]);
    }

    // ---------- Updating / deleting (IDOR checks) ----------

    public function test_owner_can_update_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobPost::factory()->create(['user_id' => $employer->id]);
        Sanctum::actingAs($employer);

        $this->putJson("/api/job-posts/{$job->id}", ['title' => 'Updated Title'])
            ->assertOk();

        $this->assertDatabaseHas('job_posts', ['id' => $job->id, 'title' => 'Updated Title']);
    }

    public function test_other_employer_cannot_update_job(): void
    {
        $job = JobPost::factory()->create();
        Sanctum::actingAs(User::factory()->employer()->create());

        $this->putJson("/api/job-posts/{$job->id}", ['title' => 'Hacked'])
            ->assertForbidden();

        $this->assertDatabaseMissing('job_posts', ['title' => 'Hacked']);
    }

    public function test_seeker_cannot_update_job(): void
    {
        $job = JobPost::factory()->create();
        Sanctum::actingAs(User::factory()->seeker()->create());

        $this->putJson("/api/job-posts/{$job->id}", ['title' => 'Hacked'])
            ->assertForbidden();
    }

    public function test_owner_can_delete_job(): void
    {
        $employer = User::factory()->employer()->create();
        $job = JobPost::factory()->create(['user_id' => $employer->id]);
        Sanctum::actingAs($employer);

        $this->deleteJson("/api/job-posts/{$job->id}")->assertOk();

        $this->assertDatabaseMissing('job_posts', ['id' => $job->id]);
    }

    public function test_other_employer_cannot_delete_job(): void
    {
        $job = JobPost::factory()->create();
        Sanctum::actingAs(User::factory()->employer()->create());

        $this->deleteJson("/api/job-posts/{$job->id}")->assertForbidden();

        $this->assertDatabaseHas('job_posts', ['id' => $job->id]);
    }
}