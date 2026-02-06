# TODO.md - Formula 1 Prediction Game

## Phase 1: Local Setup & Foundation 🏗️
- [x] Create `.env` from `.env.example` or template.
- [x] Run `composer install`.
- [x] Run `npm install` and `npm run build`.
- [x] Run `php artisan migrate`.
- [x] Seed the database with initial F1 data: `php artisan db:seed`.
- [x] Verify basic site navigation and authentication.
- [x] Expose dev version at /f1.

## Goal
Human-playable state with a "Gabe Newell" quality bar: polished, valuable, and community-centric.

## Phase 2: Core Prediction Logic 🏎️
- [x] Fix 404 Route errors in `/f1` UI (Fixed hardcoded links, menu logic).
- [x] Implement/Test `DraggableDriverList` Livewire component (UX focus with haptic-ready UI).
- [x] Implement/Test `PredictionForm` saving logic (Linked with Draggable UI).
- [x] Add "Deadline" enforcement logic (Automatic locking based on Race model timing).
- [x] Finalize `ScoringService` to handle `DNF`/`DNS` cases correctly.
- [x] Create admin command to fetch results and trigger scoring.

## Phase 3: Leaderboard & Social 🏆
- [x] Global leaderboard with "Pro Stats" visualization.
  - [x] Created `GlobalLeaderboard` Livewire component
  - [x] Added Pro Stats summary (active users, avg scores, community accuracy, perfect predictions)
  - [x] Enhanced leaderboard table with badges, supporter highlighting, accuracy progress bars
  - [x] Season/type filtering and sorting by multiple metrics
  - [x] Route: `/leaderboard/livewire` (new Livewire version)
- [ ] Implement Mini-Leagues / Private Groups.
- [x] Add User Statistics (Accuracy, Points Progression).
  - [x] Created `UserProfileStats` Livewire component
  - [x] Added detailed statistics methods to User model
  - [x] Points progression chart (cumulative and per-race)
  - [x] Accuracy over time chart
  - [x] Race-by-race performance table
  - [x] Position heatmap data method (for future visualization)
  - [x] Accuracy trends calculation with moving averages
  - [x] Route: `/leaderboard/user/{user}/livewire` (new Livewire version)
- [x] Implement "Supporter" badge systems (One-time support model).
  - [x] Migration for badge fields (is_season_supporter, badges JSON)
  - [x] Added badge management methods to User model
  - [x] Stripe Integration: Installed Laravel Cashier
  - [x] Created `StripeCheckoutController` for one-time payments
  - [x] Added `StripeWebhookController` for payment event handling
  - [x] Created `SeasonSupporter` Livewire component with Stripe checkout integration
  - [x] Integrated supporter component into profile settings
  - [x] Badge display on leaderboard and user profiles
- [x] Production Preparation
  - [x] Drafted comprehensive production deployment plan (`DEPLOYMENT.md`)
  - [x] Updated `.env.example` with Stripe configuration
  - [x] Updated `PRD.json` to reflect Stripe as a core requirement (v1.1.0)
- [ ] Implement real-time notifications for results and score updates.

## Current Audit Notes (2026-02-06)
- **Status**: Phase 3 (Iteration 1) Complete. Leaderboard, user stats, and supporter badge system implemented.
- **Next**: Mini-Leagues/Private Groups, real-time notifications, polish UI.
- **Goal**: Human-playable state with enhanced competition features.

### Phase 3 Implementation Details (2026-02-06)

**Created Components:**
- `App\Livewire\GlobalLeaderboard` - Enhanced leaderboard with Pro Stats
- `App\Livewire\UserProfileStats` - Detailed user statistics with charts
- `App\Livewire\SeasonSupporter` - Badge/supporter management component

**Database Changes:**
- Migration `2025_02_06_000001` - Added badge fields to users table
  - `is_season_supporter` (boolean)
  - `supporter_since` (timestamp)
  - `badges` (JSON array)
  - `stats_cache` (JSON array) - for future caching optimization

**Model Enhancements:**
- User model methods:
  - `getBadges()`, `hasBadge()`, `addBadge()`, `removeBadge()`
  - `makeSeasonSupporter()` - supporter management
  - `getDetailedStats($season)` - comprehensive statistics
  - `getPositionHeatmapData($season)` - for Pro Stats visualization
  - `getAccuracyTrends($season)` - accuracy trends with moving averages

**New Routes:**
- `/leaderboard/livewire` - New Livewire-based leaderboard
- `/leaderboard/user/{user}/livewire` - New Livewire-based user stats

**Remaining Work:**
- Mini-Leagues/Private Groups functionality
- Real-time notifications
- Position heatmap visualization (data method ready, needs UI)
- Additional badge types and achievements
- Analytics dashboard improvements
