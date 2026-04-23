#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

HOST="${LARAVEL_HOST:-127.0.0.1}"
PORT="${LARAVEL_PORT:-8000}"

exec php artisan serve --host="$HOST" --port="$PORT"
