#!/usr/bin/env bash
set -euo pipefail

# Retry a command quietly until it succeeds or we run out of attempts.
# usage: retry <attempts> <sleep-seconds> <command...>
retry() {
  local attempts="$1" delay="$2"; shift 2
  local i=1
  until "$@" >/dev/null 2>&1; do
    if [ "$i" -ge "$attempts" ]; then
      return 1
    fi
    i=$((i + 1))
    sleep "$delay"
  done
  return 0
}

echo "▶ Starting containers (first run pulls images, builds, and installs deps — a few minutes)..."
docker compose up -d --build

# On the very first run the container is still installing PHP + Node dependencies
# in the background, so `artisan` isn't usable yet. Wait for it before doing
# anything else — this is what used to make the first run fail and need a re-run.
echo "▶ Waiting for the app container to finish installing dependencies (first run only — can take a few minutes)..."
if ! retry 120 5 docker compose exec -T app php artisan --version; then
  echo "✗ The app container did not become ready in time." >&2
  echo "  Watch progress with:  docker compose logs -f app" >&2
  exit 1
fi

echo "▶ Preparing environment (.env + application key)..."
docker compose exec -T app sh -c '[ -f .env ] || cp .env.example .env'
docker compose exec -T app sh -c 'grep -q "^APP_KEY=base64:" .env || php artisan key:generate --force'

# MySQL reports "healthy" before it is actually ready to accept our app's
# connections, so wait until the app can really open a database connection.
echo "▶ Waiting for MySQL to accept connections..."
if ! retry 60 5 docker compose exec -T app php artisan db:show; then
  echo "✗ Could not connect to MySQL in time." >&2
  echo "  Watch progress with:  docker compose logs -f mysql" >&2
  exit 1
fi

echo "▶ Migrating and seeding the database..."
# Idempotent: already-applied migrations are skipped, and the seeder no-ops when
# data already exists — so re-running ./up.sh is always safe.
retry 10 3 docker compose exec -T app php artisan migrate --seed --force \
  || docker compose exec -T app php artisan migrate --seed --force

echo "▶ Waiting for the app to come online..."
retry 40 3 curl -fsS http://localhost:8000/up || true

echo ""
echo "✅ PolySports is ready."
echo "   App:  http://localhost:8000"
echo "   API:  http://localhost:8000/api/products"
