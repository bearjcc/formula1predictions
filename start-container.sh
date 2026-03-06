#!/bin/bash
# Custom Railpack start script. Replaces the default PHP provider script so we run
# config:cache at runtime (for ADMIN_* etc.), app:ensure-admin-user, f1:ensure-season-data, one-time jobs
# (app:ensure-test-year-bot-predictions, app:merge-rb-racing-bulls-once, app:reset-bot-legacy-predictions)
# before starting FrankenPHP. See: https://railpack.com/languages/php

set -e

if [ "$IS_LARAVEL" = "true" ]; then
  if [ "$RAILPACK_SKIP_MIGRATIONS" != "true" ]; then
    echo "Running migrations and seeding database ..."
    php artisan migrate --force
  fi

  php artisan storage:link

  php artisan config:clear
  php artisan config:cache

  php artisan app:ensure-admin-user
  php artisan f1:ensure-season-data
  php artisan app:ensure-test-year-bot-predictions
  php artisan app:merge-rb-racing-bulls-once
  php artisan app:reset-bot-legacy-predictions

  php artisan optimize:clear
  php artisan optimize

  echo "Starting Laravel scheduler in background ..."
  php artisan schedule:work &

  echo "Starting Laravel server ..."
fi

exec docker-php-entrypoint --config /Caddyfile --adapter caddyfile 2>&1
