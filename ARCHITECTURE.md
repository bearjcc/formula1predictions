## Overview

This is a Laravel application for Formula 1 prediction games and leaderboards. The architecture follows Laravel's defaults with a light layering around HTTP/UI, domain logic, and infrastructure.

- **UI layer**: Routes, controllers, Livewire components, Blade templates, Tailwind-based styling.
- **Domain layer**: Eloquent models, domain services, policies, and scoring logic.
- **Infrastructure layer**: Database (migrations, seeders, custom schema/connection classes), mail, notifications, console commands, and queues.

When you change or add code, first decide which layer you are working in and keep responsibilities local to that layer.

## Layers

### UI layer

- `routes/` defines HTTP entrypoints and maps them to controllers or Livewire components.
- `app/Http/Controllers/` handles request orchestration, validation, and delegates to domain services.
- `app/Livewire/` components encapsulate page-level and interactive UI behavior.
- `resources/views/` Blade templates render HTML; avoid putting domain logic here.

**Guidelines**

- Controllers and Livewire components should stay thin: validate input, call domain services, select views, and shape view models.
- Avoid direct database queries in views. Prefer passing already-shaped data from controllers or Livewire.
- Do not put scoring or cross-cutting domain logic in controllers or views.

### Domain layer

- `app/Models/` contains Eloquent models representing core concepts (users, races, predictions, etc.).
- `app/Services/` contains explicit domain services such as scoring, simulation/backtesting, and other business workflows.
- `app/Policies/` enforces authorization rules for models and actions.

**Guidelines**

- Keep domain rules close to the domain: models and services should own invariants and scoring math.
- Prefer small, focused service classes over large "god" services.
- Domain code should not know about Blade templates or specific HTTP details.

### Infrastructure layer

- `database/migrations/` and `database/seeders/` define schema and data bootstrapping.
- `app/Database/` contains custom database connections or grammar extensions.
- `app/Console/Commands/` contains background or scheduled workflows.
- `app/Mail/` and `app/Notifications/` integrate with external channels.

**Guidelines**

- Keep infrastructure details (SQL dialects, transport specifics) here.
- Domain and UI code should depend on abstractions, not on database details or mail drivers.

## Cross-cutting conventions

- **Validation**: Prefer Laravel form requests or explicit validation in controllers/Livewire. Avoid duplicating validation rules in multiple layers when possible.
- **Authorization**: Use policies and gates; avoid ad hoc authorization checks scattered around.
- **Error handling**: Fail fast with clear exceptions. Surface user-facing errors via validation errors or explicit UI messages, not generic 500s.
- **Naming**: Names should reflect intent and the domain (e.g., `ScoringService`, `PredictionPolicy`).

## Extending the system

When adding new features:

1. **Clarify the layer** you are working in (UI, domain, infrastructure).
2. **Reuse existing patterns**:
   - New user-facing flows usually mean a controller or Livewire component plus Blade views.
   - New business rules belong in models or services.
   - New scheduled or background work belongs in console commands or queued jobs.
3. **Respect boundaries**:
   - UI does not reach directly into infrastructure details.
   - Domain logic does not know about HTML, CSS, or concrete transport mechanisms.
4. **Prefer composition**:
   - Compose small services and helpers rather than introducing new inheritance hierarchies.

If you encounter patterns that conflict with this document, treat this file as the source of truth and prefer refactoring toward these boundaries in the area you touch.
