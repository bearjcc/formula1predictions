#!/usr/bin/env bash
# Pre-push checks: audits, readiness, style, tests, build. Run from repo root: ./scripts/pre-push.sh
set -e
cd "$(dirname "$0")/.."

echo "=== Composer audit ==="
composer audit

echo "=== Readiness check ==="
php artisan app:check-ready

echo "=== Pint (dirty files) ==="
vendor/bin/pint --dirty --test

echo "=== Tests (two batches) ==="
./scripts/test-batches.sh

echo "=== Frontend build ==="
npm run build

echo "=== NPM audit (high/critical; optional) ==="
npm audit --audit-level=high || true

echo "Pre-push checks passed."
