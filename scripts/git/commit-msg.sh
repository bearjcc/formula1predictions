#!/usr/bin/env bash
# Commit-msg: conventional commits + block vague AI-style messages.
set -euo pipefail

MSG_FILE="${1:?commit message file required}"

if [ "${GIT_HYGIENE_SKIP:-}" = "1" ]; then
  exit 0
fi

subject="$(sed -n '1p' "$MSG_FILE" | tr -d '\r')"

# Merge / revert commits
if echo "$subject" | grep -qiE '^(Merge |Revert ")'; then
  exit 0
fi

# Agent checkpoint commits (after tests pass)
if echo "$subject" | grep -qE '^checkpoint:'; then
  exit 0
fi

# Conventional Commits (required for agents and contributors)
if echo "$subject" | grep -qE '^(feat|fix|refactor|test|docs|chore|style|perf|ci|build)(\([a-z0-9._/-]+\))?!?: .{4,}'; then
  exit 0
fi

# Block vague one-word subjects common from LLM agents
if echo "$subject" | grep -qiE '^(wip|updates?|changes?|stuff|fix(ed)?|misc|tmp|temp|save|commit)\s*$'; then
  echo "commit-msg: subject is too vague: \"$subject\"" >&2
  echo "  Use: feat|fix|refactor|test|docs|chore|style|perf|ci|build: <what and why>" >&2
  echo "  Or checkpoint: <note> after a verified test run." >&2
  exit 1
fi

echo "commit-msg: subject must use Conventional Commits." >&2
echo "  Example: fix(scoring): ignore DNS drivers in race results" >&2
echo "  Got: \"$subject\"" >&2
exit 1
