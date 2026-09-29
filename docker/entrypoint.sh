#!/usr/bin/env bash
set -e
cd /var/www/html
[ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist
[ -d node_modules ] || npm install

# Make sure the app can boot cleanly even before ./up.sh finishes — otherwise a
# missing .env / APP_KEY shows up as a white screen if the browser is opened early.
[ -f .env ] || cp .env.example .env
grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force

# Vite in background (for HMR), artisan serve in foreground.
npm run dev -- --host 0.0.0.0 --port 5173 &
exec php artisan serve --host 0.0.0.0 --port 8000
