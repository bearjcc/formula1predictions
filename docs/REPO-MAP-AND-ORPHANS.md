# Repo map and orphan/dead code audit

Single reference for how the app is wired (routes, views, Livewire) and which files are not attached to the main site or are dead/orphaned.

---

## 1. Route → handler → view map

### Public (no auth)

| Route | Handler | View / response |
|-------|---------|------------------|
| `GET /` | Closure | `home` |
| `GET /{year}/races` | Closure | `races` (contains `@livewire('races.races-list')`) |
| `GET /{year}/standings` | Closure | `standings` |
| `GET /{year}/standings/drivers` | Closure | `standings.drivers` |
| `GET /{year}/standings/constructors` | Closure | `standings.constructors` |
| `GET /{year}/standings/predictions` | Closure | `standings.predictions` (contains `<livewire:global-leaderboard>`) |
| `GET /{year}/standings/predictions/{username}` | Closure | `standings.predictions` |
| `GET /{year}/race/{id}` | Closure | `race` |
| `GET /countries` | `App\Livewire\Pages\CountriesIndex` | `livewire.pages.countries-index` |
| `GET /constructor/{slug}` | Closure | `constructor` |
| `GET /driver/{slug}` | Closure | `driver` |
| `GET /circuit/{slug}` | Closure | `circuit` |
| `GET /country/{slug}` | Closure | `country` |
| `GET /race/{slug}` | Closure | `race` (same view as year/race) |

### Local/testing only

| Route | Handler | View |
|-------|---------|------|
| `GET /components` | Closure | `components` |
| `GET /draggable-demo` | Closure | `draggable-demo` |
| `GET /api/f1/test` | RacesController::testApi | (JSON) |

### Auth required

| Route | Handler | View / response |
|-------|---------|------------------|
| `GET /dashboard` | DashboardController | `dashboard` |
| `GET /analytics` | `App\Livewire\Pages\Analytics` | `livewire.pages.analytics` |
| `GET /settings/profile` | Volt | `livewire.settings.profile` |
| `GET /settings/password` | Volt | `livewire.settings.password` |
| `GET /settings/appearance` | Volt | `livewire.settings.appearance` |
| `GET /notifications` | Volt | `livewire.pages.notifications.index` |
| `GET /predict/create` | Closure | `predictions.create-livewire` (contains `<livewire:predictions.prediction-form>`) |
| `GET /predictions/{id}/edit` | Closure | `predictions.edit-livewire` (contains `<livewire:predictions.prediction-form>`) |
| `GET /predictions` (resource index) | PredictionController::index | `predictions.index` |
| `GET /predictions/create` | PredictionController::create | **Redirect** to `predict.create` |
| `POST /predictions` | PredictionController::store | redirect |
| `GET /predictions/{id}` | PredictionController::show | `predictions.show` |
| `GET /predictions/{id}/edit` | **Overridden by closure above** | `predictions.edit-livewire` (controller `edit()` never used for GET) |
| `PUT/PATCH /predictions/{id}` | PredictionController::update | redirect |
| `DELETE /predictions/{id}` | PredictionController::destroy | redirect |

### Admin (auth + admin)

| Route | Handler | View |
|-------|---------|------|
| `GET /admin/dashboard` | AdminController::dashboard | `admin.dashboard` |
| `GET /admin/users` | AdminController::users | `admin.users` |
| `GET /admin/predictions` | AdminController::predictions | `admin.predictions` |
| `GET /admin/races` | AdminController::races | `admin.races` |
| `GET /admin/scoring` | AdminController::scoring | `admin.scoring` |
| `GET /admin/settings` | AdminController::settings | `admin.settings` |

### Leaderboard (auth)

| Route | Handler | View |
|-------|---------|------|
| `GET /leaderboard` | LeaderboardController::index | `leaderboard.index` |
| `GET /leaderboard/livewire` | Closure | `leaderboard.index-livewire` (contains `<livewire:global-leaderboard>`) |
| `GET /leaderboard/season/{season}` | LeaderboardController::season | `leaderboard.season` |
| `GET /leaderboard/race/{season}/{raceRound}` | LeaderboardController::race | `leaderboard.race` |
| `GET /leaderboard/compare` | LeaderboardController::compare | `leaderboard.compare` |
| `GET /leaderboard/user/{user}` | LeaderboardController::userStats | `leaderboard.user-stats` |
| `GET /leaderboard/user/{user}/livewire` | Closure | `leaderboard.user-stats-livewire` (contains `<livewire:user-profile-stats>`) |

### Auth routes (auth.php)

| Route | Handler | View (Volt) |
|-------|---------|-------------|
| `GET /login` | Volt | `livewire.auth.login` |
| `GET /register` | Volt | `livewire.auth.register` |
| `GET /forgot-password` | Volt | `livewire.auth.forgot-password` |
| `GET /reset-password/{token}` | Volt | `livewire.auth.reset-password` |
| `GET /verify-email` | Volt | `livewire.auth.verify-email` |
| `GET /confirm-password` | Volt | `livewire.auth.confirm-password` |
| `POST /logout` | `App\Livewire\Actions\Logout` | (redirect) |

### API (auth)

| Route | Handler |
|-------|---------|
| `GET /api/f1/races/{year}` | RacesController::index |
| `GET /api/f1/races/{year}/{round}` | RacesController::show |
| `DELETE /api/f1/cache/{year}` | RacesController::clearCache (admin) |

