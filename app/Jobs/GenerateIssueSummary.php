<?php

namespace App\Jobs;

use App\Models\Issue;
use App\Services\SummaryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateIssueSummary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Retry policy: the rules-based path is deterministic, so retries only matter
    // when the LLM call is transiently failing. Back off between attempts, then
    // give up and land in failed_jobs (the dead-letter table).
    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public Issue $issue) {}

    public function handle(SummaryService $summaryService): void
    {
        // SerializesModels re-fetches the issue, so we always summarise the
        // current title/description/priority/category — important when an update
        // re-dispatches this job.
        $generated = $summaryService->generate($this->issue);

        $this->issue->forceFill([
            'summary'        => $generated['summary'],
            'next_action'    => $generated['next_action'],
            'summary_status' => 'ready',
        ])->save();
    }

    /**
     * Called once the job has exhausted all retries. We mark the issue as failed
     * rather than leaving it stuck on "pending" so the UI/API can surface it.
     */
    public function failed(?Throwable $exception): void
    {
        $this->issue->forceFill(['summary_status' => 'failed'])->save();
    }
}
