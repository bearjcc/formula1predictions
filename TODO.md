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

Short-horizon, high-value tasks ready to pick up. **2026 MVP deadline: 2026-02-20.**

- [x] **F1-081: Tighten route and auth feature tests** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: medium | Owner: agent
  - Affected: tests/Feature/RoutesTest.php, tests/Feature/ViewsTest.php, tests/Feature/WebsiteNavigationTest.php
  - Current route tests intentionally allow 500 responses (status in [200, 500]) and don’t consistently exercise authenticated-only pages. Add smoke tests that:
    - Assert 200 for all key public routes (home, current-season races/standings, country/driver/team/circuit detail) using F1ApiService mocks where needed.
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

- [ ] **F1-059: Enable session encryption**
  - Type: security | Priority: P2 | Risk: medium | Owner: agent
  - Affected: .env.example
  - Session encryption disabled (SESSION_ENCRYPT=false). Enable for production.

- [ ] **F1-060: Optimize NotificationService user loading**
  - Type: performance | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Services/NotificationService.php
  - NotificationService loads ALL users into memory with User::all(). Use chunking or pagination.

- [ ] **F1-061: Remove empty scaffold controllers (dead code)**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: DriversController.php, TeamsController.php, etc.
  - 6 empty scaffold controllers (dead code). Remove or implement.

- [x] **F1-083: Fix races page theming and 500s for current season** _(done 2026-02-10)_
  - Type: bug | Priority: P1 | Risk: medium | Owner: agent
  - Affected: resources/views/races.blade.php, app/Livewire/Races/RacesList.php, resources/views/livewire/races/partials/race-card.blade.php
  - Done: Races page header uses theme-safe text (text-zinc-900 dark:text-zinc-100, text-zinc-600 dark:text-zinc-400). Race-card partial: safe circuit access ($circuit = is_array($race['circuit'] ?? null) ? $race['circuit'] : []), try/catch for Carbon date/time parse, zinc text classes; getGroupedRacesProperty skips non-array items. RacesList already had F1ApiService try/catch. Added test "current season races page returns 200 and shows error state when API fails" in RacesPageTest.

- [ ] **F1-084: Replace prediction standings mock table with real leaderboard**
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: resources/views/standings/predictions.blade.php, app/Livewire/GlobalLeaderboard.php, related views
  - The prediction standings page currently shows three hard-coded demo users and non-functional filter dropdowns. Integrate it with the existing GlobalLeaderboard data so the page:
    - Shows the full leaderboard for the selected year/season.
    - Uses functional filters (season, type, sort) wired to Livewire, not static `<select>`s.
    - Has tests to verify that created users/predictions appear in the table and filters affect the result set.

- [ ] **F1-085: Lock components demo route to dev and prevent prod exposure**
  - Type: cleanup | Priority: P2 | Risk: low | Owner: agent
  - Affected: routes/web.php, resources/views/components.blade.php, components/layouts/layout.blade.php
  - `/components` is a Mary UI demo page that can 500 in development and should never appear in production navigation. Ensure:
    - The route is registered only in `local`/`testing` environments.
    - The sidebar link is wrapped in `Route::has('components')` (already present) and is hidden in production.
    - The components view is resilient enough not to 500 on missing assets; add a minimal smoke test in `testing` env to catch regressions.

- [ ] **F1-088: Fix /components page 500 Server Error** _(found 2026-02-10 sidebar QA)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: routes/web.php (GET /components), resources/views/components.blade.php, layout/ Mary UI components
  - Sidebar link "Components" (Mary UI demo) returns 500 when loaded. View uses `<x-layouts.layout>` and various `<x-mary-*>` components. Root cause not yet identified (check laravel.log with APP_DEBUG=true when hitting `/components`). Fix so the page renders in local/testing; can be done as part of F1-085.

