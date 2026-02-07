## Formula1Predictions TODO Backlog

- **schema_version**: 1
- **purpose**: Authoritative, machine-parseable backlog for AI agents (and collaborators) working on the Formula1Predictions Laravel 12 + Livewire app.
- **references**:
  - [AGENTS.md](AGENTS.md) (commands, conventions, handoff)
  - [README.md](README.md) (scoring rules, project layout)
- **status_values**: `todo` | `in_progress` | `blocked` | `done` | `cancelled`
- **owners**: This backlog is primarily for AI-executable work (**owner** `agent` or `mixed`), with humans free to add or adjust tasks.
- **agent_workflow**: Agents MUST remove completed (done) items from this file and ADD new items they identify as needing work but did not complete. This enables handoff between agents.

---

### Task Schema (example)

```markdown
- **id**: F1-001
  - **title**: Improve race list filtering
  - **type**: feature | bug | chore | experiment | docs
  - **status**: todo | in_progress | blocked | done | cancelled
  - **priority**: P0 | P1 | P2 | P3
  - **risk_level**: low | medium | high
  - **owner**: agent | human | mixed
  - **affected_areas**:
    - app/Livewire/Races/RacesList.php
    - resources/views/races.blade.php
  - **description**: Short paragraph describing intent and constraints.
  - **acceptance_criteria**:
    - Users can filter races by season and status.
    - Filters persist across pagination.
  - **dependencies**:
    - F1-000 (update race model fields)
  - **test_expectations**:
    - Feature tests in tests/Feature/RacesPageTest.php
  - **notes**:
    - Link to GitHub issue, design doc, or discussion if applicable.
```

---

### Now

Short-horizon, high-value tasks that are ready for agents to pick up immediately. **2026 MVP deadline: 2026-02-20.**

