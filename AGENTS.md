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
- Queue worker: separate process (e.g. Railway service or Supervisor) running `php artisan queue:work`.
- Cron/scheduler: `schedule:run` via Railway cron or `railway/run-cron.sh` loop.
- Mail: production mail driver and `MAIL_FROM_*` so verification and feedback emails send (not just log).

**Recommendation:** Run the staging smoke-test once on the production URL after deploy, then open to users.
