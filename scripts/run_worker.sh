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

QUEUE_NAME="${1:-${CELERY_QUEUES:-rag,ingestion,sync}}"
CONCURRENCY="${CELERY_CONCURRENCY:-1}"
LOGLEVEL="${CELERY_LOGLEVEL:-info}"

echo "🚀 Starting AI Celery Worker from $ROOT_DIR..."

exec "$PYTHON_BIN" -m celery -A python.celery_config.app worker \
  --loglevel="$LOGLEVEL" \
  --queues="$QUEUE_NAME" \
  --concurrency="$CONCURRENCY" \
  --hostname="ai-worker@%h"
