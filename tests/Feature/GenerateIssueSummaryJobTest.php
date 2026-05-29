<?php

namespace Tests\Feature;

use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use App\Services\SummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateIssueSummaryJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_running_the_job_populates_summary_next_action_and_status(): void
    {
        // No GEMINI_API_KEY in the testing env, so this exercises the rules-based
        // fallback path deterministically.
        $issue = Issue::factory()->create([
            'priority'       => 'critical',
            'category'       => 'security',
            'summary'        => null,
            'next_action'    => null,
            'summary_status' => 'pending',
        ]);

        (new GenerateIssueSummary($issue))->handle(app(SummaryService::class));

        $issue->refresh();

        $this->assertNotNull($issue->summary);
        $this->assertNotNull($issue->next_action);
        $this->assertSame('ready', $issue->summary_status);

        $this->assertDatabaseHas('issues', [
            'id'             => $issue->id,
            'summary_status' => 'ready',
        ]);
    }

    public function test_failed_job_marks_summary_status_as_failed(): void
    {
        $issue = Issue::factory()->create(['summary_status' => 'pending']);

        (new GenerateIssueSummary($issue))->failed(new \RuntimeException('boom'));

        $this->assertSame('failed', $issue->fresh()->summary_status);
    }
}
