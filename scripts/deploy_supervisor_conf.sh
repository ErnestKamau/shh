#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEMPLATE_PATH="$ROOT_DIR/python/supervisor.ai_service.conf.template"
OUTPUT_PATH="$ROOT_DIR/python/supervisor.ai_service.conf"

if [[ ! -f "$TEMPLATE_PATH" ]]; then
  echo "Template not found: $TEMPLATE_PATH"
  exit 1
fi

PROJECT_ROOT="${PROJECT_ROOT:-$ROOT_DIR}"
SCRIPTS_DIR="${SCRIPTS_DIR:-$PROJECT_ROOT/scripts}"
PYTHON_DIR="${PYTHON_DIR:-$PROJECT_ROOT/python}"
LOG_DIR="${LOG_DIR:-$PROJECT_ROOT/python/ai_service}"

mkdir -p "$LOG_DIR"

sed \
  -e "s|{{PROJECT_ROOT}}|$PROJECT_ROOT|g" \
  -e "s|{{SCRIPTS_DIR}}|$SCRIPTS_DIR|g" \
  -e "s|{{PYTHON_DIR}}|$PYTHON_DIR|g" \
  -e "s|{{LOG_DIR}}|$LOG_DIR|g" \
  "$TEMPLATE_PATH" > "$OUTPUT_PATH"

echo "Generated: $OUTPUT_PATH"
echo "PROJECT_ROOT=$PROJECT_ROOT"
echo "SCRIPTS_DIR=$SCRIPTS_DIR"
echo "PYTHON_DIR=$PYTHON_DIR"
echo "LOG_DIR=$LOG_DIR"
