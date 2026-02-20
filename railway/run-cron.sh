#!/usr/bin/env bash
# Run Laravel scheduler every 60 seconds (for a dedicated Railway cron service).
# Usage: from repo root, ./railway/run-cron.sh
set -e
cd "$(dirname "$0")/.."

while true; do
  php artisan schedule:run --no-interaction
  sleep 60
done
