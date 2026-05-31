#!/usr/bin/env bash
# Create a git worktree + agent branch for isolated AI work.
# Usage: ./scripts/agent-task-start.sh <task-slug> [base-branch]
set -euo pipefail

TASK="${1:?task slug required (e.g. f1-123-scoring-fix)}"
BASE="${2:-main}"

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

slug="$(echo "$TASK" | tr '[:upper:]' '[:lower:]' | sed -E 's/[^a-z0-9]+/-/g; s/^-+|-+$//g')"
[ -n "$slug" ] || { echo "task slug is empty after normalization" >&2; exit 1; }

branch="agent/$slug"
repo_name="$(basename "$ROOT")"
parent="$(dirname "$ROOT")"
worktree="$parent/${repo_name}-wt-${slug}"

[ ! -e "$worktree" ] || { echo "worktree already exists: $worktree" >&2; exit 1; }

base_ref="$BASE"
if git rev-parse --verify "origin/$BASE" >/dev/null 2>&1; then
  base_ref="origin/$BASE"
fi

git worktree add -b "$branch" "$worktree" "$base_ref" || git worktree add -b "$branch" "$worktree" "$BASE"

echo ""
echo "Branch:   $branch"
echo "Worktree: $worktree"
echo ""
echo "Next:"
echo "  1. Open the worktree folder in Cursor (not the main checkout)."
echo "  2. Run: ./scripts/install-git-hooks.sh"
echo "  3. After edits: ./scripts/git/change-stats.sh before each commit."
