#!/usr/bin/env bash
# Pre-commit: staged change limits + Pint on dirty PHP.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
# shellcheck source=scripts/git/lib.sh
. "$ROOT/scripts/git/lib.sh"

if skip_hygiene; then
  exit 0
fi

load_hygiene_config
read -r files lines <<<"$(staged_stats)"

if [ "$files" -eq 0 ]; then
  exit 0
fi

if [ "$files" -gt "$MAX_COMMIT_FILES" ] || [ "$lines" -gt "$MAX_COMMIT_LINES" ]; then
  echo "pre-commit: staged change too large ($files files, $lines lines)." >&2
  echo "  Limits: $MAX_COMMIT_FILES files, $MAX_COMMIT_LINES lines." >&2
  echo "  Split into smaller commits, or GIT_HYGIENE_SKIP=1 for a one-off override." >&2
  exit 1
fi

if [ "$lines" -gt "$WARN_STAGED_LINES" ]; then
  echo "pre-commit: warning - large staged diff ($files files, $lines lines). Consider splitting." >&2
fi

if git diff --cached --name-only --diff-filter=ACMR | grep -qE '\.(php|blade\.php)$'; then
  echo "pre-commit: Pint (dirty PHP)..."
  (cd "$ROOT" && vendor/bin/pint --dirty --test)
fi

exit 0
