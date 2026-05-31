#!/usr/bin/env node
/**
 * Cursor hook: block risky git commands from agents (force push, --no-verify, push to main).
 * stdin: Cursor hook JSON; stdout: { permission, user_message?, agent_message? }
 */
import { readFileSync } from "node:fs";

function readInput() {
  try {
    return JSON.parse(readFileSync(0, "utf8"));
  } catch {
    return {};
  }
}

function allow(extra = {}) {
  process.stdout.write(JSON.stringify({ permission: "allow", ...extra }));
}

function deny(userMessage, agentMessage) {
  process.stdout.write(
    JSON.stringify({
      permission: "deny",
      user_message: userMessage,
      agent_message: agentMessage,
    }),
  );
  process.exit(2);
}

const input = readInput();
const command = String(input.command ?? input.shellCommand ?? "").trim();

if (!command) {
  allow();
  process.exit(0);
}

const normalized = command.replace(/\s+/g, " ");

// Never bypass hooks
if (/\bgit\b.*\bcommit\b.*--no-verify\b/.test(normalized)) {
  deny(
    "git commit --no-verify is blocked in this repo.",
    "Use a normal commit. Fix pre-commit/commit-msg failures instead of skipping hooks.",
  );
}

if (/\bgit\b.*\bpush\b.*--no-verify\b/.test(normalized)) {
  deny("git push --no-verify is blocked.", "Do not skip git hooks.");
}

// Block force push to default branches
if (
  /\bgit\b.*\bpush\b/.test(normalized) &&
  /--force|-f\b/.test(normalized) &&
  /\b(main|master)\b/.test(normalized)
) {
  deny(
    "Force push to main/master is blocked.",
    "Use a feature branch and open a PR. Never force-push the default branch.",
  );
}

// Direct push to default branch (agents should use PRs)
if (
  /^\s*git\s+push\b/.test(normalized) &&
  !/\s[-\w]+\s/.test(normalized.replace(/^\s*git\s+push\s+/, "")) &&
  (/\borigin\s+main\b/.test(normalized) ||
    /\borigin\s+master\b/.test(normalized) ||
    /\bmain\b\s*$/.test(normalized) ||
    /\bmaster\b\s*$/.test(normalized))
) {
  deny(
    "Direct push to main/master is blocked for agents.",
    "Push a feature/agent branch and open a PR. Run scripts/agent-task-start before coding.",
  );
}

// Warn-style block: committing on main without explicit override env in command
if (
  /\bgit\s+commit\b/.test(normalized) &&
  !/\bGIT_HYGIENE_SKIP=1\b/.test(normalized)
) {
  // agent_message only; allow but nudge (branch check is in repo rules)
  allow({
    agent_message:
      "Before committing: run scripts/git/change-stats, use Conventional Commits, keep commits small. Do not commit on main.",
  });
  process.exit(0);
}

allow();
process.exit(0);
