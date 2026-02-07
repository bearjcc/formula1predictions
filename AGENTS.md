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

# Run tests with code coverage (requires pcov: pecl install pcov)
composer run test:coverage

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
- When editing TODO.md: remove completed (done) items and add new items you identify as needing work but did not complete (enables agent-to-agent handoff)
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

## Recent Completion (F1-014)

**Task:** Fix AdminControllerTest failure (regular user cannot delete prediction via admin) — done

**What was done:**
- Added `authorize('manage-predictions')` to `AdminController::deletePrediction` so admin delete route requires admin/moderator; regular users now receive 403 instead of 302.
- Updated test to set prediction `status => 'draft'` (policy would otherwise allow owner delete) to assert admin-route exclusivity.

**Tests:** `php artisan test tests/Feature/AdminControllerTest.php`

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

## Recent Completion (F1-011)

**Task:** Add luck and variance analytics for predictors — done

**What was done:**
- Added `ChartDataService::getPredictorLuckAndVariance($season)` returning per-user metrics: total_score, avg_accuracy, prediction_count, score_std_dev, expected_score, luck_index. Leaderboards unchanged.
- Added "Luck & Variance" option to Prediction Accuracy chart on analytics page (bar chart: Total Score and Luck Index by user).
- Extended `ChartDataServiceTest` and `DataVisualizationTest` with luck/variance structure and view tests.

**Tests:** `php artisan test tests/Feature/ChartDataServiceTest.php tests/Feature/DataVisualizationTest.php`

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

---

## Recent Completion (F1-004)

**Task:** Add basic analytics smoke tests for dashboard and analytics page — done

**What was done:**
- Enhanced `PredictionAccuracyChart` to support multiple analytics views (`user-trends`, `user-comparison`, `race-accuracy`) driven by `ChartDataService` and kept in sync via Livewire state and events.
- Ensured analytics and dashboard flows are covered by smoke tests that verify key chart components render and that chart data methods return expected structures for seeded data.

**Tests:** `php artisan test tests/Feature/DashboardTest.php tests/Feature/DataVisualizationTest.php tests/Feature/ChartDataServiceTest.php`

---

## Recent Completion (F1-005)

**Task:** Improve race list filtering UX for status and text search — done

**What was done:**
- Ensured the `RacesList` Livewire component reads and syncs `statusFilter` and `searchQuery` from the URL/query so race filters can be restored from shared or revisited URLs without changing existing loading/error behavior.
- Kept the Mary UI-based filters (status dropdown, search input, refresh button) clearly labeled and responsive, with Livewire updates avoiding full page reloads while maintaining the existing card layout and actions.
- Added a Livewire component test in `RacesPageTest` that mocks `F1ApiService` and asserts that different combinations of status and search filters produce the expected subsets of races.

**Tests:** `php artisan test tests/Feature/RacesPageTest.php`

---

## Recent Completion (F1-006)

**Task:** Refactor prediction scoring responsibilities out of the Prediction model — done

**What was done:**
- Removed deprecated `calculateScore()` and `calculateAccuracy()` methods from `Prediction` and routed all production scoring and accuracy flows (admin manual scoring, historical backfill) through `ScoringService`.
- Updated `Prediction::score()` to delegate its persistence to `ScoringService::savePredictionScore()` so score, accuracy, status, and timestamps are owned by the service, not duplicated in the model.
- Added a `savePredictionScore` regression test in `ScoringServiceTest` and verified the full scoring feature suite via `php artisan test tests/Feature/ScoringServiceTest.php` after running `vendor/bin/pint --dirty`.

**Tests:** `php artisan test tests/Feature/ScoringServiceTest.php`

---

## Recent Completion (F1-007)

**Task:** Normalize ChartDataService queries and reduce per-row model lookups — done

**What was done:**
- Updated `ChartDataService` to pre-load related drivers and teams for team standings progression, team points progression, and driver/team performance comparison, eliminating repeated `find()` calls inside tight loops to avoid N+1-style query patterns.
- Kept all chart data shapes and semantics intact, with existing tests still green after the refactor.
- Added a lightweight query-count regression test in `ChartDataServiceTest` around team points progression to ensure query volume remains bounded.

**Tests:** `php artisan test tests/Feature/ChartDataServiceTest.php`

