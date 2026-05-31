# git-hygiene

You must follow repo git hygiene. This is not optional.

## Check now

Run (PowerShell from repo root):

```powershell
.\scripts\git\change-stats.ps1
git branch --show-current
git config core.hooksPath
```

If branch is `main` or `master`: **stop**. Create a branch or worktree before any further edits:

```powershell
.\scripts\agent-task-start.ps1 -Task "<short-slug>"
# or: git switch -c agent/<short-slug>
```

If `core.hooksPath` is not `.githooks`:

```powershell
.\scripts\install-git-hooks.ps1
```

## Rules (agents)

| Do | Do not |
|----|--------|
| Branch/worktree before first edit | Code on `main`/`master` |
| `change-stats` before commit | 10k-line uncommitted dumps |
| Conventional Commits when user asks to commit | `wip`, `updates`, `changes` |
| Commit only when user asked | Drive-by commits |
| `pre-push.ps1` before "done" | `--no-verify`, force-push main |
| Small, focused commits | One giant commit |

Full detail: `.cursor/rules/ai-git-hygiene.mdc`, `AGENTS.md`, `CONTRIBUTING.md`.
