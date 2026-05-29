<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreIssueRequest;
use App\Http\Requests\UpdateIssueRequest;
use App\Jobs\GenerateIssueSummary;
use App\Models\Issue;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssueController extends Controller
{
    // Fields that feed the generated summary; changing any of them re-triggers it.
    private const SUMMARY_FIELDS = ['title', 'description', 'priority', 'category'];

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
        // Summary fields are populated later by the job; set them explicitly so the
        // create response returns the full, predictable resource shape.
        $issue->summary        = null;
        $issue->next_action    = null;
        $issue->summary_status = 'pending';
        $issue->refreshEscalation();
        $issue->save();

        // Summary/next action are produced out of band so the request returns fast.
        GenerateIssueSummary::dispatch($issue);

        if ($request->wantsJson()) {
            return response()->json($issue, 202);
        }

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue created. The summary is being generated.');
    }

    public function show(Issue $issue): Response
    {
        return Inertia::render('Issues/Show', [
            // Eager-load comments so the detail view never triggers N+1 queries.
            'issue'      => $issue->load('comments'),
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
        ]);
    }

    public function edit(Issue $issue): Response
    {
        return Inertia::render('Issues/Show', [
            'issue'      => $issue->load('comments'),
            'priorities' => Issue::priorities(),
            'categories' => Issue::categories(),
            'statuses'   => Issue::statuses(),
            'editing'    => true,
        ]);
    }

    public function update(UpdateIssueRequest $request, Issue $issue)
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
