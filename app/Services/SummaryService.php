<?php

namespace App\Services;

use App\Models\Issue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SummaryService
{
    /**
     * Generate a summary and suggested next action for an issue.
     * Tries Gemini first; falls back to rules-based logic if unavailable.
     *
     * @return array{summary: string, next_action: string}
     */
    public function generate(Issue $issue): array
    {
        $apiKey = config('services.gemini.key'); // The API key is stored in the .env file

        if ($apiKey) {
            $result = $this->fromGemini($issue, $apiKey); // If the Gemini API call is successful, the system will return the result
            if ($result) {
                return $result;
            }
        }

        return $this->fromRules($issue); // If the Gemini API call fails, the system automatically falls back to the rules-based summary engine
    }

    /**
     * @return array{summary: string, next_action: string}|null
     */
    private function fromGemini(Issue $issue, string $apiKey): ?array
    {
        // The prompt is the instructions for the Gemini API call
        $prompt = <<<PROMPT
                        You are a support operations assistant. Given the following issue details, produce:
                        1. A concise one-sentence summary (max 20 words).
                        2. A specific, actionable next step for the support team (max 20 words).

                        Issue title: {$issue->title}
                        Category: {$issue->category}
                        Priority: {$issue->priority}
                        Description: {$issue->description}

                        Respond in JSON with exactly two keys: "summary" and "next_action".
                    PROMPT;

        try {
            $response = Http::timeout(15) // The timeout is the maximum amount of time that the request can take
                ->withOptions(['verify' => false]) // The verify is a boolean that controls if the SSL certificate should be verified
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}", [
                    'contents'         => [['parts' => [['text' => $prompt]]]], // The contents is the prompt for the Gemini API call
                    'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => 120], // Temperature is a value between 0 and 1 that controls the randomness of the output
                    // MaxOutputTokens is the maximum number of tokens that can be output in the response
                ]);

            if ($response->failed()) {
                Log::warning('Gemini request failed', ['status' => $response->status()]);
                return null;
            }

            // The content is the response from the Gemini API call
            $content = $response->json('candidates.0.content.parts.0.text', '');
            // Strip markdown code fences if present
            // The preg_replace() function is used to replace the markdown code fences with an empty string
            $content = preg_replace('/^```(?:json)?\s*/i', '', trim($content));
            $content = preg_replace('/\s*```$/', '', $content);
            // The preg_replace() function is used to replace the markdown code fences with an empty string

            // The parsed is the response from the Gemini API call
            $parsed = json_decode($content, true);

            // The parsed is the response from the Gemini API call
            if (isset($parsed['summary'], $parsed['next_action'])) {
                return [
                    'summary'     => trim($parsed['summary']), // The summary is the summary of the issue
                    'next_action' => trim($parsed['next_action']), // The next action is the next action for the issue
                ];
            }

            Log::warning('Gemini returned unexpected format', ['content' => $content]); // The Log::warning() function is used to log a warning message
            return null;

        } catch (\Throwable $e) {
            Log::warning('Gemini call threw exception', ['message' => $e->getMessage()]); // The Log::warning() function is used to log a warning message
            return null; // The return null; statement is used to return null if the Gemini API call fails
        }
    }

    /**
     * Rules-based fallback: derives summary and next action from category, priority, and description keywords.
     *
     * @return array{summary: string, next_action: string}
     */
    private function fromRules(Issue $issue): array
    {
        $summary    = $this->buildSummary($issue); // The summary is the summary of the issue
        $nextAction = $this->buildNextAction($issue); // The next action is the next action for the issue

        return ['summary' => $summary, 'next_action' => $nextAction]; // The return statement is used to return the summary and next action
    }

    // buildSummary is used to build the summary for an issue
    private function buildSummary(Issue $issue): string // The buildSummary function is used to build the summary for an issue and returns a string     
    {
        $priority = ucfirst($issue->priority); // The priority is the priority of the issue
        $category = ucfirst($issue->category); // The category is the category of the issue

        $templates = [
            'security'       => "{$priority}-priority security concern reported: {$issue->title}.",
            'bug'            => "{$priority}-priority bug detected affecting system behaviour: {$issue->title}.",
            'infrastructure' => "{$priority}-priority infrastructure issue identified: {$issue->title}.",
            'feature'        => "{$priority}-priority feature request submitted: {$issue->title}.",
            'other'          => "{$priority}-priority {$category} issue logged: {$issue->title}.",
        ];

        return $templates[$issue->category] ?? "{$priority} issue: {$issue->title}."; // The return statement is used to return the summary
    }

    // buildNextAction is used to build the next action for an issue
    private function buildNextAction(Issue $issue): string // The buildNextAction function is used to build the next action for an issue and returns a string
    {
        if ($issue->priority === 'critical') { // The if statement is used to check if the priority is critical
            return match ($issue->category) { // The match statement is used to return the next action based on the category
                'security'       => 'Escalate to security team immediately and initiate incident response protocol.',
                'infrastructure' => 'Page on-call engineer and begin incident bridge call immediately.',
                'bug'            => 'Assign to senior engineer for immediate investigation and hotfix.',
                default          => 'Escalate to team lead and triage within the hour.', // The default statement is used to return the next action if the category is not found
            };
        }

        if ($issue->priority === 'high') {
            return match ($issue->category) { // The match statement is used to return the next action based on the category
                'security'       => 'Notify security team and schedule review within 4 hours.',
                'infrastructure' => 'Assign to infrastructure team for same-day resolution.',
                'bug'            => 'Assign to engineering team and target fix in next sprint.',
                'feature'        => 'Add to product backlog and schedule stakeholder review.',
                default          => 'Assign to relevant team lead for prioritisation today.', // The default statement is used to return the next action if the category is not found
            };
        }

        if ($issue->priority === 'medium') {
            return match ($issue->category) { // The match statement is used to return the next action based on the category
                'security' => 'Log in security backlog and schedule review within the week.',
                'bug'      => 'Reproduce the issue, document steps, and schedule for next sprint.',
                'feature'  => 'Gather requirements and add to upcoming sprint planning.',
                default    => 'Review during next team standup and assign an owner.', // The default statement is used to return the next action if the category is not found
            };
        }

        // low priority
        return match ($issue->category) { // The match statement is used to return the next action based on the category
            'feature' => 'Add to long-term product backlog for future consideration.',
            'bug'     => 'Document and monitor; address if recurrence is reported.',
            default   => 'Acknowledge receipt and revisit during quarterly planning.', // The default statement is used to return the next action if the category is not found  
        };
    }
}
