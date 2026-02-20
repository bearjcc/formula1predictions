# Auth Pages: Shared Layout & Dark Mode

## Goal

A single shared layout for all auth pages (login, register, forgot-password, reset-password, verify-email, confirm-password) that matches the main app layout: same header/branding, backgrounds, typography, and dark-mode behavior, with no half-light/half-dark flash.

## Reference

- **Main layout:** `resources/views/components/layouts/layout.blade.php`
- **Design system:** [DESIGN_SYSTEM.md](../DESIGN_SYSTEM.md) — Mary UI first, zinc neutrals, F1 red accents, dark mode via `.dark` on root

---

## 1. How appearance is set (no flash)

- **Source of truth:** `session('appearance', config('f1.default_appearance', 'system'))` — values `light`, `dark`, or `system`.
- **HTML root:** Same in both main and auth layouts:
  - Set **once** in the layout Blade before any output:
    - `$appearance = session('appearance', config('f1.default_appearance', 'system'));`
  - On `<html>`:
    - `@class(['dark' => $appearance === 'dark'])` — server adds `dark` only when preference is explicitly dark.
    - `data-appearance="{{ $appearance }}"` — so the inline script and JS can resolve `system` to light/dark.
- **Blocking script in `<head>`** (in `partials.head`):
  - Runs before body/content; no FOUC.
  - Reads `document.documentElement.getAttribute('data-appearance')` (or `'system'`).
  - Resolves `system` with `window.matchMedia('(prefers-color-scheme: dark)').matches`.
  - Sets `document.documentElement.classList.toggle('dark', isDark)` so that on first paint the correct theme is applied before any visible content.
- **Client-side sync:** `resources/js/app.js` applies the same logic on `livewire:init` and `livewire:navigated` when the user changes appearance (Settings), so wire:navigate and full reload stay in sync.

**Rule:** Auth layout must use the same `partials.head` and the same `$appearance` / `<html>` attributes as the main layout. Do not add a second appearance script or override `data-appearance` in auth-only markup.

---

## 2. Shared layout structure (auth)

- **Single entry:** All auth Livewire/Volt pages use `#[Layout('components.layouts.auth')]`, which forwards to `components/layouts/auth/simple.blade.php`.
- **Shared shell:**
  - `<!DOCTYPE html>`, then `<html lang="..." @class([...]) data-appearance="...">` (as above).
  - `<head>`: `@include('partials.head')` only (same as main layout).
  - `<body>`: Same base as main layout so backgrounds and typography match.

**Concrete Tailwind/body pattern (aligned with main layout):**

- Body: `class="min-h-screen bg-white dark:bg-zinc-900"` (main uses this; auth should not use a different neutral or gradient that could look different).
- Optional: `antialiased` for type; keep one consistent body class set across both layouts.

**Inner wrapper (auth-only):**

- Centered card area: e.g. `flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10`.
- Content width: `max-w-sm` for the form column.
- Do not use `bg-background` unless it is defined in the design system and matches main; prefer explicit `bg-white` / `dark:bg-zinc-900` on body so auth and app feel the same.

---

## 3. Branding and typography (auth)

- **Logo/title block:** Same as main layout’s branding (logo + app name + short tagline).
  - Use the same logo asset and link to `route('home')`.
  - Classes: `text-zinc-900 dark:text-zinc-100` for app name, `text-zinc-600 dark:text-zinc-400` for tagline (DESIGN_SYSTEM neutrals).
- **Typography:** Rely on the same font stack as main (Instrument Sans via `partials.head`). No extra font or size overrides in auth layout.
- **Headings:** Auth page titles (e.g. “Log in to your account”) use the shared `<x-auth-header>` component with theme-safe classes: `text-2xl font-bold text-zinc-900 dark:text-zinc-100`, description `text-zinc-600 dark:text-zinc-400`.

---

## 4. Mary UI and Tailwind patterns (auth pages)

- **Forms:** Mary UI first: `x-mary-input`, `x-mary-button`, `x-mary-checkbox` (see login, register, reset-password).
- **Labels:** `text-sm font-medium text-zinc-700 dark:text-zinc-300`.
- **Links:** Primary: `text-zinc-900 dark:text-zinc-100` (or font-medium) with hover `text-zinc-700 dark:text-zinc-300`; ensure focus ring: `focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-zinc-800`.
- **Secondary text:** `text-zinc-600 dark:text-zinc-400`.
- **Session/status messages:** Use `<x-auth-session-status>`; success text should support dark mode (e.g. `text-green-600 dark:text-green-400`).

Auth pages **reuse**:

- `components/layouts/auth.blade.php` → `layouts.auth.simple` (single shared layout).
- `partials.head` (appearance script, favicon, fonts, Vite).
- `<x-auth-header>`, `<x-auth-session-status>`.
- Same zinc/red and dark: variants as the rest of the app (DESIGN_SYSTEM).

---

## 5. Scripts

- Auth layout must include `@livewireScripts` before `</body>` so that Livewire/Volt auth components work. Main layout already does; auth layout must match.

---

## 6. Acceptance criteria

### Shared elements

- [ ] Auth uses the same `partials.head` as the main layout (no duplicate or auth-only head).
- [ ] Auth layout sets `<html>` with `data-appearance` and `@class(['dark' => $appearance === 'dark'])` using the same `$appearance` source as main layout.
- [ ] Body base classes match main layout: `min-h-screen bg-white dark:bg-zinc-900` (no divergent neutrals or gradients that cause visible difference).
- [ ] Branding block (logo, app name, tagline) matches main layout styling and links to home.
- [ ] All auth pages use `#[Layout('components.layouts.auth')]` and render inside the shared auth layout only.
- [ ] Auth layout includes `@livewireScripts` before `</body>`.
- [ ] `<x-auth-header>` and `<x-auth-session-status>` use theme-safe (zinc/dark) text classes.

### Dark-mode behavior

- [ ] No flash: the inline script in `partials.head` runs before body content and sets `document.documentElement.classList.toggle('dark', …)` from `data-appearance` (and `prefers-color-scheme` when `system`).
- [ ] Auth pages respect system preference when `data-appearance="system"` (no half-light/half-dark).
- [ ] After login/navigation to app (or vice versa), theme is consistent (same session appearance and same HTML/body classes).
- [ ] All interactive and text elements on auth pages use explicit `dark:` variants where needed (zinc, green for success, red for links/errors) so nothing is invisible or low-contrast in dark mode.

### DESIGN_SYSTEM alignment

- [ ] Mary UI components used for form controls and buttons; zinc for neutrals; red for primary actions/branding (per DESIGN_SYSTEM).
- [ ] No hardcoded colors outside the design system; use Tailwind zinc/red/semantic and `dark:` variants.

---

## Summary

- **One shared auth layout** (`layouts.auth` → `layouts.auth.simple`) with the same appearance source, same `<html>`/`<body>` and head as the main layout.
- **Appearance:** Set once in Blade (`$appearance`, `data-appearance`, `class="dark"` when explicit dark); resolved for `system` in a blocking script in `partials.head` so there is no flash.
- **Auth pages** reuse the same Tailwind/Mary UI patterns and components; acceptance criteria above verify shared elements and dark-mode behavior.