- **id**: F1-022
  - **title**: Implement DNF wager prediction system
  - **type**: feature
  - **status**: done
  - **priority**: P1
  - **risk_level**: medium
  - **owner**: mixed
  - **affected_areas**:
    - app/Models/Prediction.php
    - app/Services/ScoringService.php
    - app/Livewire/Predictions/PredictionForm.php
    - resources/views/livewire/predictions/*
  - **description**: Users can predict which drivers will DNF. This is a wager: +10 points per correct DNF call, -10 per incorrect. DNF predictions are optional. The `prediction_data` JSON needs a `dnf_predictions` array of driver IDs. ScoringService must compare against actual DNF statuses in results.
  - **acceptance_criteria**:
    - Users can optionally select drivers they predict will DNF.
    - +10 per correctly predicted DNF, -10 per incorrectly predicted DNF.
    - DNF predictions stored in `prediction_data.dnf_predictions`.
    - Scoring handles DNF wagers independently of position scoring.
  - **test_expectations**:
    - New tests in tests/Feature/ScoringServiceTest.php for DNF wager scoring.
  - **notes**:
    - Completed 2026-02-08. PredictionForm has optional DNF wager section (race only); Store/UpdatePredictionRequest validate dnf_predictions.

- **id**: F1-023
  - **title**: Implement half-points for shortened races
  - **type**: feature
  - **status**: done
  - **priority**: P1
  - **risk_level**: medium
  - **owner**: mixed
  - **affected_areas**:
    - app/Models/Races.php
    - app/Services/ScoringService.php
  - **description**: When the FIA awards half points for a race (due to being too short), our scoring should also award half points. Requires a flag on the race (e.g. `half_points` boolean) and ScoringService to halve the calculated score when the flag is set.
  - **acceptance_criteria**:
    - Races model has a `half_points` flag (migration if needed).
    - ScoringService halves the final score (rounded) when `half_points` is true.
    - Admin can toggle the flag.
  - **test_expectations**:
    - Tests in tests/Feature/ScoringServiceTest.php for half-points scenarios.

- **id**: F1-025
  - **title**: Auto-lock predictions before qualifying
  - **type**: feature
  - **status**: done
  - **priority**: P1
  - **risk_level**: medium
  - **owner**: agent
  - **affected_areas**:
    - app/Models/Races.php
    - app/Models/Prediction.php
    - app/Console/Commands/*
    - app/Console/Kernel.php (or scheduler)
  - **description**: Race predictions must close 1 hour before qualifying start. Sprint predictions close 1 hour before sprint qualifying. This requires: (1) qualifying/sprint times stored on Race model, (2) `allowsPredictions()` / `allowsSprintPredictions()` checking against current time, (3) a scheduled command to auto-lock submitted predictions past the deadline.
  - **acceptance_criteria**:
    - Predictions cannot be submitted or edited within 1 hour of qualifying.
    - A scheduled command locks all submitted predictions past their deadline.
    - UI shows countdown/deadline to users.
  - **test_expectations**:
    - Tests for time-based prediction locking.
  - **notes**:
    - Completed 2026-02-08. Migration qualifying_start/sprint_qualifying_start; Races.allowsPredictions/allowsSprintPredictions use 1h-before-qualifying; LockPredictionsPastDeadline command + schedule; SyncRaceSchedule command; UI deadline on prediction form; AutoLockPredictionsTest.

---

### Next

Medium-horizon improvements that should be tackled soon.

- **id**: F1-026
  - **title**: 2026 season data pipeline
  - **type**: feature
  - **status**: done
  - **priority**: P1
  - **risk_level**: medium
  - **owner**: mixed
  - **affected_areas**:
    - app/Services/F1ApiService.php
    - app/Console/Commands/*
    - database/seeders/*
  - **description**: Fetch and store the 2026 race calendar, drivers, and teams from f1api.dev. Need a command or seeder to populate the database with the upcoming season's data so users can start making predictions.
  - **acceptance_criteria**:
    - 2026 races, drivers, and teams loaded into the database.
    - Race dates and qualifying times available for prediction deadlines.
  - **notes**:
    - Completed 2026-02-08. F1ApiService: syncSeasonRacesFromSchedule (create/update races from schedule API), fetchDriversChampionship/fetchConstructorsChampionship, syncTeamsForSeason, syncDriversForSeason. f1:sync-season {year} command syncs races, teams, drivers (options: --races-only, --drivers-only, --teams-only). syncScheduleToRaces now creates missing races from schedule. getAvailableYears includes 2026. When f1api.dev has 2026 data, run `php artisan f1:sync-season 2026`.

- **id**: F1-027
  - **title**: Dashboard content and user experience
  - **type**: feature
  - **status**: done
  - **priority**: P2
  - **risk_level**: low
  - **owner**: agent
  - **affected_areas**:
    - app/Http/Controllers/DashboardController.php
    - resources/views/dashboard.blade.php
  - **description**: Dashboard should show: upcoming race with prediction deadline countdown, user's recent predictions and scores, current leaderboard position, quick links to create predictions.
  - **notes**:
    - Completed 2026-02-08. DashboardController loads real stats, upcoming races with deadline, leaderboard top 5, recent predictions; view uses dynamic data and proper links.

- **id**: F1-028
  - **title**: F1-branded UI styling
  - **type**: chore
  - **status**: todo
  - **priority**: P2
  - **risk_level**: low
  - **owner**: mixed
  - **affected_areas**:
    - resources/css/*
    - resources/views/**
    - tailwind.config.js
  - **description**: Style the site to fit with f1.com, f1tv.com, and f1api.dev aesthetic. Dark theme, F1 red accents, racing-inspired typography. Ensure all assets used are fair use or open source.

- **id**: F1-029
  - **title**: Predictions should be fully optional (partial predictions)
  - **type**: bug
  - **status**: done
  - **priority**: P2
  - **risk_level**: medium
  - **owner**: agent
  - **affected_areas**:
    - app/Services/ScoringService.php
    - app/Livewire/Predictions/PredictionForm.php
    - app/Http/Requests/*
  - **description**: Per the spec, all predictions are optional — users can predict only positions 1, 8, and 20 if they want. Current validation requires exactly 20 drivers for race predictions and exactly 10 teams for preseason. Validation should allow partial predictions while scoring only the positions the user predicted.
  - **notes**:
    - Completed 2026-02-08. Store/UpdatePredictionRequest and Livewire form allow driver_order 1–20, team_order 1–10, driver_championship 1–20. ScoringService already scored only predicted positions; perfect bonus +50 when all predicted positions correct (not only when 20 predicted). Livewire validates driverOrder/driverChampionship with id or driver_id; teamOrder/driverChampionship allow empty when type is race/sprint. Tests updated; partial prediction scoring test added.

---

### Later / Ideas

Longer-horizon ideas and exploratory improvements.

- **id**: F1-019
  - **title**: Phase 2 legacy import (CSV/JSON external sources)
  - **type**: feature
  - **status**: todo
  - **priority**: P3
  - **risk_level**: high
  - **owner**: mixed
  - **affected_areas**:
    - database/seeders/*
    - app/Console/Commands/*
  - **description**: Extend legacy import beyond Phase 1 markdown. Support CSV/JSON or external DB dumps when representative data and human-approved migrations are available.
  - **dependencies**:
    - F1-006A (Phase 1 done)
  - **notes**:
    - Deferred until representative data and human approval. Per F1-006A completed_summary.

- **id**: F1-030
  - **title**: Bot accounts and algorithm-based predictions
  - **type**: feature
  - **status**: todo
  - **priority**: P3
  - **risk_level**: low
  - **owner**: mixed
  - **affected_areas**:
    - database/seeders/*
    - app/Console/Commands/*
  - **description**: Expand bot system beyond LastRaceBot. Add bots like "ChampionshipOrderBot" (always predicts current championship standings), "RandomBot", etc. Bots should be applicable retroactively to all seasons. Previously called "dummies" in the spreadsheet era.

- **id**: F1-031
  - **title**: Monetization strategy (premium features)
  - **type**: feature
  - **status**: todo
  - **priority**: P3
  - **risk_level**: high
  - **owner**: human
  - **description**: Non-gambling, non-ad monetization to recover costs. Ideas: premium stats/analytics, badges, special abilities. Free tier must allow full gameplay. No 3rd party ads. Minimal self-promotion (lock icon + "become a member" button). Needs cost-per-user and revenue-per-user analysis.
  - **notes**:
    - Monetization and payments for cost recovery are in scope and allowed; only gambling/real-money betting is forbidden. Implementing payment/billing code requires explicit human approval.

- **id**: F1-032
  - **title**: Preseason and midseason prediction games
  - **type**: feature
  - **status**: todo
  - **priority**: P3
  - **risk_level**: low
  - **owner**: mixed
  - **description**: Review previous spreadsheets for ideas and inspiration for preseason/midseason mini-games (e.g., predict championship order, team performance, superlatives). Design and implement scoring for these prediction types.
