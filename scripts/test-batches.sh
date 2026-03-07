#!/usr/bin/env bash
# Run discovered Unit/Feature suites in two batches (same as scripts/test-batches.ps1; for CI and Unix).
# Usage: from repo root, ./scripts/test-batches.sh
set -e
cd "$(dirname "$0")/.."

php ./scripts/run-test-batches.php --batches=2
