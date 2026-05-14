#!/usr/bin/env bash
set -euo pipefail

# start_ai.sh - Orchestrates starting AI services (Inference API, Workers)
# Inspired by the KEBS automation patterns

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [[ ! -f ".venv/bin/activate" ]]; then
    echo "❌ Error: Virtual environment not found at $ROOT_DIR/.venv"
    echo "Please run: ./scripts/install_python.sh"
    exit 1
fi

echo "-------------------------------------------------------"
echo "🚀 Starting Imara AI Services"
echo "-------------------------------------------------------"

# Activate environment
source .venv/bin/activate

# Export PYTHONPATH so modules are discoverable
export PYTHONPATH="${ROOT_DIR}:${PYTHONPATH:-}"

# Service Configuration
HOST="${AI_SERVICE_HOST:-127.0.0.1}"
PORT="${AI_SERVICE_PORT:-8081}"
LOG_LEVEL="${AI_LOG_LEVEL:-info}"

echo "📍 Host: $HOST"
echo "📍 Port: $PORT"
echo "📍 PYTHONPATH: $PYTHONPATH"

# Check if we should run in background or foreground
MODE="${1:-foreground}"

if [[ "$MODE" == "background" ]]; then
    echo "🌙 Starting AI Inference API in background..."
    nohup python -m uvicorn python.ai_service.main:app --host "$HOST" --port "$PORT" --log-level "$LOG_LEVEL" > /tmp/imara-ai-api.log 2>&1 &
    echo "✅ API started (PID: $!). Logs at /tmp/imara-ai-api.log"
else
    echo "🔥 Starting AI Inference API in foreground..."
    exec python -m uvicorn python.ai_service.main:app --host "$HOST" --port "$PORT" --log-level "$LOG_LEVEL"
fi
