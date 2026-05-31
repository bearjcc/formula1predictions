#!/usr/bin/env bash
# Shared helpers for git hygiene scripts.
set -euo pipefail

repo_root() {
  git rev-parse --show-toplevel
}

load_hygiene_config() {
  local root
  root="$(repo_root)"
  # shellcheck disable=SC1091
  [ -f "$root/scripts/git/hygiene.defaults" ] && . "$root/scripts/git/hygiene.defaults"
  # shellcheck disable=SC1091
  [ -f "$root/scripts/git/hygiene.local" ] && . "$root/scripts/git/hygiene.local"
  MAX_COMMIT_FILES="${MAX_COMMIT_FILES:-25}"
  MAX_COMMIT_LINES="${MAX_COMMIT_LINES:-500}"
  WARN_STAGED_LINES="${WARN_STAGED_LINES:-300}"
}

staged_stats() {
  local files=0 lines=0
  files="$(git diff --cached --name-only | wc -l | tr -d ' ')"
  if [ "$files" -eq 0 ]; then
    echo "0 0"
    return
  fi
  lines="$(git diff --cached --numstat | awk '{s+=$1+$2} END {print s+0}')"
  echo "$files $lines"
}

skip_hygiene() {
  [ "${GIT_HYGIENE_SKIP:-}" = "1" ]
}
