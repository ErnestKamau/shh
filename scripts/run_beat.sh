#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PYTHON_DIR="$(realpath "$SCRIPT_DIR/../python")"

# Export PYTHONPATH so that the 'python' package is discoverable
export PYTHONPATH="${ROOT_DIR}:${PYTHONPATH:-}"

PYTHON_BIN="${PYTHON_BIN:-$ROOT_DIR/.venv/bin/python}"
if [[ ! -x "$PYTHON_BIN" ]]; then
  PYTHON_BIN="${PYTHON_BIN_FALLBACK:-python3}"
fi

LOGLEVEL="${CELERY_LOGLEVEL:-info}"

echo "🚀 Starting AI Celery Beat from $ROOT_DIR..."

exec "$PYTHON_BIN" -m celery -A python.celery_config.app beat --loglevel="$LOGLEVEL"
