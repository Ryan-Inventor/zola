#!/usr/bin/env bash
# INF-04 — Démarre l'environnement de dev local (MySQL + API + Frontend) en une commande
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"

wait_for_http() {
  local url="$1"
  local max="${2:-60}"
  local i=0
  while [ "$i" -lt "$max" ]; do
    if curl -fsS -o /dev/null "$url" 2>/dev/null; then
      return 0
    fi
    sleep 2
    i=$((i + 2))
  done
  return 1
}

echo "==> Zola — environnement de développement"
"$ROOT/scripts/seed.sh"

if ! lsof -i :8000 -sTCP:LISTEN >/dev/null 2>&1; then
  echo "==> Démarrage API (port 8000)..."
  (cd "$ROOT/zola-api" && php artisan serve --host=127.0.0.1 --port=8000) &
fi

if ! lsof -i :3000 -sTCP:LISTEN >/dev/null 2>&1; then
  echo "==> Démarrage Frontend (port 3000)..."
  (cd "$ROOT/zola-web" && npm run dev) &
fi

echo "==> Attente des services..."
API_OK=0
WEB_OK=0
wait_for_http "http://127.0.0.1:8000/api/v1/test" && API_OK=1 || true
wait_for_http "http://127.0.0.1:3000/" && WEB_OK=1 || true

if [ "$API_OK" -eq 1 ] && [ "$WEB_OK" -eq 1 ]; then
  echo ""
  echo "✅ Environnement Zola opérationnel"
  echo "   API : http://localhost:8000/api/v1"
  echo "   Web : http://localhost:3000"
  exit 0
fi

echo ""
echo "❌ Échec du démarrage (API=$API_OK, Web=$WEB_OK)"
exit 1
