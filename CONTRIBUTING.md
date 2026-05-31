## Getting started

- Clone the repository and install dependencies with Composer and Node.
- Copy `.env.example` to `.env` and configure database, mail, and app URL.
- Run database migrations and seeders.
- Use `php artisan serve` (or your preferred web server) plus `npm run dev`/`build` for assets if applicable.

This project is a Laravel application; follow Laravel's standard local setup practices.

## Development workflow

### Git hygiene (humans and AI agents)

1. **Branch or worktree before coding** — not on `main`/`master`.
   - Branch: `git switch -c agent/my-task`
   - Worktree (recommended for agent sessions): `.\scripts\agent-task-start.ps1 -Task "my-task"` (Windows) or `./scripts/agent-task-start.sh my-task`
2. **Install repo hooks** (once per clone/worktree): `.\scripts\install-git-hooks.ps1` or `./scripts/install-git-hooks.sh`
   - **pre-commit**: blocks oversized staged commits; runs Pint on staged PHP
   - **commit-msg**: Conventional Commits (`feat:`, `fix:`, …) or `checkpoint:` after verified tests
3. **Check diff size** while working: `.\scripts\git\change-stats.ps1` or `./scripts/git/change-stats.sh`
4. **One branch, one objective** — split unrelated work into another branch/PR.

Override hooks only when necessary: `GIT_HYGIENE_SKIP=1` (single command).

### Commits and PRs

- **Small, focused PRs**: one concern per PR; prefer stacked small commits over a single 5k-line dump.
- **Commit messages**: `type(scope): summary` — e.g. `fix(scoring): treat DNS as non-finisher`. Avoid `wip`, `updates`, `changes`.
- **Do not** use `git commit --no-verify` or force-push to `main`/`master`.

Before opening a PR:

- Run `.\scripts\pre-push.ps1` (Windows) or `./scripts/pre-push.sh` (Unix).
- That runs composer audit, readiness, Pint, tests, frontend build, and **aislop** (AI slop gate; config in `.aislop/config.yml`).

CI runs the same checks (`.github/workflows/ci.yml`, `.github/workflows/aislop.yml`).

### AI-assisted changes

- Cursor project rules: `.cursor/rules/ai-git-hygiene.mdc`, command `/guardian` for architecture review.
- Scan for slop: `npm run slop` (alias for `aislop scan`).
- Agents must not skip failing tests or hooks to appear green.

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
