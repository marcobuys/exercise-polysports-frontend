#!/usr/bin/env bash
set -euo pipefail

echo "▶ Starting containers (first run pulls images, builds, and installs deps — a few minutes)..."
docker compose up -d --build

echo "▶ Preparing environment..."
docker compose exec -T app sh -c '[ -f .env ] || cp .env.example .env'
docker compose exec -T app php artisan key:generate --force

echo "▶ Waiting for the app to come online..."
for i in $(seq 1 60); do
  if curl -fsS http://localhost:8000/up >/dev/null 2>&1; then break; fi
  sleep 3
done

echo "▶ Migrating and seeding the database..."
docker compose exec -T app php artisan migrate --seed --force

echo ""
echo "✅ PolySports is ready."
echo "   App:  http://localhost:8000"
echo "   API:  http://localhost:8000/api/products"
