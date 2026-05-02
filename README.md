# Issue Intake & Smart Summary System

A production-style Laravel + React (Inertia.js) application for a support/operations team to submit, track, and automatically summarise issues. Each issue receives an AI-generated (or rules-based) short summary and suggested next action on creation or update.

---

## Stack

| Layer      | Technology                                    |
|------------|-----------------------------------------------|
| Backend    | PHP 8.2 / Laravel 13                          |
| Frontend   | React 18 + Inertia.js (no full-page reloads)  |
| Styling    | Tailwind CSS v3                               |
| Database   | SQLite (default) — swappable to MySQL/Postgres |
| AI         | Google Gemini `gemini-2.0-flash` with rules-based fallback |
| Build tool | Vite 8                                        |

---

## Quick Start

### 1. Clone and install

```bash
git clone <repo-url>
cd issue-summary-system

composer install
npm install --legacy-peer-deps
```

> `--legacy-peer-deps` is required because `@vitejs/plugin-react` v4's peer-dep range
> was declared before Vite 8 was released; they are functionally compatible.

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

**To enable AI summaries**, open `.env` and add:

```
GEMINI_API_KEY=your-key-here
```

Get a free key at [aistudio.google.com](https://aistudio.google.com/apikey).

If this key is absent or the API call fails, the system automatically falls back to the
built-in rules-based summary engine — no configuration required.

### 3. Database setup

The project uses SQLite by default (no server required).

```bash
# Create the database file (already exists if you cloned the repo)
touch database/database.sqlite

php artisan migrate
php artisan db:seed          # loads 10 realistic sample issues
```

To use MySQL/Postgres instead, update `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, etc. in `.env`.

### 4. Run the application

Open **two terminals**:

```bash
# Terminal 1 — Laravel dev server
php artisan serve

# Terminal 2 — Vite asset watcher
npm run dev
```

Visit **http://localhost:8000**

---

## Features

### Issue Management
- **Create** issues with title, description, priority, category, and status
- **List** all issues sorted by urgency (critical → high → medium → low), with pagination
- **Filter** by status, category, and priority (combinable)
- **View** full issue detail including generated summary
- **Update** any field — summary regenerates automatically when content changes
- **Delete** issues

### Smart Summary & Next Action
Every issue gets a `summary` (one sentence) and `next_action` (specific step for the team).

| Mode       | When active                        | Description                                                 |
|------------|------------------------------------|-------------------------------------------------------------|
| AI         | `GEMINI_API_KEY` set in `.env`     | Calls `gemini-2.0-flash`; structured JSON prompt; strips fences  |
| Rules-based | Key missing or API call fails     | Matrix of category × priority → deterministic text          |

The fallback is **always available** — the app never throws errors due to missing AI config.

### Escalation Flag
Issues with `priority = high | critical` and `status = open | in_progress` are automatically
flagged as `is_escalated = true`. This flag is recomputed on every create and update.
Escalated issues show a red indicator in the list and a warning banner on the detail page.

### Validation
All inputs are validated server-side via Laravel Form Requests with descriptive error messages.
Invalid or incomplete requests return HTTP 422 with a JSON/Inertia error payload.

---

## API Reference

All endpoints live under `/api` and return JSON. No authentication is required for this demo.

| Method | Endpoint            | Description                                |
|--------|---------------------|--------------------------------------------|
| GET    | `/api/issues`       | List issues (filter: `status`, `category`, `priority`) |
| POST   | `/api/issues`       | Create an issue                            |
| GET    | `/api/issues/{id}`  | Get a single issue                         |
| PATCH  | `/api/issues/{id}`  | Update an issue                            |
| DELETE | `/api/issues/{id}`  | Delete an issue                            |

### Example: Create an issue

```bash
curl -X POST http://localhost:8000/api/issues \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Payment gateway returning 503",
    "description": "Stripe webhooks are failing with 503 errors since 14:00 UTC. Checkout is broken for all users. No deployments were made today.",
    "priority": "critical",
    "category": "infrastructure",
    "status": "open"
  }'
