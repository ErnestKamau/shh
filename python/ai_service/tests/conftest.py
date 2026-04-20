"""
Shared pytest fixtures for the AI service test suite.
Provides mocked versions of OllamaService, LiveDataService,
RetrievalService, and VisualizationService so tests run
without external dependencies.
"""

import json
import os
import pytest
from unittest.mock import MagicMock, patch

# ── Mocked OllamaService ─────────────────────────────────────────────────

@pytest.fixture
def mock_ollama():
    """OllamaService mock that returns predictable responses."""
    ollama = MagicMock()
    ollama.model = "test-model"
    ollama.generate.return_value = "This is a test response."
    ollama.chat.return_value = {
        "message": {"content": "This is a test response."}
    }
    return ollama


# ── Mocked LiveDataService ───────────────────────────────────────────────

@pytest.fixture
def mock_live_data():
    """LiveDataService mock with a real manifest loaded."""
    live_data = MagicMock()

    # Load the real manifest so routing logic can enumerate intents
    manifest_path = os.path.join(
        os.path.dirname(__file__),
        "..", "core", "live_data_manifest.json",
    )
    with open(os.path.abspath(manifest_path), "r") as f:
        manifest = json.load(f)

    live_data.templates = manifest

    # Default: execute_step returns a successful count result
    live_data.execute_step.return_value = {
        "intent": "sample_count_total",
        "data": [{"n": 248}],
        "summary": "There are **248** total samples (batches) logged in system.",
        "success": True,
        "value": 248,
        "sql": "SELECT COUNT(*) ...",
        "execution_latency_ms": 12,
    }

    # _resolve_template should return None by default (no visualization)
    live_data._resolve_template.return_value = None

    return live_data


# ── Mocked RetrievalService ──────────────────────────────────────────────

@pytest.fixture
def mock_retrieval():
    """RetrievalService mock — returns empty by default."""
    retrieval = MagicMock()
    retrieval.search.return_value = []
    return retrieval


# ── Mocked VisualizationService ──────────────────────────────────────────

@pytest.fixture
def mock_visualizer():
    """VisualizationService mock — returns empty string by default."""
    visualizer = MagicMock()
    visualizer.generate_chart_block.return_value = ""
    return visualizer


# ── SimpleAssistant with mocked dependencies ────────────────────────────

@pytest.fixture
def assistant(mock_ollama, mock_live_data, mock_retrieval, mock_visualizer):
    """Fully mocked SimpleAssistant for unit testing."""
    # Patch the request_logger to avoid DB writes during tests
    with patch("python.ai_service.core.simple_assistant.request_logger") as mock_logger:
        mock_logger.log = MagicMock()
        from python.ai_service.core.simple_assistant import SimpleAssistant
        sa = SimpleAssistant(mock_ollama, mock_live_data, mock_retrieval, mock_visualizer)
        yield sa


# ── Manifest path helper ────────────────────────────────────────────────

@pytest.fixture
def manifest_path():
    """Absolute path to the live_data_manifest.json."""
    return os.path.abspath(
        os.path.join(
            os.path.dirname(__file__),
            "..", "core", "live_data_manifest.json",
        )
    )
