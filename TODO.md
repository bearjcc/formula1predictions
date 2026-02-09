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

_(None; pick from Next or Later.)_

---

### Next

Medium-horizon improvements that should be tackled soon.

- [ ] **F1-050: Configure production mail, session security, and logging**
  - Type: security | Priority: P1 | Risk: high | Owner: agent
  - Affected: .env.example, config/session.php, config/logging.php
  - Mail driver defaults to log - notifications will never reach users. Session secure cookie not configured - session cookie sent over plain HTTP. LOG_LEVEL=debug and single-file logging (no rotation).

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

- [ ] **F1-062: Remove hardcoded mockup data from edit prediction view**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/views/predictions/edit.blade.php (L204)
  - Hardcoded mockup data in edit prediction view. Remove.

- [ ] **F1-063: Remove console.log debug statements from production JS**
  - Type: cleanup | Priority: P3 | Risk: low | Owner: agent
  - Affected: resources/js/notifications.js (L11, L79)
  - console.log debug statements in production JS. Remove.

- [ ] **F1-064: Fix clearAllCache() to cover all years**
  - Type: bug | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Services/F1ApiService.php (L720)
  - clearAllCache() only covers years 2020-2025, misses 2026. Update to include all years.

- [ ] **F1-065: Remove sensitive fields from User $fillable**
  - Type: security | Priority: P2 | Risk: low | Owner: agent
  - Affected: app/Models/User.php
  - Sensitive fields (is_season_supporter, badges) in User $fillable. Consider removing or using guarded.

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
