# Formula1Predictions — Tip-to-Tail Audit Report

## Executive Summary (2 Sentences)

**Current Status:** The platform is MVP-ready with core prediction, scoring, leaderboard, analytics, and notification flows implemented and covered by tests; all TODO items F1-000 through F1-012 are done. Full test runs are slow due to F1 API integration tests; otherwise the product is on track for the 2026-02-20 launch target.

---

## Feature Inventory

| Domain            | Features                                                                                   | Status              |
| ----------------- | ------------------------------------------------------------------------------------------ | ------------------- |
| **Auth**          | Login, register, password reset, email verification                                        | Working, tested     |
| **Predictions**   | Create/edit race, preseason, midseason, sprint; Livewire form; draggable driver/team lists | Working, tested     |
| **Scoring**       | ScoringService (DNS/DSQ edge cases); admin score/lock/unlock; queue job; manual override   | Working, tested     |
| **Races**         | Year-based races list; status/search filters; F1 API fetch; error handling                 | Working, tested     |
| **Standings**     | Drivers, teams, predictions by season                                                      | Working             |
| **Leaderboard**   | Season, race, user-stats views                                                             | Working             |
| **Analytics**     | Dashboard; ChartDataService (progression, accuracy, luck/variance); 4 chart components     | Working, tested     |
| **Notifications** | PredictionScored, RaceResults, deadlines; dropdown; real-time broadcast                    | Working, tested     |
| **Admin**         | Dashboard, users, predictions, races, scoring, settings; score/lock/override/bulk          | Working, tested     |
| **Settings**      | Profile, password, appearance (Volt)                                                       | Working, tested     |
| **Legacy Import** | Phase 1 markdown seeder; `legacy:import-historical-predictions` command                    | Done, tested |

---

## Test Coverage Matrix

| Area                     | Test File(s)                                             | Status                     |
| ------------------------ | -------------------------------------------------------- | -------------------------- |
| Admin controller         | AdminControllerTest                                      | PASS (22 cases)            |
| Scoring                  | ScoringServiceTest                                       | PASS (20 cases)            |
| ScoreRacePredictionsJob  | ScoreRacePredictionsJobTest                              | PASS                       |
| Prediction controller    | PredictionControllerTest                                 | PASS                       |
| DraggableTeamList        | DraggableTeamListTest                                    | PASS                       |
| Console commands         | ConsoleCommandsTest                                      | PASS                       |
| Predictions              | LivewirePredictionFormTest, PredictionFormValidationTest | PASS                       |
| F1 API                   | F1ApiTest, RacesPageTest                                 | PASS (F1ApiTest slow ~48s) |
| Charts                   | ChartDataServiceTest, DataVisualizationTest              | PASS                       |
| Notifications            | NotificationTest, RealTimeNotificationTest               | PASS                       |
| Auth                     | AuthenticationTest, RegistrationTest, etc.               | PASS                       |
| Import                   | HistoricalDataImportTest, SimpleHistoricalDataTest       | PASS                       |
| Routes, views, models    | RoutesTest, ViewsTest, ModelRelationshipsTest            | PASS                       |
| Slug validation          | SlugValidationTest                                       | PASS                       |

---

## Known Issues

1. **Test suite speed** — F1ApiTest optimized (500 test uses single-round call). Full run may still be slow due to RoutesTest hitting /year/races without mocking F1 API. Run targeted tests for fast feedback.

---

## TODO Backlog Snapshot

- **Done:** F1-000 through F1-012 (MVP scope, scoring, validation, analytics, notifications, sprint mode, luck/variance, backtest harness, legacy import Phase 1, social/head-to-head comparison)

---

## Screenshots (audit features)

To generate screenshots of each feature listed in the inventory, run (with the app served by Herd at `http://formula1predictions.test`):

```bash
npm run audit-screenshots
```

Output is written to the `screenshots/` directory (numbered by feature). Uses `test@example.com` / `password` for auth-required pages. To include admin pages, set `ADMIN_EMAIL` and `ADMIN_PASSWORD` (e.g. for a user with role `admin`).

---

## Files of Record

- [AGENTS_PRD.md](AGENTS_PRD.md) — Product spec, domains, milestones
- [TODO.md](TODO.md) — Backlog with acceptance criteria
- [AGENTS.md](AGENTS.md) — Agent commands and recent completions
