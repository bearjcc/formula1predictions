#!/usr/bin/env node
/**
 * sessionStart: inject current git state + mandatory hygiene checklist for agents.
 */
import { execSync } from "node:child_process";

function git(cmd) {
  try {
    return execSync(`git ${cmd}`, { encoding: "utf8" }).trim();
  } catch {
    return "";
  }
}

const branch = git("branch --show-current") || "(detached)";
const hooksPath = git("config core.hooksPath");
const hooksOk = hooksPath === ".githooks";

let staged = "0";
let unstaged = "0";
try {
  const names = git("diff --cached --name-only");
  staged = String(names ? names.split("\n").filter(Boolean).length : 0);
  const unst = git("diff --name-only");
  unstaged = String(unst ? unst.split("\n").filter(Boolean).length : 0);
} catch {
  /* ignore */
}

const onMain = branch === "main" || branch === "master";
const warnings = [];
if (onMain) {
  warnings.push(
    `BLOCKER: You are on '${branch}'. Do not edit files until you run: git switch -c agent/<task-slug> OR .\\scripts\\agent-task-start.ps1 -Task "<slug>"`,
  );
}
if (!hooksOk) {
  warnings.push(
    "Install git hooks once: .\\scripts\\install-git-hooks.ps1 (Conventional Commits, commit size limits, Pint).",
  );
}

const lines = [
  "## Git hygiene (mandatory for all agents)",
  "",
  `Current branch: **${branch}** | staged files: ${staged} | unstaged files: ${unstaged}`,
  "",
  "Before any file edit:",
  "1. Not on main/master — use `agent/<slug>` branch or worktree (`scripts/agent-task-start.ps1`).",
  "2. One branch = one objective.",
  "",
  "While working:",
  "3. Run `scripts/git/change-stats.ps1` before each commit; stop and commit if diff is large.",
  "4. Never `git commit --no-verify`, never force-push main, never push directly to main.",
  "",
  "Commits:",
  "5. Only when user asked; message `type(scope): summary` or `checkpoint:` after tests pass.",
  "6. Small commits only (hooks block >25 files / >500 lines staged).",
  "",
  "Before done:",
  "7. Run tests for touched code; then `scripts/pre-push.ps1` before claiming complete.",
  "",
  ...warnings.map((w) => `**${w}**`),
];

process.stdout.write(
  JSON.stringify({
    additional_context: lines.join("\n"),
  }),
);
