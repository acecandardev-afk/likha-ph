#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache || true

# Ensure runtime write permissions (Apache runs as www-data)
chown -R www-data:www-data storage bootstrap/cache >/dev/null 2>&1 || true
chmod -R ug+rwX storage bootstrap/cache >/dev/null 2>&1 || true

if [ ! -f .env ] && [ -f .env.example ]; then
  cp .env.example .env
fi

# Render provides a HTTPS external URL. If APP_URL is missing or still http://,
# force Laravel + Vite to generate HTTPS asset URLs to avoid mixed-content blocking.
if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
  if [ -z "${APP_URL:-}" ] || [[ "${APP_URL}" == http://* ]]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
  fi
  if [ -z "${ASSET_URL:-}" ] || [[ "${ASSET_URL}" == http://* ]]; then
    export ASSET_URL="${RENDER_EXTERNAL_URL}"
  fi
  if [ -z "${VITE_ASSET_URL:-}" ] || [[ "${VITE_ASSET_URL}" == http://* ]]; then
    export VITE_ASSET_URL="${RENDER_EXTERNAL_URL}"
  fi
fi

# Render provides a dynamic PORT environment variable for Docker web services.
# Update Apache config at runtime so it listens on the correct port.
if [ -n "${PORT:-}" ]; then
  sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
  sed -ri "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/*.conf
fi

php artisan storage:link >/dev/null 2>&1 || true

# Run package discovery (composer scripts are disabled during image build).
php artisan package:discover --ansi >/dev/null 2>&1 || true

# Ensure APP_KEY exists (Render env var is preferred).
if [ -z "${APP_KEY:-}" ]; then
  php artisan key:generate --force >/dev/null 2>&1 || true
fi

# Clear stale caches before rebuilding.
php artisan config:clear >/dev/null 2>&1 || true
php artisan route:clear >/dev/null 2>&1 || true
php artisan view:clear >/dev/null 2>&1 || true

# Cache for performance (safe to fail during first boot).
php artisan config:cache >/dev/null 2>&1 || true
php artisan route:cache >/dev/null 2>&1 || true
php artisan view:cache >/dev/null 2>&1 || true

# Run migrations on startup (recommended for Render) with a timeout to avoid hanging if DB is unavailable.
if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
  if command -v timeout >/dev/null 2>&1; then
    timeout 30s php artisan migrate --force --no-interaction >/dev/null 2>&1 || true
  else
    php artisan migrate --force --no-interaction >/dev/null 2>&1 || true
  fi
fi

if [ "${RUN_ADDRESS_SEEDER:-1}" = "1" ]; then
  php artisan db:seed --class="Database\\Seeders\\PhilippineAddressSeeder" --force --no-interaction >/dev/null 2>&1 || true
  php artisan cache:clear >/dev/null 2>&1 || true
fi

exec "$@"

