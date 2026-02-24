# Auth emails (verification + password reset) and Resend

## Overview

Two auth emails use the same styling (Laravel mail components in `resources/views/emails/`) and rely on `APP_URL` for link generation:

1. **Email verification** – sent after registration and when the user clicks "Resend verification email" on the verify-email notice.
2. **Password reset** – sent when the user requests a reset from the forgot-password page.

Both are customized in `AppServiceProvider::boot()` so copy and links use app name and the correct base URL.

## Resend

To send these (and other app mail) via [Resend](https://resend.com):

1. Set `MAIL_MAILER=resend` and `RESEND_KEY=re_...` (from Resend dashboard).
2. Set `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`. The **from** address must be a domain you have verified in Resend (e.g. `noreply@yourdomain.com`). Until the domain is verified, Resend may block or limit sending; forgot-password will not deliver until the sender is verified.
3. Ensure `APP_URL` matches your app (e.g. `https://yourapp.up.railway.app`). Links in the emails are built from this.

Example:

```env
MAIL_MAILER=resend
RESEND_KEY=re_xxxxxxxxxxxx
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
APP_URL=https://yourapp.up.railway.app
```

## Email 1: Verification

- **View:** `resources/views/emails/verify-email.blade.php`
- **Content:** Short copy asking the user to verify; single CTA button.
- **Styling:** Same as other app emails (`<x-mail::message>`, `<x-mail::button>`).
- **Link (magic link):** Built by Laravel’s `VerifyEmail` notification:
  - Route: `verification.verify` (GET `verify-email/{id}/{hash}`).
  - URL is a **temporary signed URL** (e.g. 60 minutes) with `id` (user id) and `hash` (sha1 of user’s email). Laravel generates it via `URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)])`. The `VerifyEmailController` validates the signature and marks the user verified.
  - Base URL comes from `APP_URL` (Laravel’s URL generator).
- **When sent:** On registration (Registered event) and when the user clicks "Resend verification email" on the verify-email page.

## Email 2: Password reset

- **View:** `resources/views/emails/reset-password.blade.php`
- **Content:** Short copy explaining the reset request; single CTA button; note about 60-minute expiry.
- **Styling:** Same as verification and other app emails.
- **Link:** Built in `AppServiceProvider` via `ResetPassword::createUrlUsing` and passed into the view in `toMailUsing`:
  - Route: `password.reset` (GET `reset-password/{token}`).
  - URL format: `{APP_URL}/reset-password/{token}?email={urlencoded_email}`. The reset form expects `token` in the path (from the route) and `email` in the query (pre-filled in the form).
- **When sent:** When the user submits the forgot-password form and the email matches a user; same generic success message is shown whether or not the user exists (security).

## Quick checks

- **Verification:** Register a new user or, as an unverified user, open the verify-email page and click "Resend verification email". Check inbox for the verification email; click the button and confirm you are redirected and marked verified.
- **Reset:** Open forgot-password, enter an existing user email, submit. Check inbox for the reset email; click the button and confirm the reset form loads with email pre-filled; set a new password and confirm you can log in.
- **Resend:** If using Resend, verify the sending domain in the Resend dashboard so both emails can be delivered (especially for forgot-password).
