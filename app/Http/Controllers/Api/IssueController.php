<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    // Fields that feed the generated summary; changing any of them re-triggers it.
    private const SUMMARY_FIELDS = ['title', 'description', 'priority', 'category'];

    public function index(Request $request): JsonResponse
    {
        $issues = Issue::query()
            ->byStatus($request->query('status'))
            ->byCategory($request->query('category'))
            ->byPriority($request->query('priority'))
            ->byUrgency() // Ordered by urgency: critical first, then high, then by recency in the database
            ->paginate(15)
            ->withQueryString();

        return response()->json($issues);
    }

    public function store(StoreIssueRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'open';

        $issue = new Issue($data);
        // Summary fields are populated later by the job; set them explicitly so the
        // create response returns the full, predictable resource shape.
        $issue->summary        = null;
        $issue->next_action    = null;
        $issue->summary_status = 'pending';
        $issue->refreshEscalation();
        $issue->save();

        // Summary/next action are produced out of band so the request returns fast.
        GenerateIssueSummary::dispatch($issue);

        // 202 Accepted: the issue exists, but its summary is still being generated.
        return response()->json($issue, 202);
    }

    public function show(Issue $issue): JsonResponse
    {
        // Eager-load comments so the single-issue view never triggers N+1 queries.
        return response()->json($issue->load('comments'));
    }

    public function update(UpdateIssueRequest $request, Issue $issue): JsonResponse
    {
        $issue->fill($request->validated());
        $issue->refreshEscalation();

        // Re-trigger generation only when a summary-affecting field actually changed.
        // Updating status alone must not regenerate the summary.
        $shouldRegenerate = $issue->isDirty(self::SUMMARY_FIELDS);
        if ($shouldRegenerate) {
            $issue->summary_status = 'pending';
        }

        $issue->save();

        if ($shouldRegenerate) {
            GenerateIssueSummary::dispatch($issue);
        }

        return response()->json($issue);
    }

    public function destroy(Issue $issue): JsonResponse
    {
        $issue->delete();

        return response()->json(['message' => 'Issue deleted.']);
    }
}
