<?php

namespace Tests\Feature;

use App\Models\Issue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_a_comment_to_an_existing_issue(): void
    {
        $issue = Issue::factory()->create();

        $response = $this->postJson("/api/issues/{$issue->id}/comments", [
            'author_name' => 'Jordan Lee',
            'body'        => 'I can reproduce this on the latest build.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'issue_id'    => $issue->id,
                'author_name' => 'Jordan Lee',
                'body'        => 'I can reproduce this on the latest build.',
            ]);

        $this->assertDatabaseHas('comments', [
            'issue_id'    => $issue->id,
            'author_name' => 'Jordan Lee',
        ]);
        $this->assertSame(1, $issue->comments()->count());
    }

    public function test_comment_requires_non_empty_body_and_author(): void
    {
        $issue = Issue::factory()->create();

        // Whitespace is trimmed before validation, so a blank body is rejected.
        $this->postJson("/api/issues/{$issue->id}/comments", [
            'author_name' => 'Jordan Lee',
            'body'        => '   ',
        ])->assertStatus(422)->assertJsonValidationErrors(['body']);

        $this->postJson("/api/issues/{$issue->id}/comments", [
            'body' => 'Missing the author name.',
        ])->assertStatus(422)->assertJsonValidationErrors(['author_name']);
    }
}
