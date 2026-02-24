## Formula1Predictions TODO Backlog

- Schema version: 1
- Purpose: Backlog for the Formula1Predictions Laravel 12 + Livewire app
- References:
  - [AGENTS.md](AGENTS.md) (commands, conventions, handoff)
  - [README.md](README.md) (scoring rules, project layout)
- Status values: `todo` | `in_progress` | `blocked` | `done` | `cancelled`
- Owners: AI-executable work (agent or mixed), with humans free to add or adjust tasks
- Agent workflow: Agents MUST remove completed items and ADD new items they identify

---

### Now

Short-horizon, high-value tasks for **complete, playable, shippable** state. **2026 MVP deadline: 2026-02-20.**

- [x] **F1-086: Align auth pages with main site layout and theme** _(done 2026-02-20)_
  - Auth layout (simple.blade.php) uses same html/body classes, partials.head, branding (logo, app name, tagline), zinc/red typography, Mary UI forms, theme-safe labels/links. Feature tests for /login and /register assert 200, data-appearance, shared branding, body classes, blocking script in head.

- [x] **F1-087: Stabilize dark mode and appearance handling** _(done 2026-02-20)_
  - Theme set once in layout Blade ($appearance, @class dark, data-appearance); blocking script in partials.head runs before body; main and auth share same body classes (min-h-screen antialiased bg-white dark:bg-zinc-900). Feature test asserts dark session sets html class="dark" for consistent background.

- [x] **F1-069: Enable email verification** _(done 2026-02-20)_
  - User implements MustVerifyEmail (contract + trait); main auth group and analytics/F1 API use `verified` middleware; verification routes remain auth-only. EnsureAdminUser sets email_verified_at on create. README documents mail for production. Tests: verification email on registration, unverified redirect, verified access, EnsureAdminUser email_verified_at. Auth changes are review-required per AGENTS.md.

- [x] **F1-080: Feedback page for users to message site owner** _(done 2026-02-19)_
  - Type: feature | Priority: P2 | Risk: low | Owner: agent
  - GET/POST via Livewire at `feedback` route; form (message required, subject optional); stored in `feedback` table; optional email to `MAIL_FEEDBACK_TO`; link in layout user dropdown; tests in FeedbackTest.

- [x] **F1-076: Create supervisor config for queue workers** _(done 2026-02-20)_
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Done: deployment/supervisord.conf for laravel-worker (queue:work, database queue); README "Supervisor (self-hosted queue worker)" and AGENTS bullet for placement and supervisorctl/supervisord commands.

- [x] **F1-094: Migrate leaderboard views (index, compare) from daisyUI to Mary UI** _(done 2026-02-20)_
  - Type: UI | Priority: P2 | Risk: medium | Owner: agent
  - Done: resources/views/leaderboard/index.blade.php and compare.blade.php use x-layouts.layout; daisyUI replaced with x-mary-card, x-mary-button, x-mary-badge, x-mary-avatar; tables use zinc palette (thead/tbody/divide); native selects with zinc styling for GET forms; behavior and routes preserved. Tests and pint run.

**Shippable v1 – complete (2026-02-20).** All items done: F1-084, F1-086/087, F1-069, F1-080, F1-072, F1-076, F1-093, F1-094, F1-095, F1-104 (+ test fixes).
- [x] **F1-093:** Fix WCAG contrast on predictions/show and leaderboard pages _(done 2026-02-20)_
- [x] **F1-095:** Add/verify mobile overflow-x-auto wrappers on standings and leaderboard tables _(done 2026-02-20)_
  - Verified: standings/drivers.blade.php, standings/teams.blade.php, leaderboard/index.blade.php, leaderboard/compare.blade.php, and livewire/global-leaderboard.blade.php (prediction standings) all wrap data tables in `<div class="overflow-x-auto">`. No changes required.
- [x] **F1-104:** Fix prediction request validation inconsistency _(done 2026-02-20)_
---

### Done (Now)

