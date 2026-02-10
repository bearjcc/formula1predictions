#!/usr/bin/env bash

set -euo pipefail

# Simple deployment script for the Formula1Predictions app.
# Intended to be run on the production server from the project root.
#
# Responsibilities:
# - Pull latest code from the main branch
# - Install PHP dependencies without dev packages
# - Run database migrations
# - Build frontend assets
# - Warm Laravel caches
#
# Usage:
#   ./deploy.sh
#
# Assumptions:
# - APP_ENV is set to production
# - .env is already configured on the server
# - The current user has permission to run composer, npm, and php artisan

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo ">>> Starting deployment in $PROJECT_DIR"

if command -v git >/dev/null 2>&1; then
  echo ">>> Pulling latest code from origin/main"
  git fetch origin main
  git reset --hard origin/main
else
  echo "!!! git not found; skipping code update"
fi

if command -v composer >/dev/null 2>&1; then
  echo ">>> Installing PHP dependencies (no-dev, optimized autoloader)"
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
else
  echo "!!! composer not found; skipping PHP dependency installation"
fi

echo ">>> Running database migrations"
php artisan migrate --force --no-interaction

if command -v npm >/dev/null 2>&1; then
  if [ -f package-lock.json ]; then
    echo ">>> Installing JS dependencies with npm ci"
    npm ci
  else
    echo ">>> Installing JS dependencies with npm install"
    npm install
  fi

  echo ">>> Building frontend assets"
  npm run build
else
  echo "!!! npm not found; skipping frontend build"
fi

echo ">>> Clearing and caching application configuration"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Deployment completed successfully."