- [ ] **F1-086: Align auth pages with main site layout and theme**
  - Type: UI | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: resources/views/livewire/auth/*.blade.php, components/layouts/auth*.blade.php, components/layouts/layout.blade.php
  - Login/register/forgot/reset pages currently use a different layout and feel disconnected from the main app. Update them to:
    - Share visual language (colors, typography, spacing) with the primary layout.
    - Respect the same dark-mode behavior (no half-light/half-dark flash).
    - Have feature tests that visit `/login` and `/register` and assert the presence of shared branding/layout elements.

- [ ] **F1-087: Stabilize dark mode and appearance handling**
  - Type: UI | Priority: P2 | Risk: medium | Owner: agent
  - Affected: components/layouts/layout.blade.php, components/layouts/auth*.blade.php, resources/views/partials/head.blade.php, appearance settings
  - Many pages render with mixed light/dark styles or flash between modes before settling. Standardize the theme initialization so:
    - The `<html>`/`<body>` classes and `data-appearance` are set once, early, based on system or user preference.
    - All layouts (main, auth, settings) use the same color tokens and background/text utilities.
    - Add a small feature test (or Dusk/browser test later) to ensure dark-mode pages render without obvious conflicting background/text colors.

- [ ] **F1-062: Remove hardcoded mockup data from edit prediction view**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/views/predictions/edit.blade.php (L204)
  - Hardcoded mockup data in edit prediction view. Remove.

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

- [ ] **F1-068: Add index on predictions.race_id**
  - Type: performance | Priority: P2 | Risk: low | Owner: agent
  - Affected: database/migrations/
  - Missing index on predictions.race_id. Add for query performance.

- [ ] **F1-069: Enable email verification**
  - Type: security | Priority: P2 | Risk: medium | Owner: agent
  - Affected: app/Models/User.php
  - Email verification commented out (MustVerifyEmail). Uncomment and configure.

- [ ] **F1-070: Remove laravel/tinker from production dependencies**
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Affected: composer.json
  - laravel/tinker in production dependencies. Move to require-dev.

- [ ] **F1-071: Update .gitignore for storage/logs/ subdirectories**
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Affected: .gitignore
  - .gitignore doesn't cover storage/logs/ subdirectories. Add.

- [ ] **F1-072: Set up CI/CD pipeline**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: .github/workflows/ or .gitlab-ci.yml
  - No CI/CD pipeline (no GitHub Actions, no GitLab CI). Set up automated testing and deployment.

- [ ] **F1-073: Create Dockerfile and docker-compose for production**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: Dockerfile, docker-compose.yml
  - No Dockerfile or docker-compose for production. Create for containerized deployment.

- [ ] **F1-074: Create deployment script**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: deploy.sh, Envoy.blade.php, or Forge config
  - No deployment script (no Forge, Vapor, Envoy, or shell script). Create.

- [ ] **F1-075: Create production .env template**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: .env.production.example
  - No production .env template. Create with production-appropriate values.

- [ ] **F1-076: Create supervisor config for queue workers**
  - Type: infrastructure | Priority: P2 | Risk: medium | Owner: agent
  - Affected: supervisord.conf
  - No supervisor config for queue workers. Create for production queue management.

- [ ] **F1-077: Document server cron setup for scheduled tasks**
  - Type: documentation | Priority: P2 | Risk: low | Owner: agent
  - Affected: README.md or DEPLOYMENT.md
  - Scheduled task (predictions:lock-past-deadline) needs server cron. Document setup instructions.

- [ ] **F1-078: Admin panel with appropriate actions**
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: routes, controllers, policies, admin views
  - Central admin UI for managing users, content, and app operations (e.g. promote admin, lock predictions, moderate content). Build on existing admin routes/controllers; add actions as needed.

- [ ] **F1-079: RSS-compatible News page for admins to post updates**
  - Type: feature | Priority: P2 | Risk: medium | Owner: mixed
  - Affected: database (news/announcements table or similar), admin UI, public News page, RSS feed route
  - News/announcements model and CRUD for admins; public News page and RSS feed so users can subscribe to updates.

- [ ] **F1-080: Feedback page for users to message site owner**
  - Type: feature | Priority: P2 | Risk: low | Owner: agent
  - Affected: routes, controller or Livewire component, feedback storage (table or mail), optional notifications
  - Form for users to send feedback/messages to site owner; store and/or email; no public display of messages.

- [ ] **F1-093: Fix WCAG contrast failures on predictions and leaderboard pages** _(found 2026-02-10 audit)_
  - Type: accessibility | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/predictions/show.blade.php, resources/views/leaderboard/index.blade.php
  - predictions/show.blade.php uses `text-zinc-500` on `bg-zinc-900` (~2:1 contrast ratio; WCAG AA requires 4.5:1). leaderboard/index.blade.php uses `text-gray-400` on white background (~2.5:1 ratio). Also `text-green-500` on dark backgrounds is borderline.
  - Fix: Replace low-contrast text with `text-zinc-300 dark:text-zinc-200` on dark backgrounds; use `text-zinc-600 dark:text-zinc-400` on light backgrounds.

- [ ] **F1-094: Migrate leaderboard views from daisyUI to Mary UI** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: medium | Owner: agent
  - Affected: resources/views/leaderboard/index.blade.php, resources/views/leaderboard/compare.blade.php
  - Both files use old `@extends('components.layouts.layout')` pattern (should be `<x-layouts.layout>`) and 45+ daisyUI classes: `.btn`, `.btn-primary`, `.btn-outline`, `.card`, `.card-body`, `.form-control`, `.select`, `.select-bordered`, `.table`, `.table-zebra`, `.badge`, `.badge-outline`, `.bg-base-100`.
  - Migrate to Mary UI components (`x-mary-button`, `x-mary-card`, `x-mary-table`, `x-mary-badge`, `x-mary-select`) and the new layout pattern.

- [ ] **F1-095: Add mobile responsive wrappers to data tables** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/standings/drivers.blade.php, standings/teams.blade.php, leaderboard/index.blade.php, leaderboard/compare.blade.php
  - All 4 files have `<table>` elements without an `overflow-x-auto` wrapper. Tables overflow horizontally on mobile screens (<640px), breaking the layout.
  - Wrap each table in `<div class="overflow-x-auto">`.

- [ ] **F1-096: Standardize color palette to zinc (remove gray usage)** _(found 2026-02-10 audit)_
  - Type: UI | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/standings/drivers.blade.php, standings/teams.blade.php, resources/views/leaderboard/index.blade.php
  - DESIGN_SYSTEM.md specifies zinc as the neutral palette, but these files use `bg-gray-100`, `text-gray-800`, `dark:bg-gray-900`, `text-gray-400` etc. Replace all `gray-*` with corresponding `zinc-*` classes.

- [ ] **F1-097: Fix predict/create dark mode visibility** _(found 2026-02-10 audit)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: resources/views/predict/create.blade.php (lines 181-199)
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
  - Affected: resources/views/livewire/auth/login.blade.php, resources/views/predict/create.blade.php, resources/views/components/prediction-form.blade.php
  - Multiple files use `focus:ring-indigo-500` or `focus:ring-blue-500` for focus indicators. DESIGN_SYSTEM.md specifies F1 brand red as primary. Update to `focus:ring-red-600 dark:focus:ring-red-500`.

- [ ] **F1-101: Remove dead duplicate leaderboard routes** _(found 2026-02-10 audit)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: routes/web.php (lines 87-95)
  - `/leaderboard/livewire` and `/leaderboard/user/{user}/livewire` are duplicate routes with Livewire wrappers. The non-Livewire versions (`leaderboard.index`, `leaderboard.user-stats`) are the primary routes linked in navigation. Remove the dead duplicates.

- [ ] **F1-102: Clean up RacesController empty CRUD methods** _(found 2026-02-10 audit)_
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: app/Http/Controllers/RacesController.php
  - `create()`, `store()`, `edit()`, `update()`, `destroy()` are empty stub methods (contain only `//` comment). No routes use them. Remove or implement.
  - Related: F1-061 (empty scaffold controllers).

- [x] **F1-103: Harden admin password default in config** _(done 2026-02-10)_
  - Type: security | Priority: P2 | Risk: medium | Owner: agent
  - Done: Removed password default from config. EnsureAdminUser and AdminSeeder now require explicit ADMIN_PASSWORD when creating new admins; they skip/fail gracefully when unset.

- [ ] **F1-104: Fix prediction request validation inconsistency** _(found 2026-02-10 audit)_
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Http/Requests/StorePredictionRequest.php, app/Http/Requests/UpdatePredictionRequest.php
  - `StorePredictionRequest` validates `prediction_data.superlatives.*` as `['nullable']` (accepts any type), while `UpdatePredictionRequest` validates as `['nullable', 'string']`. Rules should be consistent; both should validate as `['nullable', 'string']`.

- [x] **F1-105: Add is_admin to User model $hidden array** _(done 2026-02-10 with F1-065)_
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Done: Added is_admin to User::$hidden so admin status is not exposed in JSON serialization.
