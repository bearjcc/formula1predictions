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
```

Use minimal test filters when validating changes. No verification scripts or tinker when tests cover the behavior.

---

## Stack & structure

- **Stack:** PHP 8.4.5, Laravel 12, Livewire 3, Volt, Mary UI (primary) / Flux UI, Tailwind v4, Pest v4, Laravel Pint.
- **Key paths:** `app/Models/` (Prediction, Races, Drivers, Teams, Standings) | `app/Services/` (ScoringService, F1ApiService, ChartDataService, NotificationService) | `app/Livewire/` (PredictionForm, DraggableDriverList, RacesList, Charts/*) | `resources/views/` (Blade + Volt).
- **Integrations:** Use `F1ApiService` for external API; never raw HTTP. Use `ScoringService` as system of record for scoring. Use Laravel Boost MCP when available (search-docs, tinker, get-absolute-url, browser-logs). Herd serves the app at `https://[kebab-dir].test`.

---

## Conventions

- **Laravel:** `php artisan make:*` for new files. `bootstrap/app.php` for middleware; no `app/Console/Kernel.php`. Eloquent and relationships over `DB::`; eager load to avoid N+1. Form Request classes for controller validation; Livewire/Volt validation in components. Named routes and `route()`. `config()` only — never `env()` outside config files. Queued jobs for long work (`ShouldQueue`).
- **PHP:** Curly braces for control structures. Constructor property promotion; explicit return types and parameter types. PHPDoc for array shapes and complex returns. Enums: TitleCase keys.
- **UI:** Mary UI (`x-mary-*`) first; Flux if needed; Blade fallback. Single root per Livewire/Volt component. `wire:key` in loops; `wire:model.live` for real-time; `wire:loading` / `wire:dirty` for loading. Tailwind v4 (`@import "tailwindcss"`); use `gap` for list spacing; support `dark:` where the app does. No deprecated Tailwind utilities (see README/tailwind v4 replacements).
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
- **F1 API:** `F1ApiService` — cache, retries, timeouts; log errors; never crash user flows on API failure.

Preserve model relationships and existing patterns when changing schema or behavior.

---

## Boundaries

**Always:** Follow conventions above; update TODO.md (remove done, add new items you didn’t complete); use Eloquent and Form Requests; run Pint and add/update tests for behavior changes.

**Ask first:** Scoring formula or rule changes in `ScoringService`; migrations that alter/remove columns or risk data loss; auth/authorization changes; new Composer/NPM deps or external services.

**Never:** Read/write `.env` or secrets; drop tables or irreversibly change data outside migrations; disable/delete tests to get green; implement gambling, payments, or betting.

---

## Autonomy by area

- **Safe (no review):** UI-only tweaks, localized bug fixes and refactors with tests, new analytics that don’t change scores or standings.
- **Review required:** Any scoring change that can alter user scores; migrations that remove/alter columns or cause data loss; auth/policy changes; external API endpoint/key changes.
- **Forbidden:** Touching secrets; dropping production tables; gambling/payments.

---

## TODO & handoff

- **TODO.md:** Single backlog; schema in file. Status: `todo` | `in_progress` | `blocked` | `done` | `cancelled`. When finishing work: set `done`, add brief completion note. When leaving work incomplete: remove done items, add new items you identified. Respect `risk_level`, `owner`, and `affected_areas`.
- **Handoff template:** Task title and goal; current status (test pass rate, what’s done); key areas to focus on; recent accomplishments; env (test command, key files).

Note recurring pitfalls or better patterns in handoff notes (1–3 lines) for the next agent.

---

## Completed tasks (summary)

F1-000 (MVP scope) through F1-018; F1-020 (race diffs 10–19 to spec); F1-021 (sprint scoring weights, FL +5, perfect bonus +15 top-8-only); F1-024 (WebsiteNavigationTest mock + RacesController route param fix). Details in git history.

---

## Current session handoff (2026-02-08)

**Task:** Scoring fixes to match spec, test fixes.

**Status:** 373 tests passing, 0 failing. Scoring now matches spec for race position diffs 0–20+ and sprint scoring (weights, fastest lap, perfect bonus). BacktestScoringHarness updated to match.

**Completed this session:**
- F1-020: Race `getPositionScore()` now uses explicit match for diffs 10–19 (non-linear spec values).
- F1-021: Sprint `getSprintPositionScore()` rewritten (0→8..7→1, 8+→0), fastest lap +5, perfect bonus +15 (top 8 only).
- F1-024: WebsiteNavigationTest mocks F1ApiService; RacesController `index()` now accepts route `$year` param.
- Fixed LivewirePredictionFormTest strict type comparison (Livewire hydration converts int IDs to strings).

**Focus next:** F1-022 (DNF wager, mixed); F1-023 (half-points, mixed); F1-025 (auto-lock); F1-026 (2026 data pipeline).

**Key files:** `app/Services/ScoringService.php`, `tests/Feature/ScoringServiceTest.php`, `tests/Support/BacktestScoringHarness.php`.
