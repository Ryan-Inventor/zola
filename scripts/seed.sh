#!/usr/bin/env bash
# INF-04 — Migrations + seed initial (préparation AUTH-01)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

echo "==> Zola — migrate + seed"
php "$ROOT/scripts/ensure-database.php"
cd "$ROOT/zola-api"
php artisan migrate --force
php artisan db:seed --force
echo "==> Seed terminé."