```

### Example: Filter issues

```bash
curl "http://localhost:8000/api/issues?priority=critical&status=open" \
  -H "Accept: application/json"
```

### Example: Update status

```bash
curl -X PATCH http://localhost:8000/api/issues/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status": "in_progress"}'
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── IssueController.php          # Web (Inertia) controller
│   │   └── Api/IssueController.php      # Pure JSON API controller
│   └── Requests/
│       ├── StoreIssueRequest.php        # Create validation
│       └── UpdateIssueRequest.php       # Update validation
├── Models/
│   └── Issue.php                        # Model, scopes, escalation logic
└── Services/
    └── SummaryService.php               # Gemini + rules-based fallback

database/
├── migrations/
│   └── ..._create_issues_table.php
└── seeders/
    └── IssueSeeder.php                  # 10 realistic sample issues

resources/js/
├── Components/Badge.jsx                 # Colour-coded priority/status/category chips
└── Pages/Issues/
    ├── Index.jsx                        # List + filter view
    ├── Create.jsx                       # New issue form
    └── Show.jsx                         # Detail + inline edit

routes/
├── web.php                              # Inertia routes
└── api.php                              # JSON API routes
```

---

## Architecture & Key Decisions

### Why SQLite?
SQLite requires zero server setup, ships with PHP, and is sufficient for a take-home demo.
The Laravel database layer is identical for MySQL/Postgres — switching is a one-line `.env` change.

### Why Inertia.js?
Inertia bridges Laravel's server-side routing and React components without building a separate
SPA API. This keeps the backend authoritative (routing, auth, validation) while giving a modern
reactive UI — a practical trade-off for a full-stack Laravel team.

### SummaryService design
`SummaryService::generate()` is the single entry point for both AI and rules-based paths.
The controller and seeder don't need to know which engine ran. The service:
1. Checks for `GEMINI_API_KEY` via `config('services.gemini.key')`.
2. Calls the Gemini `generateContent` REST API with a structured prompt that requests JSON output.
3. Strips markdown code fences from the response (models sometimes include them).
4. Falls back to rules-based on any failure (network error, unexpected format, rate-limit).

### Escalation logic
Escalation is a pure domain rule on the model — `Issue::refreshEscalation()` — called from
both controllers so the flag stays consistent regardless of how an issue is created or updated.
This makes it easy to extend (e.g., add time-based escalation) without touching controllers.

### Dual interface (web + API)
The app exposes both an Inertia web UI (`/issues`) and a REST JSON API (`/api/issues`).
The web controller uses `Inertia::render()`; the API controller returns `response()->json()`.
Both share the same Form Requests and SummaryService, so validation and business logic
are never duplicated.

---

## What I Would Improve With More Time

1. **Authentication & roles** — Gate issue creation/deletion to logged-in users; add an admin role for escalation management.
2. **Queue the AI call** — Move `SummaryService::generate()` into a Laravel Job so the HTTP response returns immediately and the summary appears asynchronously (via polling or WebSocket).
3. **Time-based escalation** — A scheduled command (`php artisan schedule:run`) could re-evaluate escalation for issues open longer than a configurable SLA window.
4. **Audit log** — Store a history of status changes and who made them (using a `issue_events` table or Laravel's `spatie/laravel-activitylog`).
5. **Test coverage** — Add PHPUnit feature tests for the API endpoints and a unit test for `SummaryService`'s rules-based fallback.
6. **AI model abstraction** — Allow the model name (`gemini-2.0-flash`) and provider to be configured via `.env` so teams can switch to GPT-4o, Claude, or a local Ollama endpoint without a code change.
7. **Retry & caching** — Retry failed Gemini calls with exponential back-off; cache summaries to avoid redundant API calls on re-reads.
# issue-summary-system
