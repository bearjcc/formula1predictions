# Design: Prediction Standings Page (GlobalLeaderboard)

## Summary

The prediction standings page at `/{year}/standings/predictions` is a Blade layout that embeds `App\Livewire\GlobalLeaderboard`. The Livewire component owns filter state (season, type, sort), loads real prediction standings from the DB via Eloquent (using existing `Prediction`/`User` data already scored by `ScoringService`), and renders a single Livewire view with no separate Blade partials. Scoring logic remains entirely in `ScoringService`; no changes to scoring rules.

---

## URL patterns and query parameters

| URL | Route name | Purpose |
|-----|------------|--------|
| `GET /{year}/standings/predictions` | `standings.predictions` | Main prediction leaderboard for that season. |
| `GET /{year}/standings/predictions/{username}` | `standings.predictions.user` | Same view with `username` in context (e.g. future “focus user” or deep link; currently not used in Blade). |

- **Path:** `{year}` is the season (e.g. `2026`). It is the primary source of truth for “which season this page is for.” The route closure passes `year` to the view; the view passes it to the layout (title/subtitle) and to the Livewire component as the initial `season`.
- **Query (Livewire `#[Url]`):** The component keeps these in sync with the URL for shareability and back/forward:
  - `season` – defaults from route `year` when the page is first loaded via `<livewire:global-leaderboard :season="$year" />`; can diverge if the user changes the Season dropdown (see “Season vs path” below).
  - `type` – filter by prediction type: `all`, `race`, `preseason` (and optionally `midseason` if added later).
  - `sortBy` – sort key: `total_score`, `avg_score`, `avg_accuracy`, `predictions_count`.
  - `page` – pagination (e.g. `page=2`).

**Recommendation (optional improvement):** When the user changes the Season dropdown, consider navigating to `/{newSeason}/standings/predictions?type=...&sortBy=...` (e.g. via `$this->redirect(route('standings.predictions', ['year' => $this->season], absolute: false))`) so the path stays the single source of truth and links stay consistent. Today, changing season only updates the query param `season`; the path remains the original `year`.

---

## What lives in Livewire (`GlobalLeaderboard`)

- **Properties (state):**
  - `season` (int), `type` (string), `sortBy` (string), `page` (int) – all `#[Url]` for query sync.
  - `leaderboard` (array) – list of user rows (rank, name, initials, total_score, avg_score, avg_accuracy, predictions_count, perfect_predictions, is_supporter, badges).
  - `proStats` (array) – aggregate stats (total_users, avg_total_score, median_score, avg_accuracy, perfect_predictions, supporters, etc.).
  - `availableSeasons` (array) – seasons to show in the Season dropdown (from DB + current + route year).
  - `perPage`, `chartId` – presentation/config.

- **Actions:**
  - `mount()` – set default season from input (route-supplied `season`), load `availableSeasons`, then `loadLeaderboard()`.
  - `updatedSeason()`, `updatedType()`, `updatedSortBy()` – reset page and reload or re-sort.
  - `resetPage()` – set `page = 1`.
  - Private: `loadAvailableSeasons()`, `loadLeaderboard()`, `sortLeaderboard()`, `loadProStats()`, `calculateMedian()`.

- **Rendering:** `render()` builds a `LengthAwarePaginator` from `leaderboard` and passes `paginatedLeaderboard` to the view. No scoring logic here; it only reads `Prediction` columns `score` and `accuracy` (set by `ScoringService`).

---

## Blade structure

- **Page:** `resources/views/standings/predictions.blade.php`  
  - Uses layout `x-layouts.layout` with title and header subtitle using `$year`.  
  - Single Livewire embed: `<livewire:global-leaderboard :season="$year" />`.  
  - No other Blade partials; the rest of the content is inside the Livewire component.

- **Component view:** `resources/views/livewire/global-leaderboard.blade.php`  
  - One file: filters card (Season, Type, Sort By), optional Pro Stats card, leaderboard table (with pagination) and empty state.  
  - No `@include` partials; optional future refactor could extract e.g. `livewire/partials/leaderboard-filters.blade.php` and `livewire/partials/leaderboard-table.blade.php` for clarity only.

---

## How filters map to database queries

- **Season:** All queries restrict to `season = $this->season` (from route on first load, then from component state/URL).
- **Type:**  
  - `all` – no extra `type` filter.  
  - `race` – `where('type', 'race')`.  
  - `preseason` – `where('type', 'preseason')`.  
  (Optional: add `midseason` to the Type dropdown and use `where('type', 'midseason')` for consistency with `Prediction` and `ScoringService`.)

- **Scored data only:** Leaderboard aggregates use predictions with `status = 'scored'` (and `where('season', $this->season)` plus optional `type`). Counts for “number of predictions” use the same season/type filters but may include non-scored for display; in the current implementation, `withCount(['predictions' => ...])` is for presence and “predictions_count” and does not require status; sums/averages use `where('status', 'scored')`.

- **Sort:** Applied in PHP on the already-loaded `leaderboard` array (`sortLeaderboard()`), keyed by `sortBy`: `total_score`, `avg_score`, `avg_accuracy`, `predictions_count` (all descending). Ranks are then assigned by array index.

- **Pagination:** In-memory: `array_slice($this->leaderboard, ...)` in `render()` with `LengthAwarePaginator`; no DB-level limit/offset. Acceptable for moderate list sizes; if the number of users with predictions grows large, consider moving sort/pagination into a single Eloquent/query builder so only one page is loaded.

---

## Scoring and boundaries

- **Scoring:** All scoring and score/accuracy writes are in `App\Services\ScoringService`. This design does not change any scoring rules. The leaderboard only reads `Prediction.score` and `Prediction.accuracy` (and `status = 'scored'`). Perfect-prediction count uses a simple threshold (e.g. `score >= 25`) for display only; that is not part of the official scoring formula.
- **AGENTS.md:** No new migrations, no auth/authorization changes, no changes to `ScoringService`. Safe area: UI and read-only use of existing data.

---

## Optional follow-ups

1. **Route validation:** Ensure `{year}` is validated (e.g. integer, min/max) in the route or middleware so invalid years return 404 or 400.
2. **Season in URL:** Prefer path as source of truth: on season change, navigate to `/{season}/standings/predictions?...` instead of only updating `?season=`.
3. **Type filter:** Add `midseason` to the Type dropdown and to the component’s filter logic so it matches `Prediction` types (race, sprint, preseason, midseason); optionally “Race” could include both race and sprint if desired.
4. **User deep link:** If `standings.predictions.user` is used, pass `$username` into the Livewire component (e.g. optional prop) and scroll/highlight that user or load a small “your position” card.

---

## Files involved

| File | Responsibility |
|------|----------------|
| `routes/web.php` | Define `/{year}/standings/predictions` and `.../predictions/{username}`; return view with `year` (and optional `username`). |
| `resources/views/standings/predictions.blade.php` | Layout wrapper; pass `$year` to layout and to `<livewire:global-leaderboard :season="$year" />`. |
| `app/Livewire/GlobalLeaderboard.php` | State (season, type, sortBy, page, leaderboard, proStats, availableSeasons); load and sort data from User/Prediction; no scoring logic. |
| `resources/views/livewire/global-leaderboard.blade.php` | Filters, Pro Stats, table, pagination, empty state. |
| `App\Models\User` | Relationships and scopes used by GlobalLeaderboard (e.g. `predictions` with filters). |
| `App\Models\Prediction` | `score`, `accuracy`, `status`, `type`, `season` read by leaderboard queries. |
| `App\Services\ScoringService` | Sole authority for scoring; unchanged by this design. |
