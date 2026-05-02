<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Services\SummaryService;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function __construct(private readonly SummaryService $summaryService) {}

    public function run(): void
    {
        $samples = [
            [
                'title'       => 'Login page returns 500 error for SSO users',
                'description' => 'Users authenticated via the company SSO provider are receiving a HTTP 500 Internal Server Error immediately after being redirected back from the identity provider. This affects 100% of SSO users and blocks all access to the platform. Standard username/password login still works. First reported at 08:42 UTC by the operations team.',
                'priority'    => 'critical',
                'category'    => 'bug',
                'status'      => 'open',
            ],
            [
                'title'       => 'Database replication lag exceeding 30 seconds',
                'description' => 'The read replica in the EU region is consistently lagging behind the primary by 30-45 seconds during peak hours (09:00–11:00 UTC). This causes stale data to be served from the reporting dashboard. No data loss has been detected, but real-time analytics are unreliable. Began occurring after last week\'s RDS instance type upgrade.',
                'priority'    => 'high',
                'category'    => 'infrastructure',
                'status'      => 'in_progress',
            ],
            [
                'title'       => 'Unauthenticated users can access admin API endpoints',
                'description' => 'A penetration test conducted on 2026-04-28 revealed that several /api/admin/* routes do not enforce authentication middleware. A crafted request with a spoofed Accept header bypasses the guard. Affected endpoints include user deletion and role assignment. No evidence of external exploitation yet, but the risk is critical.',
                'priority'    => 'critical',
                'category'    => 'security',
                'status'      => 'open',
            ],
            [
                'title'       => 'Add bulk export to CSV from the issue list',
                'description' => 'Operations team has requested the ability to select multiple issues from the list view and export them to a CSV file for offline reporting and handoff to the compliance team. Fields needed: id, title, priority, category, status, summary, created_at. Should support filtered exports.',
                'priority'    => 'medium',
                'category'    => 'feature',
                'status'      => 'open',
            ],
            [
                'title'       => 'Email notifications not delivered to @contractor.example.com addresses',
                'description' => 'Since the mail provider migration on 2026-04-25, emails to contractor addresses on the @contractor.example.com domain are silently dropped. Internal @company.com addresses receive emails correctly. The issue appears to be a missing SPF record for the subdomain. Contractors miss ticket updates and SLA notifications.',
                'priority'    => 'high',
                'category'    => 'bug',
                'status'      => 'open',
            ],
            [
                'title'       => 'Scheduled report job times out after 5 minutes',
                'description' => 'The nightly report generation job that runs at 02:00 UTC has been timing out since the dataset grew beyond 500k records. The job produces a PDF summary for executive review. It fails silently — no error email is sent, and the report is missing from the next morning\'s dashboard. A query optimisation or async job refactor is likely needed.',
                'priority'    => 'medium',
                'category'    => 'infrastructure',
                'status'      => 'open',
            ],
            [
                'title'       => 'Dark mode support for the web dashboard',
                'description' => 'Multiple users have requested a dark mode toggle for the main dashboard. This is a quality-of-life improvement, not blocking any workflow. Could be implemented via Tailwind\'s dark: variant and a toggle stored in localStorage or user preferences.',
                'priority'    => 'low',
                'category'    => 'feature',
                'status'      => 'open',
            ],
            [
                'title'       => 'Incorrect timezone displayed on issue timestamps',
                'description' => 'All issue timestamps are displayed in UTC regardless of the user\'s configured timezone preference. Users in AEST (UTC+10) report that timestamps appear 10 hours behind. The timezone is stored correctly in the user profile but is not applied on the frontend.',
                'priority'    => 'medium',
                'category'    => 'bug',
                'status'      => 'resolved',
            ],
            [
                'title'       => 'Upgrade Node.js runtime from v18 to v22 LTS',
                'description' => 'Node.js v18 reaches end-of-life in April 2025. The build pipeline and staging environment should be upgraded to v22 LTS before production. All dependencies need to be audited for compatibility. This is a proactive maintenance task with no immediate user impact.',
                'priority'    => 'low',
                'category'    => 'infrastructure',
                'status'      => 'closed',
            ],
            [
                'title'       => 'Search results do not return partial matches',
                'description' => 'The global search bar only returns results for exact keyword matches. Users searching for "auth" do not see results containing "authentication" or "authorisation". A full-text search index or LIKE-based query with wildcards should be implemented to improve discoverability.',
                'priority'    => 'medium',
                'category'    => 'feature',
                'status'      => 'in_progress',
            ],
        ];

        foreach ($samples as $sample) {
            $issue = new Issue($sample);
            $issue->refreshEscalation();

            $generated = $this->summaryService->generate($issue);
            $issue->summary     = $generated['summary'];
            $issue->next_action = $generated['next_action'];
            $issue->save();
        }

        $this->command->info('Seeded ' . count($samples) . ' sample issues.');
    }
}
