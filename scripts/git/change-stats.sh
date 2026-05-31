#!/usr/bin/env bash
# Print working-tree and staged diff stats (for agents and humans).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT"

branch="$(git branch --show-current 2>/dev/null || echo "(detached)")"
echo "branch: $branch"

if [ "$branch" = "main" ] || [ "$branch" = "master" ]; then
  echo "warning: editing on protected default branch; use a feature branch or worktree." >&2
fi

staged_files="$(git diff --cached --name-only | wc -l | tr -d ' ')"
staged_lines=0
if [ "$staged_files" -gt 0 ]; then
  staged_lines="$(git diff --cached --numstat | awk '{s+=$1+$2} END {print s+0}')"
fi

unstaged_files="$(git diff --name-only | wc -l | tr -d ' ')"
unstaged_lines=0
if [ "$unstaged_files" -gt 0 ]; then
  unstaged_lines="$(git diff --numstat | awk '{s+=$1+$2} END {print s+0}')"
fi

echo "staged:   $staged_files files, $staged_lines lines"
echo "unstaged: $unstaged_files files, $unstaged_lines lines"
echo ""
git diff --stat 2>/dev/null | tail -n 5 || true
