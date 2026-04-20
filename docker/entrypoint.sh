#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

php artisan storage:link >/dev/null 2>&1 || true

# Run package discovery (composer scripts are disabled during image build).
php artisan package:discover --ansi >/dev/null 2>&1 || true

# Ensure APP_KEY exists (Render env var is preferred).
if [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force >/dev/null 2>&1 || true
fi

# Cache for performance (safe to fail during first boot).
php artisan config:cache >/dev/null 2>&1 || true
php artisan route:cache >/dev/null 2>&1 || true
php artisan view:cache >/dev/null 2>&1 || true

# Run migrations on startup (recommended for Render).
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  php artisan migrate --force --no-interaction || true
fi

exec "$@"

