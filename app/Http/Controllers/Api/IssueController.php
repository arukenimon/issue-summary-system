<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\SummaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IssueController extends Controller
{
    // SummaryService is used to generate a summary and suggested next action for an issue
    public function __construct(private readonly SummaryService $summaryService) {}

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
        $issue->refreshEscalation();

        $generated = $this->summaryService->generate($issue);
        $issue->summary     = $generated['summary'];
        $issue->next_action = $generated['next_action'];
        $issue->save();

        return response()->json($issue, 201);
    }

    public function show(Issue $issue): JsonResponse
    {
        return response()->json($issue);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): JsonResponse
    {
        $data = $request->validated();

        $issue->fill($data);
        $issue->refreshEscalation();

        if (array_intersect(array_keys($data), ['title', 'description', 'priority', 'category'])) {
            $generated = $this->summaryService->generate($issue);
            $issue->summary     = $generated['summary'];
            $issue->next_action = $generated['next_action'];
        }

        $issue->save();

        return response()->json($issue);
    }

    public function destroy(Issue $issue): JsonResponse
    {
        $issue->delete();

        return response()->json(['message' => 'Issue deleted.']);
    }
}