- [x] **F1-081: Tighten route and auth feature tests** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: medium | Owner: agent
  - Affected: tests/Feature/RoutesTest.php, tests/Feature/ViewsTest.php, tests/Feature/WebsiteNavigationTest.php
  - Current route tests intentionally allow 500 responses (status in [200, 500]) and don’t consistently exercise authenticated-only pages. Add smoke tests that:
    - Assert 200 for all key public routes (home, current-season races/standings, country/driver/constructor/circuit detail) using F1ApiService mocks where needed.
    - Log in as a normal user and as an admin (is_admin=true) and hit dashboard, analytics, settings pages, prediction CRUD, and admin routes, asserting 200 and correct redirects when unauthenticated.
    - Avoid hitting the real F1 API in tests.
  - Done: F1ApiService mock (RoutesTest, ViewsTest); auth smoke tests (redirects, user pages, admin dashboard). Admin sub-routes (users, predictions, etc.) lack views—only dashboard tested.

- [x] **F1-082: Fix 2026 standings and prediction standings pages** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: high | Owner: mixed
  - Affected: routes/web.php, resources/views/standings.blade.php, resources/views/standings/*.blade.php, app/Livewire/GlobalLeaderboard.php
  - Done: Prediction standings already use GlobalLeaderboard (real users). GlobalLeaderboard now keeps URL season and current_season in availableSeasons so /2026/standings/predictions shows 2026 and real data. Added Standings2026Test (200 + year in heading, no fake usernames, real users with predictions appear).

- [x] **F1-089: Create missing admin views (5 pages return 500)** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: high | Owner: agent
  - Affected: resources/views/admin/users.blade.php, admin/predictions.blade.php, admin/races.blade.php, admin/scoring.blade.php, admin/settings.blade.php
  - Done: Created all 5 views using main layout and dashboard styling; users (paginated with predictions count/sum), predictions (paginated with user, score/lock/unlock/delete actions), races (paginated list), scoring (races with pending count and score/queue/half-points actions), settings (placeholder). Added test "admin can load all admin view pages" in AdminControllerTest.

- [x] **F1-090: Implement detail pages with real data (team, driver, circuit, country, race)** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: high | Owner: mixed
  - Affected: resources/views/team.blade.php, driver.blade.php, circuit.blade.php, country.blade.php, race.blade.php, routes/web.php
  - Done: Updated all 5 route closures to resolve models by computed slug and abort 404 for invalid slugs. Rewrote all 5 blade views to use real model data (attributes, relationships, accessors) instead of hardcoded stubs. Both `/{year}/race/{id}` and `/race/{slug}` now resolve the Race model. Added 16 tests in DetailPageTest.php covering real data display, 404 handling, and absence of hardcoded data. Updated RoutesTest.php to create models before asserting 200.

- [x] **F1-091: Implement countries index page with real data** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: medium | Owner: agent
  - Affected: app/Livewire/Pages/CountriesIndex.php, resources/views/livewire/pages/countries-index.blade.php, routes/web.php
  - Done: Replaced hardcoded view with Livewire full-page component. Database-backed Countries query, pagination (9 per page), functional filters: search (name/code), status (all/active/historic), championships (all/1-5/6-10/10+). Region dropdown left as "All Regions" (no DB column). Fixed Countries::getFlagUrlAttribute recursion. Added CountriesIndexTest (8 tests). Removed old countries.blade.php.

- [x] **F1-092: Fix Prediction model mass assignment vulnerability** _(done 2026-02-10)_
  - Type: security | Priority: P1 | Risk: high | Owner: agent
  - Done: Removed score, accuracy, status, submitted_at, locked_at, scored_at from $fillable. ScoringService, AdminController, BotPredictionsSeeder, HistoricalPredictionsSeeder use forceFill() for system fields; PredictionForm uses submit() after create/update. PredictionFactory states submitted(), locked(), scored() for tests. Added test mass-assigning score or status via create is rejected.

---

---

### Next

Medium-horizon improvements that should be tackled soon.

- [x] **F1-050: Configure production mail, session security, and logging** _(done 2026-02-10)_
  - Type: security | Priority: P1 | Risk: high | Owner: agent
  - Affected: .env.example, config/session.php, config/logging.php, tests/Feature/ProductionConfigTest.php
  - Implemented: session.secure defaults to true when APP_URL uses https; .env.example documents MAIL_MAILER (production: smtp), SESSION_SECURE_COOKIE, LOG_LEVEL (production: warning/error), LOG_STACK (production: daily for rotation). Added ProductionConfigTest for session secure, logging daily channel, and env example docs.

- [x] **F1-055: Add proper exception handling for user-facing controllers** _(done 2026-02-09)_
  - Type: security | Priority: P1 | Risk: medium | Owner: agent
  - Affected: StripeCheckoutController.php, RacesController.php, AdminController.php
  - Implemented: generic user-facing error messages; Log::error with exception context; no raw $e->getMessage() to users. Admin view methods wrapped in try/catch with redirect+flash on failure.

---

### Later / Ideas

Longer-horizon ideas and exploratory improvements.

- [ ] **F1-031: Monetization strategy (premium features)** _(deferred to later release)_
  - Type: feature | Priority: P3 | Risk: high | Owner: human
  - Non-gambling, non-ad monetization to recover costs. Ideas: premium stats/analytics, badges, special abilities. Free tier must allow full gameplay. No 3rd party ads. Minimal self-promotion (lock icon + "become a member" button). Needs cost-per-user and revenue-per-user analysis.
  - Note: Monetization and payments for cost recovery are in scope; only gambling/real-money betting is forbidden. Implementing payment/billing code requires explicit human approval. Stripe/Season Supporter routes and settings UI are commented out until this is picked up.

- [ ] **F1-019: Phase 2 legacy import (CSV/JSON external sources)**
  - Type: feature | Priority: P3 | Risk: high | Owner: mixed
  - Affected: database/seeders/*, app/Console/Commands/*
  - Extend legacy import beyond Phase 1 markdown. Support CSV/JSON or external DB dumps when representative data and human-approved migrations are available.
  - Depends on: F1-006A (Phase 1 done)
  - Note: Deferred until representative data and human approval.

- [x] **F1-059: Enable session encryption** _(done 2026-02-10)_
  - Type: security | Priority: P2 | Risk: medium | Owner: agent
  - Affected: .env.example
  - Done: Set SESSION_ENCRYPT=true in .env.example so production deployments copied from the template use encrypted sessions by default. Added ProductionConfigTest assertion to ensure the template keeps this default.

- [ ] **F1-060: Optimize NotificationService user loading**
  - Type: performance | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Services/NotificationService.php
  - NotificationService loads ALL users into memory with User::all(). Use chunking or pagination.

- [x] **F1-061: Remove empty scaffold controllers (dead code)** _(done 2026-02-18)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Done: Removed TeamsController, StandingsController, DriversController, CountriesController, CircuitsController and their 10 form requests (Store/Update for each).

- [x] **F1-107: Orphan views (repo map)** _(done 2026-02-18)_
  - Added `GET /scoring` and sidebar link "How scoring works". Merged title/description into create-livewire and edit-livewire; removed predictions/create, predictions/edit, predict/create, predict/edit.

- [x] **F1-108: Orphan components (repo map)** _(done 2026-02-18)_
  - Removed components/prediction-form.blade.php, components/layouts/auth/card.blade.php, components/layouts/auth/split.blade.php, components/placeholder-pattern.blade.php, components/layouts/app.blade.php.

- [x] **F1-109: RacesController form requests (with F1-102)** _(done 2026-02-18)_
  - Removed RacesController create/store/edit/update/destroy and deleted StoreRacesRequest.php, UpdateRacesRequest.php.

- [x] **F1-083: Fix races page theming and 500s for current season** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: medium | Owner: agent
  - Affected: resources/views/races.blade.php, app/Livewire/Races/RacesList.php, resources/views/livewire/races/partials/race-card.blade.php
  - Done: Races page header uses theme-safe text (text-zinc-900 dark:text-zinc-100, text-zinc-600 dark:text-zinc-400). Race-card partial: safe circuit access ($circuit = is_array($race['circuit'] ?? null) ? $race['circuit'] : []), try/catch for Carbon date/time parse, zinc text classes; getGroupedRacesProperty skips non-array items. RacesList already had F1ApiService try/catch. Added test "current season races page returns 200 and shows error state when API fails" in RacesPageTest.

- [x] **F1-084: Replace prediction standings mock table with real leaderboard** _(done 2026-02-19)_
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Implemented per docs/DESIGN-STANDINGS-PREDICTIONS.md: predictions.blade.php uses app layout and only `<livewire:global-leaderboard :season="$year" />`. GlobalLeaderboard uses #[Url] for season, type, sortBy, page; filters map to DB (Prediction.season, type, status=scored); sort/pagination in-memory in render(); real users/predictions only. Standings2026Test and related tests pass.

- [x] **F1-085: Lock components demo route to dev and prevent prod exposure** _(done 2026-02-11)_
  - Type: cleanup | Priority: P2 | Risk: low | Owner: agent
  - Route already registered only in local/testing (web.php). Sidebar uses `Route::has('components')`. Added smoke test in RoutesTest: components page returns 200 in testing env.

- [x] **F1-088: Fix /components page 500 Server Error** _(done 2026-02-11)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Fixed: Replaced missing `o-shield` icon with `o-shield-check` in components.blade.php (SvgNotFound from heroicons set).

- [x] **F1-086: Align auth pages with main site layout and theme** _(done 2026-02-20)_
  - Done: Auth layout aligned per AUTH_LAYOUT_DESIGN.md; feature tests for /login, /register.

- [x] **F1-087: Stabilize dark mode and appearance handling** _(done 2026-02-20)_
  - Done: Single theme init; blocking script in head; feature test for dark html class.

- [x] **F1-062: Remove hardcoded mockup data from edit prediction view** _(done 2026-02-18)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Done: Orphan predictions/edit.blade.php was removed in F1-107; edit flow uses edit-livewire only.

- [ ] **F1-063: Remove console.log debug statements from production JS**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/js/notifications.js (L11, L79)
  - console.log debug statements in production JS. Remove.

- [x] **F1-064: Fix clearAllCache() to cover all years** _(done 2026-02-10)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Services/F1ApiService.php
  - Fixed: Loop now clears years 2020 through current_season + 1 (covers 2026 and next season). Added test.

- [x] **F1-065: Remove sensitive fields from User $fillable** _(done 2026-02-10)_
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Done: Removed is_admin, is_season_supporter, supporter_since, badges, stats_cache, stats_cache_updated_at from $fillable. Added is_admin to $hidden. AdminSeeder, EnsureAdminUser, PromoteAdminUser, TestUserSeeder use forceFill(). UserFactory::admin() state for tests. Added UserModelTest (mass-assign create/update rejected).

- [ ] **F1-066: Remove redundant indexes**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: database/migrations/
  - Redundant indexes on 4 tables (unique + explicit index on same column). Clean up.

- [ ] **F1-067: Remove empty no-op migration file**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: database/migrations/2025_08_26_100104
  - Empty no-op migration file. Remove.

- [x] **F1-068: Add index on predictions.race_id** _(done 2026-02-24)_
  - Type: performance | Priority: P2 | Risk: low | Owner: agent
  - Affected: database/migrations/
  - Done: Added migration to create an index on predictions.race_id and a database schema test (DatabaseIndexesTest) to assert the index exists after migrations.

- [x] **F1-069: Enable email verification** _(duplicate; see Now section — done 2026-02-20)_

- [x] **F1-070: Remove laravel/tinker from production dependencies** _(done; verify checkbox if implemented)_
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Moved `laravel/tinker` to `require-dev`; add ProductionConfigTest assertion if not already present.

- [x] **F1-071: Update .gitignore for storage/logs/ subdirectories** _(done 2026-02-24)_
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Affected: .gitignore
  - Added pattern to ignore `storage/logs/**/*.log` so log files in subdirectories are not committed.

- [x] **F1-072: Set up CI/CD pipeline** _(done 2026-02-19)_
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: .github/workflows/ci.yml
  - Done: CI workflow and test-batches.sh added. GitHub Actions on push/PR to main and master; PHP 8.4, two-batch tests via scripts/test-batches.sh, npm run build, optional Pint (continue-on-error). Documented in README and AGENTS.md.

- [ ] **F1-073: Create Dockerfile and docker-compose for production**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: Dockerfile, docker-compose.yml
  - No Dockerfile or docker-compose for production. Create for containerized deployment.

- [x] **F1-074: Create deployment script** _(done 2026-02-10)_
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: deploy.sh, Envoy.blade.php, or Forge config
  - Done: Added deploy.sh at project root to pull latest code, install PHP dependencies without dev packages, run migrations, build frontend assets, and warm Laravel caches. Added DeploymentScriptTest to ensure the script exists and includes key deployment steps.

- [x] **F1-075: Create production .env template** _(done 2026-02-11)_
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: .env.production.example
  - No production .env template. Create with production-appropriate values.

- [x] **F1-076: Create supervisor config for queue workers** _(done 2026-02-20)_
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: deployment/supervisord.conf, README, AGENTS.md
  - Done: deployment/supervisord.conf (laravel-worker, queue:work, database); README subsection and AGENTS bullet for placement and run commands.

- [x] **F1-077: Document server cron setup for scheduled tasks** _(done 2026-02-19)_
  - Type: documentation | Priority: P2 | Risk: low | Owner: agent
  - Affected: README.md, AGENTS.md
  - Done: README “Railway deployment” and AGENTS “Railway” describe cron via railway/run-cron.sh loop or Railway cron; queue worker as separate service with php artisan queue:work.

- [ ] **F1-078: Admin panel with appropriate actions**
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: routes, controllers, policies, admin views
  - Central admin UI for managing users, content, and app operations (e.g. promote admin, lock predictions, moderate content). Build on existing admin routes/controllers; add actions as needed.

- [ ] **F1-079: RSS-compatible News page for admins to post updates**
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: database (news/announcements table or similar), admin UI, public News page, RSS feed route
  - News/announcements model and CRUD for admins; public News page and RSS feed so users can subscribe to updates.

- [x] **F1-080: Feedback page for users to message site owner** _(done 2026-02-19)_
  - Type: feature | Priority: P2 | Risk: low | Owner: agent
  - GET/POST via Livewire; `feedback` table; optional MAIL_FEEDBACK_TO; link in layout; FeedbackTest.

- [x] **F1-093: Fix WCAG contrast failures on predictions and leaderboard pages** _(done 2026-02-20)_
  - Type: accessibility | Priority: P2 | Risk: low | Owner: agent
  - Done: predictions/show.blade.php — `text-zinc-500` on dark sidebar → `text-zinc-300 dark:text-zinc-200`; score → `text-green-400 dark:text-green-300`; Notes heading and card subtitle → `text-zinc-600 dark:text-zinc-400`. Leaderboard index/season/race/compare and global-leaderboard: `text-gray-400`/`opacity-50`/`text-zinc-500` → `text-zinc-600 dark:text-zinc-400` or `text-zinc-300` for medals. AccessibilityTest and pint --dirty run.

- [ ] **F1-094: Migrate leaderboard views from daisyUI to Mary UI** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: medium | Owner: agent
  - Affected: resources/views/leaderboard/index.blade.php, resources/views/leaderboard/compare.blade.php
  - Both files use old `@extends('components.layouts.layout')` pattern (should be `<x-layouts.layout>`) and 45+ daisyUI classes: `.btn`, `.btn-primary`, `.btn-outline`, `.card`, `.card-body`, `.form-control`, `.select`, `.select-bordered`, `.table`, `.table-zebra`, `.badge`, `.badge-outline`, `.bg-base-100`.
  - Migrate to Mary UI components (`x-mary-button`, `x-mary-card`, `x-mary-table`, `x-mary-badge`, `x-mary-select`) and the new layout pattern.

- [x] **F1-095: Add mobile responsive wrappers to data tables** _(done 2026-02-20)_
  - Type: UI | Priority: P2 | Risk: low | Owner: agent
  - Verified: All specified views (standings/drivers, standings/teams, leaderboard/index, leaderboard/compare, livewire/global-leaderboard) already wrap tables in `<div class="overflow-x-auto">`. No changes required.

- [ ] **F1-096: Standardize color palette to zinc (remove gray usage)** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/standings/drivers.blade.php, standings/teams.blade.php, resources/views/leaderboard/index.blade.php
  - DESIGN_SYSTEM.md specifies zinc as the neutral palette, but these files use `bg-gray-100`, `text-gray-800`, `dark:bg-gray-900`, `text-gray-400` etc. Replace all `gray-*` with corresponding `zinc-*` classes.

- [ ] **F1-100: Repo map and orphan audit** _(done 2026-02-18)_
  - Type: docs | Priority: P3 | Risk: none | Owner: agent
  - Done: Created docs/REPO-MAP-AND-ORPHANS.md with full route-to-view map and list of orphan views, controllers, and form requests. Optional follow-up: remove orphans or add route for public scoring page.

- [x] **F1-097: Fix predict/create dark mode visibility** _(done 2026-02-18)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Done: Orphan predict/create.blade.php removed in F1-107; create flow uses predictions/create-livewire only.
  - Three color-coded info boxes use `bg-zinc-50`, `bg-blue-50`, `bg-amber-50` with no `dark:` variants. In dark mode these are completely invisible (nearly white boxes on dark background). Add appropriate dark mode variants: e.g. `dark:bg-zinc-700`, `dark:bg-blue-900/30`, `dark:bg-amber-900/30`.

- [ ] **F1-098: Replace daisyUI modal in delete-user-form** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/views/livewire/settings/delete-user-form.blade.php
  - Uses HTML5 `<dialog>` with daisyUI `.modal`/`.modal-box` classes and inline `onclick="deleteModal.showModal()"` handlers. Should use `<x-mary-modal>` component with Alpine.js or Livewire event handling for consistency with the rest of the app.

- [ ] **F1-099: Define or replace undefined CSS utility classes** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/home.blade.php, dashboard.blade.php, standings views, races.blade.php
  - `.text-auto-muted` used in 5+ views, `.bg-card` used in 6 instances on home.blade.php, `.text-shadow` used in home.blade.php hero. None are defined in DESIGN_SYSTEM.md or the app CSS.
  - Either define these classes in the design system CSS (resources/css/app.css) or replace with standard Tailwind utilities.

- [ ] **F1-100: Update focus ring colors to F1 brand** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/views/livewire/auth/login.blade.php (predict/create and components/prediction-form were removed in F1-107/F1-108).
  - Multiple files use `focus:ring-indigo-500` or `focus:ring-blue-500` for focus indicators. DESIGN_SYSTEM.md specifies F1 brand red as primary. Update to `focus:ring-red-600 dark:focus:ring-red-500`.

- [ ] **F1-101: Remove dead duplicate leaderboard routes** _(found 2026-02-10 audit)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: routes/web.php (lines 87-95)
  - `/leaderboard/livewire` and `/leaderboard/user/{user}/livewire` are duplicate routes with Livewire wrappers. The non-Livewire versions (`leaderboard.index`, `leaderboard.user-stats`) are the primary routes linked in navigation. Remove the dead duplicates.

- [x] **F1-102: Clean up RacesController empty CRUD methods** _(done 2026-02-18)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Done: Removed create(), store(), edit(), update(), destroy() from RacesController; deleted StoreRacesRequest and UpdateRacesRequest (F1-109).

- [x] **F1-103: Harden admin password default in config** _(done 2026-02-10)_
  - Type: security | Priority: P2 | Risk: medium | Owner: agent
  - Done: Removed password default from config. EnsureAdminUser and AdminSeeder now require explicit ADMIN_PASSWORD when creating new admins; they skip/fail gracefully when unset.

- [x] **F1-104: Fix prediction request validation inconsistency** _(done 2026-02-20)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Http/Requests/StorePredictionRequest.php, app/Http/Requests/UpdatePredictionRequest.php
  - Done: Both requests validate `prediction_data.superlatives.*` as `['nullable', 'string']`. FormValidationTest and PredictionFormValidationTest cover superlatives.

- [x] **F1-105: Add is_admin to User model $hidden array** _(done 2026-02-10 with F1-065)_
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Done: Added is_admin to User::$hidden so admin status is not exposed in JSON serialization.
  
- [ ] **F1-106: Revisit country detail page and sidebar link**
  - Type: bug | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/views/country.blade.php, components/layouts/layout.blade.php
  - The country detail page is not yet set up correctly for production (data, layout, or navigation expectations). Temporarily hide the sidebar navigation entry until the page is finalized, then align the page with the rest of the site and restore the link.
