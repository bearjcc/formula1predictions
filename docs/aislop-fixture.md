# aislop fixture project

This repository is set up as a real-world scan target for [aislop](https://github.com/scanaislop/aislop): Laravel 12, Livewire 3, PHP, Blade, and a small Vite/JS surface.

## Scan command (pinned harness)

Use the same invocation as the aislop OSS benchmark:

```bash
AISLOP_NO_TELEMETRY=1 DO_NOT_TRACK=1 CI=1 NO_COLOR=1 npx aislop@<version> scan . --json
```

Or from this repo after `npm install`:

```bash
npm run aislop:ci
```

## Cohort metadata (for aislop maintainers)

| Field | Value |
| --- | --- |
| Repository | `https://github.com/bearjcc/formula1predictions` |
| Primary languages | PHP, JavaScript |
| Package managers | Composer, npm |
| Config | `.aislop/config.yml` |
| Notes | Excludes `vendor/`, `tests/`, and `scripts/` so scans focus on app code; npm audit is handled separately in CI |

When adding this repo to an aislop benchmark cohort manifest, pin a commit SHA and record the resulting score and top rules in the run summary.

## Ratcheting

`ci.failBelow` in `.aislop/config.yml` is intentionally conservative. Raise it as warnings are fixed so CI enforces improvement without blocking on legacy noise.
