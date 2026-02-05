# Formula1Predictions — Agent Instructions

AI agents maintain and extend this Laravel 12 F1 predictions platform: bug fixes, features, and scoring/analytics experiments. High autonomy within guardrails below.

**References**: [AGENTS_PRD.md](AGENTS_PRD.md) (full spec) | [.cursor/rules/](.cursor/rules/) (conventions) | [LESSONS_LEARNED.md](LESSONS_LEARNED.md) (cross-session learnings)

---

## Commands

```bash
# Run tests (targeted)
php artisan test
php artisan test tests/Feature/ScoringServiceTest.php
php artisan test --filter=testName

# Format (run before finalizing)
vendor/bin/pint --dirty

# Build frontend (if UI changes)
npm run build
# or for dev: npm run dev / composer run dev
```

Always lint, format, and run relevant tests. Use full builds sparingly.

---

## Tech Stack

- PHP 8.4.5, Laravel 12
- Livewire 3, Volt, Flux UI (free)
- Tailwind CSS v4
- Pest v4 (unit, feature, browser)
- Laravel Pint

---

## Project Structure

| Path | Purpose |
|------|---------|
| `app/Models/` | Eloquent models (Prediction, Races, Drivers, Teams, Standings) |
| `app/Services/` | ScoringService, F1ApiService, ChartDataService, NotificationService |
| `app/Livewire/` | PredictionForm, DraggableDriverList, RacesList, Charts/* |
| `resources/views/` | Blade + Volt views |
| `tests/Feature/`, `tests/Browser/` | Pest tests |

Use `F1ApiService` for external API calls; never raw HTTP. Use `ScoringService` as system of record for scoring logic.

---

## Boundaries

**Always do:**
- Follow `.cursor/rules/*.mdc` and sibling-file conventions
- Use Eloquent relationships; prefer Form Requests for validation
- Run `vendor/bin/pint --dirty` after code changes
- Add/update Pest tests for behavioral changes
- Use `config()` only; never `env()` outside config

**Ask first:**
- Scoring formula or rule changes in `ScoringService`
- Migrations that alter/remove columns or risk data loss
- Auth/authorization changes (policies, guards)
- New Composer/NPM dependencies or external services

**Never do:**
- Read/write `.env` or secrets
- Drop tables or irreversibly modify data outside migrations
- Disable/delete tests to get green builds
- Implement gambling, payments, or betting features

---

## Lessons Learned

When you discover a recurring pitfall or better pattern, add a brief entry to [LESSONS_LEARNED.md](LESSONS_LEARNED.md). Keep entries concise (1–3 lines). This improves consistency across sessions.

---

## Session Handoff

When ending a session with incomplete work, generate a handoff block for the next agent.

**Work types:** code implementation | testing/debugging | docs | refactor | planning

**Template:**

```markdown
**Task:** [Context-aware title] — [Measurable goal]

**Current Status:**
- [Quantifiable progress: test pass rate, endpoints done, coverage, etc.]

**Key Areas to Focus On:**
1. [Highest priority]
2. [Next priority]

**Recent Accomplishments (don't redo):**
- [List what was completed]

**Environment:**
- Tests: `php artisan test [path]`
- Key files: [paths]
```

Put commands and key paths in handoffs so the next agent can resume immediately.

---

## Recent Completion (F1-002)

**Task:** Improve F1 API error reporting in races list — done

**What was done:**
- Added `App\Exceptions\F1ApiException` with `getLogContext()` for structured logging
- Updated `F1ApiService` to throw `F1ApiException` on API/connection failures; `fetchAllRacesForYear` now throws when 0 races due to API failure
- Updated `RacesList` to show user-friendly message ("We're having trouble loading race data right now...") and log with year/endpoint/status
- Extended `F1ApiTest` and `RacesPageTest` for 500, connection failure, and no-technical-details-exposed scenarios

**Tests:** `php artisan test tests/Feature/F1ApiTest.php tests/Feature/RacesPageTest.php` (note: F1Api 500 test ~48s due to 24-round loop)

---

## Recent Completion (F1-000)

**Task:** Define 2026 season MVP scope, legacy data strategy, and release plan — done

**What was done:**
- Documented a 2026 MVP feature set, legacy-import boundaries, and milestone structure in `AGENTS_PRD.md` (see “1.2 2026 Season MVP (F1-000)”).
- Updated `TODO.md` so `F1-000` is marked done, related tasks reference the MVP plan, and legacy/import work (`F1-006A`) is clearly scoped as a Phase 1 import.

**Tests:** No application behavior changed (docs and backlog only), so no PHP tests were run for this task.

---

## Recent Completion (F1-001)

**Task:** Harden race prediction scoring around DNS/DSQ edge cases — done

**What was done:**
- Updated `Prediction` so `score()`, `calculateScore()`, and `calculateAccuracy()` delegate into `ScoringService`, keeping `ScoringService` as the scoring system of record and deprecating model-level implementations.
- Fixed `ScoringService::findDriverPosition()` to gracefully handle results without an explicit `position` field while preserving existing processed-results behavior.
- Added tests in `ScoringServiceTest` for EXCLUDED drivers and for `Prediction::score()` integration with `ScoringService` to verify consistent scores/accuracy and status updates.

**Tests:** `php artisan test tests/Feature/ScoringServiceTest.php`

---

## Recent Completion (F1-003)

**Task:** Strengthen Livewire prediction form validation and editing flows — done

**What was done:**
- Updated `PredictionForm` and HTTP Form Requests so race predictions require a `race_round`, preseason/midseason predictions prohibit it, and core prediction arrays are validated consistently with existing rules.
- Enforced `Prediction::isEditable` and user ownership in the Livewire prediction form, blocking edits to locked/scored or non-owned predictions and surfacing a clear message/disabled UI state.
- Extended `LivewirePredictionFormTest` and `PredictionFormValidationTest` to cover invalid `type`/`season`/`raceRound` combinations and blocked edit scenarios.

**Tests:** `php artisan test tests/Feature/LivewirePredictionFormTest.php tests/Feature/PredictionFormValidationTest.php`
