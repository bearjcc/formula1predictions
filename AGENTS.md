# Formula1Predictions — Agent Instructions

Single reference for AI agents: commands, conventions, guardrails, handoff. Scoring rules live in [README.md](README.md#scoring). Backlog in [TODO.md](TODO.md).

---

## Commands

```bash
php artisan test
php artisan test tests/Feature/ScoringServiceTest.php
php artisan test --filter=testName
composer run test:coverage   # optional, needs pcov
vendor/bin/pint --dirty     # run before finalizing
npm run build               # after UI changes (or npm run dev / composer run dev)
pip install -r scripts/requirements.txt && python scripts/prepare-favicon.py   # regenerate favicon/logo from scripts/source/venice-f1-car.png
```

**First admin (deploy):**
- **Local:** Register a user, then promote: `php artisan app:promote-admin you@example.com` or set `ADMIN_EMAIL` in .env and run `php artisan app:promote-admin`.
- **Railway/Production:** Set `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NAME` in environment variables. The start command runs `app:ensure-admin-user` on every deploy—creates admin if not exist, sets `email_verified_at`, never overwrites password on existing users.

Use minimal test filters when validating changes. No verification scripts or tinker when tests cover the behavior. If full `php artisan test` times out (e.g. Windows), run `.\scripts\test-batches.ps1` or run in two batches: (1) `php artisan test tests/Unit tests/Feature/AccessibilityTest.php tests/Feature/AdminControllerTest.php tests/Feature/Auth tests/Feature/AutoLockPredictionsTest.php tests/Feature/BasicPhase1Test.php tests/Feature/BotPredictionsSeederTest.php tests/Feature/BotsSeedCommandTest.php tests/Feature/ChampionshipOrderBotSeederTest.php tests/Feature/ChartDataServiceTest.php tests/Feature/ConsoleCommandsTest.php tests/Feature/DashboardTest.php tests/Feature/DataVisualizationTest.php tests/Feature/DraggableDriverListTest.php tests/Feature/DraggableTeamListTest.php tests/Feature/F1ApiTest.php tests/Feature/FakerBasicSeederTest.php tests/Feature/FormValidationTest.php` (165 tests); (2) remaining tests in tests/Feature/.

---

## Stack & structure

- **Stack:** PHP 8.4.5, Laravel 12, Livewire 3, Volt, Mary UI, Tailwind v4, Pest v4, Laravel Pint.
- **Key paths:** `app/Models/` (Prediction, Races, Drivers, Teams, Standings) | `app/Services/` (ScoringService, F1ApiService, ChartDataService, NotificationService) | `app/Livewire/` (PredictionForm, DraggableDriverList, RacesList, Charts/*) | `resources/views/` (Blade + Volt).
- **Integrations:** Use `F1ApiService` for external API; never raw HTTP. Use `ScoringService` as system of record for scoring. Use Laravel Boost MCP when available (search-docs, tinker, get-absolute-url, browser-logs). Herd serves the app at `https://[kebab-dir].test`.

---

## Conventions

- **Laravel:** `php artisan make:*` for new files. `bootstrap/app.php` for middleware; no `app/Console/Kernel.php`. Eloquent and relationships over `DB::`; eager load to avoid N+1. Form Request classes for controller validation; Livewire/Volt validation in components. Named routes and `route()`. `config()` only — never `env()` outside config files. Queued jobs for long work (`ShouldQueue`).
- **PHP:** Curly braces for control structures. Constructor property promotion; explicit return types and parameter types. PHPDoc for array shapes and complex returns. Enums: TitleCase keys.
- **UI:** Mary UI (`x-mary-*`) first; Blade fallback. Single root per Livewire/Volt component. `wire:key` in loops; `wire:model.live` for real-time; `wire:loading` / `wire:dirty` for loading. Tailwind v4 (`@import "tailwindcss"`); use `gap` for list spacing; support `dark:` where the app does. No deprecated Tailwind utilities (see README/tailwind v4 replacements).
- **Livewire/Volt:** State on server; validate and authorize in actions. Use `mount()`, `updatedFoo()` for init and side effects. Volt for new interactive pages: `php artisan make:volt [name] [--pest]`. Namespace `App\Livewire`; dispatch with `$this->dispatch()`.
- **Testing:** Pest only. `php artisan make:test --pest <name>`. Feature tests preferred. Use `assertForbidden`, `assertNotFound` etc. instead of raw `assertStatus`. Mock external APIs (e.g. `Http::fake()`); do not hit real F1 API in tests. Factories for models; datasets for validation tests. Browser tests in `tests/Browser/`.
- **Format:** Run `vendor/bin/pint --dirty` before finalizing. Do not remove or disable tests to get green.
- **Docs:** Create docs only when the user asks. Follow sibling-file patterns and existing structure.

---

## Domains & key files

- **Races/calendar:** `Races`, `Circuits`, `Countries`; results JSON on race; list/detail views.
- **Predictions/scoring:** `Prediction` (types: race, preseason, midseason; statuses: draft, submitted, locked, scored, cancelled); `prediction_data` JSON; `ScoringService` for all scoring and edge cases (DNS/DSQ/DNF/cancellation, overrides).
- **Standings/leaderboards:** `Standings` (drivers/constructors); leaderboard routes/controllers.
- **Analytics:** `ChartDataService`; Livewire chart components under `app/Livewire/Charts/*`.
- **Notifications:** `NotificationService`; `app/Events/*`, `app/Notifications/*`; notification dropdown components.
- **Admin/jobs:** Admin controllers/views; `ScoreRacePredictionsJob` and other jobs in `app/Jobs/*`.
- **F1 API:** `F1ApiService` — cache, retries, timeouts; log errors; never crash user flows on API failure. On deploy, `f1:ensure-season-data` runs at startup to preload current year (races, drivers, teams) if missing; no user interaction required.

Preserve model relationships and existing patterns when changing schema or behavior.

---

## Boundaries

**Always:** Follow conventions above; update TODO.md (remove done, add new items you didn't complete); use Eloquent and Form Requests; run Pint and add/update tests for behavior changes.

**Ask first:** Scoring formula or rule changes in `ScoringService`; migrations that alter/remove columns or risk data loss; auth/authorization changes; new Composer/NPM deps or external services.

**Never:** Read/write `.env` or secrets; drop tables or irreversibly change data outside migrations; disable/delete tests to get green; implement gambling or real-money betting.

**Monetization:** We need a monetization strategy to cover costs. Cost-recovery monetization is allowed (e.g. subscriptions, tips, season supporter, premium features). Implementing payment/billing code requires explicit approval. Gambling and real-money betting on race outcomes are forbidden.

---

## Autonomy by area

- **Safe (no review):** UI-only tweaks, localized bug fixes and refactors with tests, new analytics that don't change scores or standings.
- **Review required:** Any scoring change that can alter user scores; migrations that remove/alter columns or cause data loss; auth/policy changes; external API endpoint/key changes; payment/billing implementation.
- **Forbidden:** Touching secrets; dropping production tables; gambling or real-money betting. Monetization to cover costs is allowed.

---

## TODO & handoff

- **TODO.md:** Single backlog; schema in file. Status: `todo` | `in_progress` | `blocked` | `done` | `cancelled`. When finishing work: set `done`, add brief completion note. When leaving work incomplete: remove done items, add new items you identified. Respect `risk_level`, `owner`, and `affected_areas`.
- **Handoff template:** Task title and goal; current status (test pass rate, what's done); key areas to focus on; recent accomplishments; env (test command, key files).

Note recurring pitfalls or better patterns in handoff notes (1–3 lines) for the next agent.

---

## Completed tasks (summary)

F1-000 (MVP scope) through F1-018; F1-020 (race diffs 10–19 to spec); F1-021 (sprint scoring weights, FL +5, perfect bonus +15 top-8-only); F1-024 (WebsiteNavigationTest mock + RacesController route param fix). Details in git history.

---

## Current session handoff (2026-02-08)

**Task:** Fix failing tests; backlog hygiene.

**Status:** Test fixes and F1-033 verification complete. Unit 5 + Feature batch 1 (160) = 165 passed. Feature batch 2 (HistoricalDataImport through Settings) passes when run; full `php artisan test` may timeout on Windows—run in two batches (see Commands section).

**Completed this session:**
- User::getDetailedStats: implemented top_3_count and bottom_3_count (per-race leaderboard position tracking); removed stale PHPDoc TODOs.
- N+1 fixes: ScoringService scoreRacePredictions/scoreSprintPredictions and ScoreHistoricalPredictions now eager load user on predictions.
- test-batches.ps1: added missing TestUserSeederTest.php to batch 2.
- AutoLockPredictionsTest, F1ApiTest, ModelRelationshipsTest, PredictionFormValidationTest, WebsiteNavigationTest: uses(RefreshDatabase::class); F1ApiTest Http::fake URL and factory fix.
- ModelRelationshipsTest: $user->refresh() before relationship assert in "user can have many predictions" for robustness when run in batch.
- TODO.md: removed done items from Later (F1-030, F1-032).
- F1-033: Verified test suite; Unit 5 + Feature batch 1 (160) pass; documented two-batch run in AGENTS Commands.
- Ran `vendor/bin/pint` (161 style fixes). README Testing section: added note about two-batch run when full suite times out.
- Added `scripts/test-batches.ps1`: runs Unit + Feature in two batches; README and AGENTS reference it.

**Focus next:** F1-031 (monetization, human-led) or F1-019 when unblocked.

**Key files:** `tests/Feature/AutoLockPredictionsTest.php`, `tests/Feature/F1ApiTest.php`, `TODO.md`.
