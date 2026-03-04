-NoNewline

---

## Agent workflow

- **Run tests before claiming completion or making commits.** At minimum run the test suite (or the subset that covers your changes). Auth and other critical paths have tests that assert 200 and no Laravel 500 error page; if those fail, fix before committing.
- **Pre-push:** `.\scripts\pre-push.ps1` or `./scripts/pre-push.sh` runs audits, readiness (`app:check-ready`), Pint, tests, and build. CI runs the same checks (see .github/workflows/ci.yml).

---

## Handoff

**Shippable v1 backlog complete (2026-02-20).** All Now / Shippable v1 items (F1-084, 086/087, 069, 080, 072, 076, 093, 094, 095, 104 + test fixes) are done. No critical P1/P2 items remain in the Now scope.

### Final ship checklist (human)

**Staging smoke-test**

- Auth: register, login, logout, email verification flow, password reset.
- Predictions: create, edit, view; standings/predictions page and global leaderboard.
- Leaderboard: index, season, race, compare; filters and sort.
- Feedback: open feedback page, submit message; optional MAIL_FEEDBACK_TO check.
- Dark mode: toggle appearance; verify key pages (home, predictions, leaderboard, auth) in both themes.

**Production env**

- `ADMIN_EMAIL` set; on Railway, the custom `start-container.sh` (Railpack) runs `app:ensure-admin-user` at startup; otherwise run admin seeder or `php artisan app:ensure-admin-user`.
- **Railway:** Any `php` or `artisan` commands that agents suggest (e.g. tests, migrations, seeders) must be run in the **build stage** in Railway, not in the local shell, unless you have a separate way to run them (e.g. Railway CLI `railway run ...`).
- Queue worker: separate process (e.g. Railway service or Supervisor) running `php artisan queue:work`.
- Cron/scheduler: `schedule:run` via Railway cron or `railway/run-cron.sh` loop.
- Mail: production mail driver and `MAIL_FROM_*` so verification and feedback emails send (not just log).

**Recommendation:** Run the staging smoke-test once on the production URL after deploy, then open to users.

---

## AI agents and code quality

AI is a powerful junior engineer for this repo. Its job is to improve maintainability and clarity, not to ship as many lines of code as possible.

### 1. Implementation is not the bottleneck

- **Code is cheap, maintenance is not.** Before writing code, the agent must be able to state:
  - The concrete problem it is solving.
  - Why solving it is worth the added complexity.
  - How the change will be tested.
- If a feature or change is not clearly justified, the correct action is to recommend not implementing it.

### 2. Spec before code

- Summarize the request in your own words, including inputs, outputs, and success criteria.
- Identify the architectural layer being touched (HTTP, domain, persistence, background jobs, UI).
- Propose a short plan in plain language before editing files.
- When architecture docs and code disagree, prefer the documented architecture and call out the inconsistency.

### 3. No pattern drift

- Do not introduce new folder structures, state management styles, or helper patterns unless the task explicitly calls for an architectural change.
- If you see multiple conflicting patterns, pick one that aligns best with Laravel and this repo, explain the choice, and work toward unifying on that pattern.
- Never copy obviously messy or ad hoc code to new places. If you must touch it, improve it.

### 4. Mandatory smell check

For every change, quickly audit the touched files for:

- Duplication.
- Large or deeply nested functions.
- Mixed responsibilities in a single class or method.
- Tight coupling across layers (for example, views reaching into database logic).
- Hidden or shared mutable state.
- Dead code and unused branches.

If you find smells in the code you are touching, refactor them while staying within the scope of the task. Explain what you cleaned up and why it is better.

### 5. Design for deletion

- Prefer small, well named functions and classes with a single clear responsibility.
- Keep module boundaries explicit so a feature can be removed without surgery across the whole app.
- Avoid speculative abstractions and "future proofing". Build the simplest thing that clearly solves the current need.

### 6. Surface area control

- Do not expand the feature set just because it is easy. Avoid adding extra options, toggles, or flows unless they are clearly valuable.
- Keep diffs as small as possible while still fixing local structural problems you are already touching.
- If a change would significantly increase complexity, call that out and propose a smaller alternative.

### 7. Preserve human understanding

- When changing non-trivial behavior, describe:
  - The assumptions the code is making.
  - The state it reads and writes.
  - How errors and edge cases are handled.
- Favor code that a new contributor can understand and reason about in a few minutes.

### 8. Zero tolerance for slop

- Do not merge hacks under the promise of "we will clean this later". Later almost always means never.
- If you find a clearly bad pattern in an area you are modifying, prioritize removing or isolating it instead of building more on top.
- If the only way to implement a request is to add obvious slop, say so and recommend a different approach or a larger refactor.