### Deferred (commented in web.php, F1-031)

- Checkout/Stripe routes → StripeCheckoutController
- Stripe webhook → StripeWebhookController

---

## 2. View inclusion / component usage

- **Layout**: Most pages use `<x-layouts.layout>` or `@extends('components.layouts.layout')`. Layout includes `partials.head` and embeds `<livewire:notifications.notification-dropdown />` when auth.
- **Settings**: Profile, password, appearance use `<x-settings.layout>` and `@include('partials.settings-heading')`. Profile embeds `<livewire:settings.delete-user-form />`.
- **Auth Volt views**: Use `<x-auth-header>`, `<x-auth-session-status>`, and layout `auth.simple` (via `layouts.auth`).
- **Races**: `races.blade.php` embeds `@livewire('races.races-list')`; races-list includes `livewire.races.partials.race-card`.
- **Predictions**: Create/edit use `predictions.create-livewire` and `predictions.edit-livewire`, which embed `<livewire:predictions.prediction-form>`. PredictionForm view embeds `predictions.draggable-driver-list` and `predictions.draggable-team-list`.
- **Analytics**: `livewire.pages.analytics` embeds charts: prediction-accuracy-chart, standings-chart, driver-consistency-chart, points-progression-chart.
- **Standings predictions**: `standings.predictions` embeds `<livewire:global-leaderboard>`.

---

## 3. Orphan / dead files

### Orphan views (no route and not included anywhere)

| File | Reason |
|------|--------|
| `resources/views/scoring.blade.php` | "How scoring works" content page; no route points to it. Admin uses `admin.scoring`, not this. |
| `resources/views/predictions/create.blade.php` | Old create wrapper; create flow uses `predict.create` → `predictions.create-livewire`. PredictionController::create redirects to `predict.create`, so this view is never rendered. |
| `resources/views/predictions/edit.blade.php` | Old edit wrapper; GET `predictions/{id}/edit` is overridden by a closure that returns `predictions.edit-livewire`. Controller::edit never runs for GET. |
| `resources/views/predict/create.blade.php` | Not used; `predict/create` route returns `predictions.create-livewire`, not this file. |
| `resources/views/predict/edit.blade.php` | No route `predict/edit` exists; edit is `predictions/{id}/edit` → `predictions.edit-livewire`. |
| `resources/views/components/prediction-form.blade.php` | Old Blade form component; all prediction create/edit use Livewire `predictions.prediction-form`, never `<x-prediction-form>`. |
| `resources/views/components/layouts/auth/card.blade.php` | Auth layout uses only `auth.simple`; card and split are never referenced. |
| `resources/views/components/layouts/auth/split.blade.php` | Same as above. |
| `resources/views/components/placeholder-pattern.blade.php` | Not referenced anywhere. |
| `resources/views/components/layouts/app.blade.php` | Wrapper around layout; no view or component uses `<x-layouts.app>`. |

### Deferred / commented (not orphan, intentionally off)

- `resources/views/livewire/season-supporter.blade.php`: Used in profile.blade.php but commented out; checkout routes deferred (F1-031).
- Stripe controllers and checkout routes: commented; re-enable with F1-031.

### Orphan controllers (never referenced in routes)

| Controller | Note |
|------------|------|
| `App\Http\Controllers\TeamsController` | Stub resource; team detail uses closure + `team` view. |
| `App\Http\Controllers\StandingsController` | Stub resource; standings use closures. |
| `App\Http\Controllers\DriversController` | Stub resource; driver detail uses closure + `driver` view. |
| `App\Http\Controllers\CountriesController` | Stub resource; countries use Livewire CountriesIndex. |
| `App\Http\Controllers\CircuitsController` | Stub resource; circuit detail uses closure + `circuit` view. |

`RacesController` is used only for API (index, show, clearCache, testApi). Its `store` and `update` methods (and thus `StoreRacesRequest` / `UpdateRacesRequest`) are never routed and are dead code.

### Orphan form requests (only used by orphan controllers or dead methods)

| Request | Used only by |
|---------|----------------|
| StoreTeamsRequest, UpdateTeamsRequest | TeamsController (orphan) |
| StoreStandingsRequest, UpdateStandingsRequest | StandingsController (orphan) |
| StoreDriversRequest, UpdateDriversRequest | DriversController (orphan) |
| StoreCountriesRequest, UpdateCountriesRequest | CountriesController (orphan) |
| StoreCircuitsRequest, UpdateCircuitsRequest | CircuitsController (orphan) |
| StoreRacesRequest, UpdateRacesRequest | RacesController::store/update (not routed) |

---

## 4. Summary

- **Attached**: All routes in `web.php` and `auth.php` resolve to existing views or redirects; Livewire/Volt components used in those views are attached.
- **Cleanup (2026-02-18):** Orphan views, components, stub controllers, and form requests from sections 3 were removed. Public route `GET /scoring` was added and linked in the sidebar ("How scoring works"). Create/edit prediction pages use only create-livewire and edit-livewire (titles merged in).
- **Deferred**: Stripe/checkout routes and Season Supporter UI are commented by design (F1-031).

See git history and TODO.md (F1-061, F1-107, F1-108, F1-109, F1-102) for the cleanup tasks completed.
