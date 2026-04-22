#!/bin/bash
# Imara AI Standalone Service Runner
# Runs the FastAPI service on port 8081

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Export PYTHONPATH so that ai_service and py_etl are discoverable
export PYTHONPATH="${SCRIPT_DIR}:${PYTHONPATH}"

# Navigate to the script directory
cd "${SCRIPT_DIR}"

echo "🚀 Starting Imara AI Service..."
echo "📍 PYTHONPATH: ${PYTHONPATH}"

# Run uvicorn
# You can customize host/port via env vars AI_SERVICE_HOST / AI_SERVICE_PORT
exec uvicorn ai_service.main:app --host 0.0.0.0 --port 8081 --log-level info
