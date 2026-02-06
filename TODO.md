# TODO.md - Formula 1 Prediction Game

## Phase 1: Local Setup & Foundation 🏗️
- [ ] Create `.env` from `.env.example` or template.
- [ ] Run `composer install`.
- [ ] Run `npm install` and `npm run build`.
- [ ] Run `php artisan migrate`.
- [ ] Seed the database with initial F1 data: `php artisan db:seed`.
- [ ] Verify basic site navigation and authentication.

## Phase 2: Core Prediction Logic 🏎️
- [ ] Implement/Test `DraggableDriverList` Livewire component.
- [ ] Implement/Test `PredictionForm` saving logic.
- [ ] Add "Deadline" enforcement logic (locking predictions before race start).
- [ ] Finalize `ScoringService` to handle `DNF`/`DNS` cases correctly.
- [ ] Create admin command to fetch results and trigger scoring.

## Phase 3: Leaderboard & Social 🏆
- [ ] Build global leaderboard view.
- [ ] Implement Mini-Leagues / Private Groups.
- [ ] Add User Statistics (Accuracy, Points Progression).
- [ ] Implement real-time notifications for results and score updates.

## Current Audit Notes (2026-02-06)
- **Status**: Structural bones are in place (Controllers, Models, Migrations exist).
- **Broken**: Environment is not initialized. Services (F1 API) likely need API keys.
- **Goal**: Human-playable state.