---

## Recent Completion (F1-008)

**Task:** Enhance notifications UX and coverage for scored predictions — done

**What was done:**
- Enriched `PredictionScored` and `NotificationService::sendPredictionScoredNotification` so prediction-scored notifications (stored and real-time) include race name, score, and accuracy in their payloads.
- Updated the `NotificationDropdown` Livewire view to highlight prediction-scored notifications with a dedicated label, race name, points, and accuracy plus a “View prediction” call-to-action, while keeping existing unread/read behavior intact.
- Extended `NotificationTest` and `RealTimeNotificationTest` to cover the new data shape and dropdown rendering for prediction-scored notifications, then ran `php artisan test tests/Feature/NotificationTest.php tests/Feature/RealTimeNotificationTest.php` successfully.

**Tests:** `php artisan test tests/Feature/NotificationTest.php tests/Feature/RealTimeNotificationTest.php`

---

## Recent Completion (F1-009)

**Task:** Add backtest harness for alternative scoring experiments — done

**What was done:**
- Created `tests/Support/BacktestScoringHarness` with production, linear, and flatter position-scoring variants. Harness is compute-only (no DB persistence).
- `compareVariants()` returns production_scores, alternative_scores, score_deltas, and rank_changes for experiment analysis.
- Extended `ScoringServiceTest` and `SimpleHistoricalDataTest` with backtest harness tests; production variant matches ScoringService output.

**Tests:** `php artisan test tests/Feature/ScoringServiceTest.php tests/Feature/SimpleHistoricalDataTest.php`

---

## Recent Completion (F1-010)

**Task:** Introduce sprint-only prediction mode — done

**What was done:**
- Added a `sprint` prediction type wired through `Prediction`, `Races` (including `sprintPredictions()` and `allowsSprintPredictions()`), and `ScoringService`, with sprint predictions scored via dedicated sprint position weights and a smaller perfect-bonus so they remain separate from full-race scoring.
- Updated the Livewire `PredictionForm`, Blade prediction form component, and HTTP Form Requests so sprint predictions reuse the race-style driver order/fastest-lap data shape, require a `race_round`, and are only allowed on races flagged with `has_sprint = true`, leaving preseason/midseason flows unchanged.
- Extended `ScoringServiceTest`, `PredictionFormValidationTest`, and `LivewirePredictionFormTest` with sprint-focused cases, then ran `php artisan test tests/Feature/ScoringServiceTest.php tests/Feature/PredictionFormValidationTest.php tests/Feature/LivewirePredictionFormTest.php` successfully after `vendor/bin/pint --dirty`.

**Tests:** `php artisan test tests/Feature/ScoringServiceTest.php tests/Feature/PredictionFormValidationTest.php tests/Feature/LivewirePredictionFormTest.php`

---

## Recent Completion (F1-006A)

**Task:** Design and implement legacy data import pipeline — done

**What was done:**
- Phase 1 legacy import via `Database\Seeders\HistoricalPredictionsSeeder` (markdown from `previous/`), `legacy:import-historical-predictions` command, idempotent seeder and tests.
- All import tests made self-contained: in-test markdown fixtures so `HistoricalDataImportTest` and `SimpleHistoricalDataTest` pass without a gitignored `previous/` directory.
- Phase 2 (CSV/external legacy) deferred until representative data and human-approved migrations are available.

**Tests:** `php artisan test tests/Feature/HistoricalDataImportTest.php tests/Feature/SimpleHistoricalDataTest.php`

---

## Recent Completion (F1-012)

**Task:** Social and head-to-head comparison mode — done

**What was done:**
- Added `ChartDataService::getHeadToHeadComparison()` and `getHeadToHeadScoreProgression()` for selected users in a season.
- Added `/leaderboard/compare` route with shareable URL `?season=YEAR&users=1,2,3`; multi-select form and comparison table with cumulative score chart.
- Added "Head-to-Head Compare" and "Compare" links on leaderboard index and user-stats pages.
- Fixed layout `$slot` vs `@yield('content')` so both @extends and component usage work.
- Extended `ChartDataServiceTest` and new `LeaderboardTest` with head-to-head and compare tests.

**Tests:** `php artisan test tests/Feature/LeaderboardTest.php tests/Feature/ChartDataServiceTest.php`
