<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Models\Issue;
use App\Services\SummaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    // SummaryService is used to generate a summary and suggested next action for an issue
    public function __construct(private readonly SummaryService $summaryService) {}

    public function index(Request $request): Response
    {
        $issues = Issue::query()
            ->byStatus($request->query('status'))
            ->byCategory($request->query('category'))
            ->byPriority($request->query('priority'))
            ->byUrgency() // Ordered by urgency: critical first, then high, then by recency
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Issues/Index', [
            'issues'     => $issues,
            'filters'    => $request->only(['status', 'category', 'priority']),
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Issues/Create', [
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
        ]);
    }

    public function store(StoreIssueRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 'open';

        $issue = new Issue($data);
        $issue->refreshEscalation();

        $generated = $this->summaryService->generate($issue);
        $issue->summary     = $generated['summary'];
        $issue->next_action = $generated['next_action'];
        $issue->save();

        if ($request->wantsJson()) {
            return response()->json($issue, 201);
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue created successfully.');
    }

    public function show(Issue $issue): Response
    {
        return Inertia::render('Issues/Show', [
            'issue'      => $issue,
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
        ]);
    }

    public function edit(Issue $issue): Response
    {
        return Inertia::render('Issues/Show', [
            'issue'      => $issue,
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
            'editing'    => true,
        ]);
    }

    public function update(UpdateIssueRequest $request, Issue $issue)
    {
        $data = $request->validated();

        $issue->fill($data);
        $issue->refreshEscalation();

        // Regenerate summary if content fields changed
        if (array_intersect(array_keys($data), ['title', 'description', 'priority', 'category'])) {
            $generated = $this->summaryService->generate($issue);
            $issue->summary     = $generated['summary'];
            $issue->next_action = $generated['next_action'];
        }

        $issue->save();

        if ($request->wantsJson()) {
            return response()->json($issue);
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue updated successfully.');
    }

    public function destroy(Issue $issue)
    {
        $issue->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Issue deleted.']);
        }

        return redirect()->route('issues.index')
            ->with('success', 'Issue deleted.');
    }
}
