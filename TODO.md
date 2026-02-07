## Formula1Predictions TODO Backlog

- **schema_version**: 1
- **purpose**: Authoritative, machine-parseable backlog for AI agents (and collaborators) working on the Formula1Predictions Laravel 12 + Livewire app.
- **references**:
  - [AGENTS.md](AGENTS.md)
  - [AGENTS_PRD.md](AGENTS_PRD.md)
  - [.cursor/rules/todo-and-project-management.mdc](.cursor/rules/todo-and-project-management.mdc)
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

Short-horizon, high-value tasks that are ready for agents to pick up immediately.

_(None; F1-016, F1-017 completed.)_

---

### Next

Medium-horizon improvements that should be tackled soon.

- **id**: F1-018
  - **title**: Optimize or isolate F1ApiTest to avoid full-suite timeout
  - **type**: chore
  - **status**: todo
  - **priority**: P3
  - **risk_level**: low
  - **owner**: agent
  - **affected_areas**:
    - tests/Feature/F1ApiTest.php
  - **description**: F1ApiTest loops over many rounds (~48s); full `php artisan test` can timeout. Consider reducing iterations, mocking external calls in default suite, or excluding from default run.
  - **acceptance_criteria**:
    - Full test suite completes without timeout.
    - F1 API integration still verified when run explicitly.
  - **notes**:
    - See AUDIT_REPORT.md Known Issues.

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
