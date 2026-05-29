<?php

namespace Tests\Feature;

use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IssueApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_an_issue_and_summary_starts_pending(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/issues', [
            'title'       => 'Checkout fails on mobile Safari',
            'description' => 'Users on iOS Safari cannot complete checkout; the pay button is unresponsive.',
            'priority'    => 'high',
            'category'    => 'bug',
        ]);

        // Async boundary: the issue exists immediately, but the summary is not ready yet.
        $response->assertStatus(202)
            ->assertJson([
                'summary'        => null,
                'next_action'    => null,
                'summary_status' => 'pending',
                'is_escalated'   => true, // high priority + open
            ]);

        $this->assertDatabaseHas('issues', [
            'title'          => 'Checkout fails on mobile Safari',
            'status'         => 'open',
            'summary_status' => 'pending',
        ]);
    }

    public function test_create_rejects_missing_and_invalid_fields(): void
    {
        $missing = $this->postJson('/api/issues', []);
        $missing->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'priority', 'category']);

        $invalid = $this->postJson('/api/issues', [
            'title'       => 'A valid title',
            'description' => 'A sufficiently long description for the issue.',
            'priority'    => 'urgent',   // not an allowed value
            'category'    => 'bug',
        ]);
        $invalid->assertStatus(422)->assertJsonValidationErrors(['priority']);
    }

    public function test_list_can_combine_status_and_priority_filters(): void
    {
        Issue::factory()->create(['priority' => 'high', 'status' => 'open',     'category' => 'bug']);
        Issue::factory()->create(['priority' => 'high', 'status' => 'resolved', 'category' => 'bug']);
        Issue::factory()->create(['priority' => 'low',  'status' => 'open',     'category' => 'bug']);

        $response = $this->getJson('/api/issues?priority=high&status=open');

        $response->assertOk()->assertJsonCount(1, 'data');
        $this->assertSame('high', $response->json('data.0.priority'));
        $this->assertSame('open', $response->json('data.0.status'));
    }

    public function test_single_issue_view_loads_comments_without_n_plus_1(): void
    {
        $issue = Issue::factory()->create();
        $issue->comments()->createMany(
            collect(range(1, 12))->map(fn ($i) => [
                'author_name' => "Author {$i}",
                'body'        => "Comment number {$i}",
            ])->all()
        );

        DB::enableQueryLog();
        $response = $this->getJson("/api/issues/{$issue->id}");
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk()->assertJsonCount(12, 'comments');

        // Eager loading keeps this constant (issue lookup + one comments query),
        // regardless of how many comments exist. A regression to lazy/per-row
        // loading would push this far higher.
        $this->assertLessThanOrEqual(3, $queryCount, "Expected <= 3 queries, got {$queryCount} (possible N+1).");
    }

    public function test_creating_an_issue_dispatches_the_summary_job(): void
    {
        Queue::fake();

        $this->postJson('/api/issues', [
            'title'       => 'Nightly export job stalls',
            'description' => 'The 02:00 UTC export job hangs and never writes the output file.',
            'priority'    => 'medium',
            'category'    => 'infrastructure',
        ])->assertStatus(202);

        Queue::assertPushed(GenerateIssueSummary::class, 1);
    }

    public function test_updating_status_only_does_not_redispatch_summary_job(): void
    {
        Queue::fake();
        $issue = Issue::factory()->create(['status' => 'open']);

        $this->patchJson("/api/issues/{$issue->id}", ['status' => 'in_progress'])
            ->assertOk();

        Queue::assertNotPushed(GenerateIssueSummary::class);
    }

    public function test_updating_description_redispatches_summary_job(): void
    {
        Queue::fake();
        $issue = Issue::factory()->create(['summary_status' => 'ready']);

        $this->patchJson("/api/issues/{$issue->id}", ['description' => 'A freshly rewritten description for the issue.'])
            ->assertOk()
            ->assertJson(['summary_status' => 'pending']);

        Queue::assertPushed(GenerateIssueSummary::class, 1);
    }
}
