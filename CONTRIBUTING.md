## Getting started

- Clone the repository and install dependencies with Composer and Node.
- Copy `.env.example` to `.env` and configure database, mail, and app URL.
- Run database migrations and seeders.
- Use `php artisan serve` (or your preferred web server) plus `npm run dev`/`build` for assets if applicable.

This project is a Laravel application; follow Laravel's standard local setup practices.

## Development workflow

- **Branching**: Create feature branches from the main branch for any non-trivial change.
- **Small, focused PRs**: Keep changesets small and cohesive around a single concern.
- **Commit messages**: Use clear, descriptive messages that explain the "why," not just the "what."

Before opening a PR:

- Run the test suite (PHPUnit/Pest) locally.
- Run static analysis and formatting (e.g., Laravel Pint) if configured.
- On this project, prefer running the pre-push script:
  - Windows: `.\scripts\pre-push.ps1`
  - Unix-like: `./scripts/pre-push.sh`

These scripts run audits, readiness checks, Pint, tests, and the build so your changes match CI.

## Code style and architecture

- Follow Laravel conventions for controllers, models, migrations, and Blade templates.
- Keep controllers and Livewire components thin; move business rules into domain services or models.
- Prefer **small pure functions**, clear interfaces, and explicit dependencies over hidden globals.
- Avoid introducing new patterns or folder structures if an existing one already fits.
- When in doubt about structure, read `ARCHITECTURE.md` and align with the documented layers.

### Architecture Guardian

When using the "guardian" assistant/command in Cursor:

- It will first read `ARCHITECTURE.md` and this `CONTRIBUTING.md` file.
- It optimizes for long-term maintainability, clarity, and architectural consistency.
- It may refactor nearby code to remove smells (duplication, deep nesting, mixed responsibilities) within the scope of your change.

If you need to diverge from the documented architecture, document why in your PR description and, when appropriate, propose updates to `ARCHITECTURE.md`.

## Testing

- New features should include tests where practical (feature, unit, or browser tests as appropriate).
- Reproduce bugs with failing tests first when possible, then fix them.
- Use existing tests as examples for structure and naming.

Do not claim a feature is "done" until it has test coverage proportionate to its risk and impact.
