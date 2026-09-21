#!/usr/bin/env bash
set -e
cd /var/www/html
[ -d vendor ] || composer install --no-interaction --prefer-dist
[ -d node_modules ] || npm install
# Vite in background (for HMR), artisan serve in foreground.
npm run dev -- --host 0.0.0.0 --port 5173 &
exec php artisan serve --host 0.0.0.0 --port 8000
