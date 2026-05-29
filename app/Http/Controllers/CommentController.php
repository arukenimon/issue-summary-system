<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Issue;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Issue $issue): RedirectResponse
    {
        $issue->comments()->create($request->validated());

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Comment added.');
    }
}
