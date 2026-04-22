#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PYTHON_DIR="$(realpath "$SCRIPT_DIR/../python")"

# Export PYTHONPATH so that ai_service and py_etl are discoverable
export PYTHONPATH="${PYTHON_DIR}:${PYTHONPATH:-}"

PYTHON_BIN="${PYTHON_BIN:-$ROOT_DIR/.venv/bin/python}"
if [[ ! -x "$PYTHON_BIN" ]]; then
  PYTHON_BIN="${PYTHON_BIN_FALLBACK:-python3}"
fi

HOST="${AI_SERVICE_HOST:-0.0.0.0}"
PORT="${AI_SERVICE_PORT:-8081}"
WORKERS="${AI_SERVICE_WORKERS:-2}"
# Default is production-safe; set AI_SERVICE_RELOAD=true for local hot-reload development.
RELOAD="${AI_SERVICE_RELOAD:-false}"

echo "🚀 Starting AI Inference API from $PYTHON_DIR..."

if [[ "$RELOAD" == "true" ]]; then
  exec "$PYTHON_BIN" -m uvicorn ai_service.main:app --host "$HOST" --port "$PORT" --reload
fi

exec "$PYTHON_BIN" -m uvicorn ai_service.main:app --host "$HOST" --port "$PORT" --workers "$WORKERS"
