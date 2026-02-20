# F1-069: Email Verification Implementation Checklist

Enable Laravel email verification for normal registrations. **Auth changes are review-required** (AGENTS.md).

**Implemented 2026-02-20.** User model (MustVerifyEmail), routes (verified middleware), EnsureAdminUser, mail doc, and feature tests completed. Review auth/authorization changes per AGENTS.md before merging.

---

## 1. User model: enable MustVerifyEmail

- [x] **Uncomment and use the contract:** In `app/Models/User.php`, uncomment `use Illuminate\Contracts\Auth\MustVerifyEmail` and add `implements MustVerifyEmail` to the class declaration.
- [ ] **Use the trait:** Add `use Illuminate\Auth\MustVerifyEmail;` so the model has `hasVerifiedEmail()`, `markEmailAsVerified()`, and `sendEmailVerificationNotification()`. (The trait implements the contract.)
- [ ] **Keep existing cast:** `email_verified_at` is already in `casts()`; no change.

Result: New registrations will receive the framework’s verification email (Registered event → SendEmailVerificationNotification). No change needed in the registration Livewire/Volt component.

---

## 2. Routes and middleware

- [ ] **Apply `verified` to protected app routes:** The dashboard already uses `middleware(['auth', 'verified'])`. Add `verified` to the main authenticated group in `routes/web.php` (the `Route::middleware(['auth'])->group(...)` that contains settings, predict, predictions, leaderboard, admin, etc.) so that unverified users are redirected to the verification notice instead of accessing app features. Use `middleware(['auth', 'verified'])` for that group.
- [ ] **Leave verification routes unverified:** In `routes/auth.php`, the verify-email notice and the `verification.verify` route must remain accessible to authenticated-but-unverified users (no `verified` middleware). Current setup is correct: verification routes are under `auth` only.
- [ ] **Optional:** If any route should be reachable by unverified users (e.g. a “verify later” landing page), keep it in a group that has `auth` but not `verified`.

---

## 3. Impact on existing users

- [ ] **Admins:** `EnsureAdminUser` already sets `email_verified_at => now()` when creating the first admin. For an **existing** admin that has no `email_verified_at`, either: (a) require them to verify once, or (b) in `EnsureAdminUser`, when syncing an existing admin, set `email_verified_at = now()` if it is null so admins are always treated as verified. Document the choice.
- [ ] **Existing normal users:** Users who already have `email_verified_at` set remain verified. Users with `email_verified_at` null will be redirected to the verification notice after login once `verified` middleware is applied.
- [ ] **Grandfathering (optional):** If you want to avoid requiring verification for existing accounts, run a one-time migration or command that sets `email_verified_at = now()` for all users where it is null. Otherwise, leave them unverified and require verification on next login.

---

## 4. Config and notifications

- [ ] **config/auth.php:** No email-verification keys; no change required for basic verification.
- [ ] **Notification customization (optional):** To customize the verification email (copy, from name, etc.), publish the notification: `php artisan vendor:publish --tag=laravel-notifications` and then customize `Illuminate\Auth\Notifications\VerifyEmail` or create a custom notification and override `sendEmailVerificationNotification()` on the User model. Ensure mail driver and `MAIL_FROM` are configured for production.

---

## 5. Tests

- [ ] **Existing:** `tests/Feature/Auth/EmailVerificationTest.php` already covers verification screen, successful verify, and invalid hash. Once User implements `MustVerifyEmail`, ensure these tests still pass (they use `User::factory()->unverified()` and `hasVerifiedEmail()`).
- [ ] **Registration flow:** Add or extend a test that registers a new user and asserts a verification email is sent (e.g. `Notification::fake()` and assert `VerifyEmail` notification sent).
- [ ] **Access control:** Add or extend a test that an unverified user hitting a `verified`-protected route (e.g. dashboard or predict) is redirected to `verification.notice`.
- [ ] **EnsureAdminUser:** Confirm existing test(s) still pass; if you changed admin `email_verified_at` behavior, add an assertion for it.

---

## 6. Final checks

- [ ] Run full test suite (e.g. `php artisan test` or `.\scripts\test-batches.ps1`).
- [ ] Run `vendor/bin/pint --dirty`.
- [ ] Update TODO.md: set F1-069 to `done` and add a brief completion note.
- [ ] Handoff: note in AGENTS.md or handoff that auth change was implemented and reviewed.

---

## Summary

| Area              | Action |
|-------------------|--------|
| User model        | Implement `MustVerifyEmail` (contract + trait); keep `email_verified_at` cast. |
| Routes            | Add `verified` to main auth group in `web.php`; leave verification routes with `auth` only. |
| Existing users    | Decide: require verification for null `email_verified_at` or grandfather; optionally set admin `email_verified_at` in EnsureAdminUser. |
| Config/notifications | No config change required; optionally publish/customize VerifyEmail notification. |
| Tests             | Rely on EmailVerificationTest; add registration + verification email test and unverified-access redirect test. |
