# Production Troubleshooting Guide

Quick reference for diagnosing and fixing the issues you described: login failures, crashes, slow races page, and broken buttons.

---

## Root Cause Found (2026-02-13)

**MySQL connection timeout** was causing all symptoms. Deploy logs showed:

```
PDOException: SQLSTATE[HY000] [2002] Connection timed out
```

The app was using `mysql.railway.internal` (private network), which was timing out—likely due to region mismatch or cold MySQL. **Fix applied:** `DB_URL` set to reference `${{MySQL.MYSQL_PUBLIC_URL}}`, which uses the TCP proxy (`metro.proxy.rlwy.net`) and works from any region. Laravel uses `DB_URL` when set, so this overrides the private host.

A redeploy should have been triggered. After it completes, test login and page load.

---

## 1. Login: Nobody Can Log In (Including Admin)

**Most likely cause: Sessions not persisting.**

### Checklist

| Check | Fix |
|-------|-----|
| **SESSION_DRIVER** | Must be `database` on Railway. The `file` driver uses ephemeral storage and loses sessions on deploy/restart. |
| **APP_URL** | Must exactly match your production URL (e.g. `https://formula1predictions.up.railway.app`). Wrong URL breaks redirects and cookie domain. |
| **sessions table** | Run `php artisan migrate` to ensure the `sessions` table exists (it's in the users migration). |
| **ADMIN_EMAIL, ADMIN_PASSWORD** | Set in Railway Variables. `app:ensure-admin-user` runs on each deploy—it creates/updates the admin. If these are wrong or missing, no admin exists. |

### Verify in Railway Dashboard

1. **Variables tab:** Confirm `SESSION_DRIVER=database`, `APP_URL=https://your-actual-domain`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
2. **Deploy logs:** After deploy, you should see "Admin user X already exists" or "Created admin user X". If you see "ADMIN_EMAIL is not set" or "ADMIN_PASSWORD is not set", the admin was never created.

### If login form submits but you end up logged out

- Session is not being stored. Double-check `SESSION_DRIVER=database` and that the `sessions` table exists.
- For custom domains (e.g. f1.ursaminor.games), leave `SESSION_DOMAIN` **unset** so the cookie is scoped to the current host. If you had set `SESSION_DOMAIN=null` in env, the app now treats that as “no domain” so cookies work; redeploy and try again.

---

## 1b. Migrations fail: SQLSTATE[42000] syntax error "near '10'" or table_type

If deploy fails during `php artisan migrate` with a MySQL syntax error on `information_schema.tables` / `table_type in ('BASE TABLE', 'SYSTEM VERSIONED')`, the app uses a custom MySQL schema grammar that checks only `BASE TABLE`. This is already applied in code (see `App\Database\Schema\Grammars\MySqlGrammar` and `AppServiceProvider`). Ensure you’re on the latest deploy. If the error persists, confirm **`DB_URL` or `DB_DATABASE`** is your actual database name (e.g. `railway`). With `DB_URL`, the path must be like `.../railway`; a misparsed URL can pass a number as the database and trigger "near '10'". The grammar is applied whenever the `mysql` connection is configured (not only when default) and always quotes the schema name; set **DB_DATABASE** explicitly (e.g. `railway`) if using DB_URL to avoid misparsed database segment.

---

## 2. Site Crashes (500 errors)

### Checklist

| Check | Fix |
|-------|-----|
| **APP_KEY** | Must be set. Run `php artisan key:generate --show` locally and add to Railway Variables. |
| **Database** | MySQL vars (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) must point to your Railway MySQL service. Use `${{MySQL.MYSQLHOST}}` etc. if using variable references. |
| **APP_DEBUG** | Keep `false` in production. Turn on temporarily only to capture error details, then turn off. |
| **Build vs runtime env** | `nixpacks.toml` runs `config:cache` at build time. Railway build may not have access to secrets. The start command runs `config:cache` again at runtime—ensure all required vars are in Railway Variables (not just in a local `.env`). |

### View errors

1. **Railway deploy logs:** Project → Deployments → select deployment → Logs. Look for PHP fatal errors or stack traces.
2. **Laravel logs:** If you can run `railway run php artisan tail` or access storage, check `storage/logs/laravel.log`.

---

## 3. Races Page Takes Ages to Load

**Cause:** When the `races` table has no rows for that year, the app calls the external F1 API to sync. That blocks the request until the API responds.

### Mitigations

1. **Preload on deploy:** `f1:ensure-season-data` runs at startup and loads the current year. Ensure it completes successfully (check deploy logs).
2. **Load more years:** The command only loads `config('f1.current_season')` (e.g. 2026). Visiting `/2025/races` when 2025 data is missing will trigger an API call. Consider adding `f1:ensure-season-data 2025` (and 2024) to the start command if users browse past seasons often.
3. **Timeout:** F1 API has 30s timeout and 3 retries. Slow API = slow page. Caching happens after first load—subsequent loads use the DB.

### Quick fix for deploy

Add to `nixpacks.toml` start command (before `serve`):

```
php artisan f1:ensure-season-data 2025 && php artisan f1:ensure-season-data 2026
```

(adjust years as needed)

---

## 4. Buttons Don't Work

**Likely causes:** Livewire depends on JS, sessions, and correct asset URLs.

| Symptom | Check |
|---------|-------|
| Login/Register submit does nothing | Session or CSRF issue (see Login section). |
| Livewire buttons (e.g. "Make Prediction", "Try Again") do nothing | 1) Open browser DevTools (F12) → Console. Look for JS errors or 419/500. 2) Ensure `APP_URL` is correct so Livewire's JS and endpoints resolve. 3) Vite build: `npm run build` must run; assets in `public/build` must be deployed. |
| 419 on form submit | CSRF token mismatch. Usually means session not persisting or wrong domain. |

### Verify Livewire assets

Visit `https://your-site.com/build/assets/app-xxx.js` (or similar). If 404, the build failed or `APP_URL` is wrong for asset URLs.

---

## 5. Railway Variable Checklist

Copy this into your Railway project and ensure each is set:

```
APP_NAME="F1 Predictions"
APP_ENV=production
APP_KEY=<from php artisan key:generate --show>
APP_DEBUG=false
APP_URL=https://YOUR-ACTUAL-RAILWAY-URL

ADMIN_EMAIL=your-admin@example.com
ADMIN_PASSWORD=your-secure-password
ADMIN_NAME=Your Name

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# Database: Prefer DB_URL (public TCP proxy) over private host—avoids connection timeouts.
# When DB_URL is set, Laravel uses it and ignores DB_HOST/DB_PORT/etc.
DB_URL=${{MySQL.MYSQL_PUBLIC_URL}}
DB_CONNECTION=mysql
# Fallbacks (used only if DB_URL unset):
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

---

## 6. Quick Diagnostic Commands (run via Railway CLI)

```bash
railway link   # if not already linked
railway run php artisan config:show session
railway run php artisan migrate:status
railway run php artisan app:ensure-admin-user
```

---

## Next Steps

1. Confirm `SESSION_DRIVER=database` and `APP_URL` in Railway.
2. Re-deploy and watch deploy logs for `app:ensure-admin-user` and `f1:ensure-season-data`.
3. Try login again; if it still fails, temporarily set `APP_DEBUG=true` and retry to see the actual error (then set back to `false`).
4. Check browser DevTools Console and Network tab when clicking broken buttons.
