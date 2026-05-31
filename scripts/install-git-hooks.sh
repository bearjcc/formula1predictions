#!/usr/bin/env bash
# Point this repo at .githooks/. Run from repo root.
set -euo pipefail
cd "$(dirname "$0")/.."

git config core.hooksPath .githooks
chmod +x .githooks/pre-commit .githooks/commit-msg 2>/dev/null || true
chmod +x scripts/git/*.sh 2>/dev/null || true

echo "core.hooksPath set to .githooks"
echo "Hooks: pre-commit (size + Pint), commit-msg (Conventional Commits)"
echo "Stats: ./scripts/git/change-stats.sh"
echo "Skip once: GIT_HYGIENE_SKIP=1"
