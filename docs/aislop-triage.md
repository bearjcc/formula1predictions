# AISlop triage backlog (2026-05-31)

Baseline score after slop cleanup PR: **50** (`failBelow: 50`).

Run locally: `npm run slop` or `npm run aislop:ci`.

## Fixed in this branch

- `F1ApiService` API base URL moved to `config('f1.api_base_url')` (was `ai-slop/hardcoded-url`)
- Exclude `.cursor/hooks` and `.cursor/commands` from scans (hook scripts are not app imports)

## P1 — next vertical slices (one PR each)

| Area | Rule / smell | Files | Action |
|------|----------------|-------|--------|
| Services | `complexity/file-too-large` | `F1ApiService.php` (~1006 LOC), `ChartDataService.php`, `RaceScoringService.php` | Split by concern; keep tests green per slice |
| Livewire | `complexity/file-too-large` | `PredictionForm.php`, `draggable-driver-list.blade.php` | Extract subcomponents / form sections |
| Commands | `complexity/function-too-long` | `MergeUsers`, `AuditRace1Predictions`, `EnsureZoeRound2Prediction` | Extract private methods |
| JS | Knip false positives | Vite entry files (`app.js`, CSS) | Tune aislop exclude or knip config when supported |

## P2 — defer (noisy or needs product decision)

| Rule | Notes |
|------|--------|
| `knip/dependencies` | Audit scripts use devDeps at runtime; do not auto-remove |
| `ai-slop/console-leftover` | Warning only; sweep when touching files |
| Larastan / Deptrac | Separate initiative; not bundled with slop pass |

## Ratchet policy

Raise `ci.failBelow` in `.aislop/config.yml` only after **two consecutive** green `aislop:ci` runs at a higher score (e.g. 55+).
