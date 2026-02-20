# Formula 1 Predictions

A Laravel 12 web app for F1 race predictions: users predict finishing order and optional extras, get scored automatically, and compete on leaderboards. Replaces the 2022–2024 spreadsheet with auth, persistence, and automated scoring.

**Stack:** PHP 8.4.5, Laravel 12, Livewire 3, Volt, Mary UI, Tailwind v4, Pest v4. Served locally via [Laravel Herd](https://herd.laravel.com) at `https://formula1predictions.test` (or your project kebab-case name).

---

## Quick links

- [Installation](#installation)
- [Scoring (canonical)](#scoring)
- [Project layout](#project-layout)
- [Testing](#testing)
- [Docs](#docs)

---

## Installation

```bash
git clone <repo> && cd formula1predictions
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build
```

With Herd, the site is available at `https://formula1predictions.test`. Otherwise: `php artisan serve`.

---

## Admin Setup

### Local Development

Register a user account at `/register`, then promote to admin:
```bash
php artisan app:promote-admin you@example.com
```

Or set `ADMIN_EMAIL` in `.env` and run:
```bash
php artisan app:promote-admin
```

### Railway / Production Deployment

Set these environment variables in Railway:
```
ADMIN_EMAIL=your-admin@example.com
ADMIN_PASSWORD=your-secure-password-here
ADMIN_NAME=Your Name
```

The custom `start-container.sh` (used by Railpack on deploy) runs `app:ensure-admin-user` at startup, which creates or updates the admin user from these variables. **Important:** Change your password after first login.

### Demo bots (testing / demonstration)

To seed the site with algorithm bots that submit predictions for every race:

```bash
php artisan bots:seed --only=last,season,random
```

- **LastBot** — Predicts each race as the same order as the previous race; first race of the year uses the last race of the previous year.
- **SeasonBot** — Predicts current championship order (before each round); first race uses previous season’s final standings.
- **RandomBot** — Random driver order per race.

To run all bot seeders (including circuit-, previous-year-, and smart-weighted bots): `php artisan bots:seed`.

---

## Scoring

Canonical scoring rules (implement in `ScoringService`; tests in `tests/Feature/ScoringServiceTest.php`).

### Race (full race)

- **Position accuracy** (predicted vs actual):  
  0 → 25 | 1 → 18 | 2 → 15 | 3 → 12 | 4 → 10 | 5 → 8 | 6 → 6 | 7 → 4 | 8 → 2 | 9 → 1  
  10 → 0 | 11 → -1 | 12 → -2 | 13 → -4 | 14 → -6 | 15 → -8 | 16 → -10 | 17 → -12 | 18 → -15 | 19 → -18 | 20+ → -25  
- **Fastest lap:** +10 if predicted driver matches actual.
- **DNF wager:** +10 per correct DNF prediction, -10 per incorrect (optional predictions).
- **Perfect prediction:** +50 if every predicted driver is in the correct position (all diffs 0).
- **Half points:** When the FIA awards half points for a shortened race, halve the race score (rounded).
- **Predictions optional:** Users may predict any subset of positions (e.g. only 1st, 8th, 20th); score only predicted positions.
- **Missing drivers (DNP/DNQ/DNS/DSQ/EXCLUDED):** Omit from processed results; that driver’s prediction contributes 0. DNF drivers keep a position and are scored by position.

### Sprint

- **Position:** 0 → 8 | 1 → 7 | 2 → 6 | 3 → 5 | 4 → 4 | 5 → 3 | 6 → 2 | 7 → 1 | 8+ → 0 (no negative).
- **Fastest lap:** +5.
- **Perfect bonus:** +15 when top 8 predicted positions are all correct.

### Preseason / Midseason

- **Driver championship order** and **team (constructor) order** scored against final season standings.
- **Position diff:** Same table as race (0 → 25, 1 → 18, …, 9 → 1, 10 → 0, 11+ → negative).
- **Perfect bonus:** +50 when every predicted driver and team is in the correct position.
- Scored via `php artisan predictions:score-championship {season} --type=preseason|midseason`.

### Result processing

- **FINISHED / DNF:** Driver has a position; score by position.
- **DNS / DSQ / EXCLUDED:** Omit from processed results; prediction for that driver = 0.
- Admins can override scores. Cancelled races: predictions set to `cancelled`, score 0.

---

## Project layout

| Path | Purpose |
|------|---------|
| `app/Models/` | Prediction, Races, Drivers, Teams, Standings |
| `app/Services/` | ScoringService, F1ApiService, ChartDataService, NotificationService |
| `app/Livewire/` | PredictionForm, DraggableDriverList, RacesList, Charts/* |
| `resources/views/` | Blade + Volt views |
| `config/f1.php` | F1/scoring config |
| `tests/Feature/`, `tests/Browser/` | Pest tests |

Use `F1ApiService` for all external API calls (no raw HTTP). Treat `ScoringService` as the single source of truth for scoring.

---

## Testing

```bash
php artisan test
php artisan test tests/Feature/ScoringServiceTest.php
php artisan test --filter=testName
```

If `php artisan test` times out (e.g. on some Windows setups), run `.\scripts\test-batches.ps1` (PowerShell) or `./scripts/test-batches.sh` (Unix/CI), or use the two-batch commands in [AGENTS.md](AGENTS.md) Commands.

Coverage (optional): `composer run test:coverage` (requires pcov).

### CI

GitHub Actions runs on push and pull request to `main` and `master` (see [.github/workflows/ci.yml](.github/workflows/ci.yml)): two-batch tests via `scripts/test-batches.sh`, then `npm run build`. Optional: `vendor/bin/pint --dirty --test` (continue-on-error).

---

## Railway deployment

Deployment uses [Railpack](https://railpack.com/) (PHP/FrankenPHP). A custom `start-container.sh` in the repo root runs migrate, `config:cache`, `app:ensure-admin-user`, `f1:ensure-season-data`, then starts the server.

- **Web:** Railpack runs the app via the custom start script; admin user creation and season data preload run at startup.
- **Queue worker:** Run as a **separate Railway service** with start command `php artisan queue:work`. Do not run the worker in the same process as the web app.
- **Cron / scheduler:** Either (1) a dedicated service running `./railway/run-cron.sh` (loop: `schedule:run` every 60s), or (2) Railway’s cron feature pointing at a URL or command that runs the scheduler. See [railway/run-cron.sh](railway/run-cron.sh).
- **Email:** For production, configure a mail driver and `MAIL_FROM_ADDRESS` (and `MAIL_FROM_NAME`) so verification and other transactional emails are sent; otherwise they are logged (`MAIL_MAILER=log`).

### Supervisor (self-hosted queue worker)

For self-hosted or VPS deployments using [Supervisor](https://supervisord.org/) to run the Laravel queue worker:

- **Config:** [deployment/supervisord.conf](deployment/supervisord.conf) defines a `laravel-worker` program running `php artisan queue:work` (database queue; one process, autorestart, logs under `deployment/logs/`).
- **Place:** Copy the file to your server (e.g. `/etc/supervisor/conf.d/formula1predictions.conf`) and set `directory=` (and optional `user=`) to your app root, or run `supervisord -c deployment/supervisord.conf` from the app root after editing paths. Create `deployment/logs/` so Supervisor can write logs.
- **Run:** `supervisorctl reread && supervisorctl update && supervisorctl start laravel-worker:*` (or `supervisord -c deployment/supervisord.conf` if using the file as the main config).

---

## Docs

- **[TODO.md](TODO.md)** — Backlog and task status (Now / Next / Later).
- **[AGENTS.md](AGENTS.md)** — AI/agent instructions: commands, conventions, guardrails, handoff.
- **[DESIGN_SYSTEM.md](DESIGN_SYSTEM.md)** — UI, colours, components, accessibility.

---

## License

Proprietary. All rights reserved.
