"""
Test Suite: Intent Routing Accuracy

Verifies the tiered routing strategy:
  1. Greetings → conversational (skip SQL + RAG)
  2. Keyword/rule matches → correct SQL intent
  3. LLM fallback → when rules miss
  4. RAG fallback → when no SQL match
  5. Error handling → clean responses on failure
"""

import pytest
from unittest.mock import MagicMock, patch


# ── Greeting Short-Circuit ────────────────────────────────────────────────

class TestGreetingDetection:
    """Greetings should skip SQL and RAG entirely."""

    @pytest.mark.parametrize("greeting", [
        "Hello",
        "Hi there",
        "Good morning",
        "Hey",
        "Thanks",
        "How are you",
        "Cheers",
    ])
    def test_greeting_routes_to_conversational(self, assistant, greeting):
        result = assistant.process_query(greeting)
        assert result["meta"]["route"] == "conversational"
        assert result["meta"]["routing_tier"] == "greeting"

    def test_greeting_does_not_invoke_sql(self, assistant, mock_live_data):
        assistant.process_query("Hello")
        mock_live_data.execute_step.assert_not_called()

    def test_greeting_does_not_invoke_rag(self, assistant, mock_retrieval):
        assistant.process_query("Hi there")
        mock_retrieval.search.assert_not_called()


# ── Keyword Routing (Group A) ────────────────────────────────────────────

class TestKeywordRouting:
    """Group A routes should match deterministically via keywords."""

    @pytest.mark.parametrize("query,expected_intent", [
        ("How many samples are in the lab?", "sample_count_in_lab"),
        ("samples in the lab", "sample_count_in_lab"),
        ("total samples", "sample_count_total"),
        ("how many samples today", "sample_count_today"),
        ("samples this month", "sample_count_this_month"),
        ("samples by status", "samples_by_status"),
        ("rejected samples count", "samples_rejected"),
        ("latest received batch", "latest_received_batches"),
        ("low stock items", "inventory_low_stock"),
        ("maintenance schedule", "equipment_maintenance_schedule"),
        ("equipment downtime", "equipment_downtime_summary"),
        ("pending capa", "capa_pending"),
        ("average turnaround time", "tat_overall_average"),
        ("overdue batches", "tat_overdue_batches"),
        ("open complaints", "complaint_count_open"),
        ("top clients", "top_clients_by_volume"),
        ("analyst workload today", "analyst_workload_today"),
        ("how many samples are verified", "sample_count_verified"),
        ("how many samples are approved", "sample_count_approved"),
        ("how many samples request review", "sample_count_request_review"),
    ])
    def test_keyword_routes_to_correct_intent(self, assistant, query, expected_intent):
        result = assistant.process_query(query)
        assert result["meta"]["route"] == f"sql:{expected_intent}"
        assert result["meta"]["routing_tier"] == "keyword"

    def test_keyword_route_does_not_invoke_llm(self, assistant, mock_ollama):
        """Keyword-matched queries should NOT call the LLM for classification."""
        assistant.process_query("How many samples in the lab?")
        # generate() should not be called for routing — only for conversational
        mock_ollama.generate.assert_not_called()


# ── LLM Fallback Routing ─────────────────────────────────────────────────

class TestLlmFallbackRouting:
    """Queries that don't match keywords should fall back to LLM classification."""

    def test_llm_fallback_when_no_keyword_match(self, assistant, mock_ollama):
        """An ambiguous query should trigger LLM classification."""
        # Make LLM return a valid intent
        mock_ollama.generate.return_value = "sample_count_total"

        result = assistant.process_query("Tell me the overall numbers for our lab operations")
        assert result["meta"]["routing_tier"] == "llm"
        assert "sql:" in result["meta"]["route"]

    def test_llm_timeout_falls_to_rag(self, assistant, mock_ollama, mock_retrieval):
        """If LLM classification times out, should fall through to RAG/chat."""
        import concurrent.futures
        mock_ollama.generate.side_effect = concurrent.futures.TimeoutError()
        mock_retrieval.search.return_value = []  # No RAG hits either

        result = assistant.process_query("What is the operational efficiency metric?")
        # Should fall through to conversational since LLM timed out and no RAG
        assert result["meta"]["route"] == "conversational"


# ── RAG Routing ───────────────────────────────────────────────────────────

class TestRagRouting:
    """Knowledge base queries should route to RAG when SQL doesn't match."""

    def test_rag_route_for_sop_question(self, assistant, mock_ollama, mock_retrieval):
        """SOP questions should not match SQL keywords and should hit RAG."""
        # LLM returns no match
        mock_ollama.generate.return_value = "none"

        # RAG returns chunks
        mock_retrieval.search.return_value = [
            {
                "content": "SOP-001: Sample handling requires gloves.",
                "collection_name": "compliance",
                "entity_type": "SOP",
                "entity_id": 1,
            }
        ]

        result = assistant.process_query("What does the SOP say about sample handling?")
        assert result["meta"]["route"] == "rag"
        assert len(result["sources"]) > 0


# ── Error Handling ────────────────────────────────────────────────────────

class TestErrorHandling:
    """Errors should produce clean, user-friendly responses."""

    def test_db_failure_returns_clean_message(self, assistant, mock_live_data):
        """DB connection failure should return a helpful message, not a traceback."""
        mock_live_data.execute_step.side_effect = ConnectionError("Connection refused")

        result = assistant.process_query("How many samples in the lab?")
        assert "temporarily unavailable" in result["answer"]
        assert "sql:" in result["meta"]["route"]

    def test_zero_result_sql_handled_cleanly(self, assistant, mock_live_data):
        """SQL returning zero rows should give a meaningful message."""
        mock_live_data.execute_step.return_value = {
            "intent": "samples_rejected",
            "data": [],
            "summary": "No matching records were found for 'Rejected or cancelled samples'.",
            "success": True,
            "value": None,
        }

        result = assistant.process_query("How many rejected samples?")
        assert "No matching records" in result["answer"] or result["meta"]["route"].startswith("sql:")

    def test_sql_execution_failure_returns_clean_message(self, assistant, mock_live_data):
        """SQL execution error should return a user-friendly message."""
        mock_live_data.execute_step.return_value = {
            "error": "relation does not exist",
            "success": False,
            "summary": "The reporting database is currently under heavy load.",
        }

        result = assistant.process_query("total samples")
        # Should have some answer, not crash
        assert result["answer"] != ""
        assert result["meta"]["route"].startswith("sql:")


# ── Response Metadata ─────────────────────────────────────────────────────

class TestResponseMetadata:
    """Responses should include proper metadata for observability."""

    def test_response_includes_trace_id(self, assistant):
        result = assistant.process_query("Hello", trace_id="test-trace-123")
        assert result["meta"]["trace_id"] == "test-trace-123"

    def test_response_includes_latency(self, assistant):
        result = assistant.process_query("Hello")
        assert "latency_ms" in result["meta"]
        assert isinstance(result["meta"]["latency_ms"], int)

    def test_response_includes_routing_tier(self, assistant):
        result = assistant.process_query("total samples")
        assert result["meta"]["routing_tier"] in ("keyword", "keyword_loose", "llm", "rag", "fallback", "greeting", "error")
