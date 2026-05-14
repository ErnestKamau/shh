#!/usr/bin/env bash
set -euo pipefail

# install_python.sh - Sets up the Python virtual environment for Imara AI
# Inspired by the KEBS automation patterns

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

echo "-------------------------------------------------------"
echo "🚀 Initializing Imara AI Python Environment"
echo "-------------------------------------------------------"

# Check for Python 3.10+
if ! command -v python3 &> /dev/null; then
    echo "❌ Error: python3 not found. Please install Python 3.10 or higher."
    exit 1
fi

PYTHON_VERSION=$(python3 -c 'import sys; print(".".join(map(str, sys.version_info[:2])))')
echo "📍 Detected Python version: $PYTHON_VERSION"

if ! python3 -c 'import sys; exit(0 if sys.version_info >= (3, 10) else 1)'; then
    echo "❌ Error: Python 3.10+ is required."
    exit 1
fi

# Create Virtual Environment
if [[ ! -d ".venv" ]]; then
    echo "📦 Creating virtual environment in $ROOT_DIR/.venv..."
    python3 -m venv .venv
    echo "✅ Virtual environment created."
else
    echo "ℹ️  Virtual environment already exists."
fi

# Install dependencies
echo "📥 Installing/Updating dependencies from python/requirements.txt..."
source .venv/bin/activate

# Use a faster mirror if needed, or just standard pip
pip install --upgrade pip

if [[ -f "python/requirements.txt" ]]; then
    # We use --extra-index-url in requirements.txt for torch+cpu
    pip install -r python/requirements.txt
    echo "✅ Dependencies installed successfully."
else
    echo "⚠️  Warning: python/requirements.txt not found. Skipping dependency installation."
fi

echo "-------------------------------------------------------"
echo "🎉 Python environment is ready!"
echo "To activate manually: source .venv/bin/activate"
echo "-------------------------------------------------------"
