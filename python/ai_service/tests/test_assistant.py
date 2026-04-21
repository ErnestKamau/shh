import pytest
import asyncio
from unittest.mock import MagicMock, patch
from ai_service.core.intermediate_assistant import IntermediateAssistant
from ai_service.services.ollama_service import OllamaService
from ai_service.core.simple_assistant import SimpleAssistant

@pytest.fixture
def mock_ollama():
    service = MagicMock(spec=OllamaService)
    service.model = "test-model"
    return service

@pytest.fixture
def mock_simple_assistant():
    mock = MagicMock(spec=SimpleAssistant)
    # Add intent_router to the mock and default is_greeting to False
    mock.intent_router = MagicMock()
    mock.intent_router.is_greeting.return_value = False
    return mock

@pytest.fixture
def assistant(mock_ollama, mock_simple_assistant):
    return IntermediateAssistant(mock_ollama, mock_simple_assistant)

@pytest.mark.asyncio
async def test_conversational_routing(assistant, mock_ollama, mock_simple_assistant):
    """Verify that a greeting query is delegated to SimpleAssistant priority."""
    mock_simple_assistant.intent_router.is_greeting.return_value = True
    mock_simple_assistant.process_query.return_value = {
        "answer": "Warm greetings! I am Imara AI.",
        "meta": {"route": "conversational", "routing_tier": "greeting"}
    }
    
    result = await assistant.process_query(
        message="hello assistant",
        messages=[{"role": "user", "content": "hello assistant"}]
    )
    
    # Ensure it delegated to simple assistant
    assert "Warm greetings" in result["answer"]
    mock_simple_assistant.process_query.assert_called_once()
    mock_ollama.generate.assert_not_called()

@pytest.mark.asyncio
async def test_operational_delegation(assistant, mock_ollama, mock_simple_assistant):
    """Verify that an operational query is delegated to SimpleAssistant."""
    mock_simple_assistant.process_query.return_value = {
        "answer": "You have 5 samples today.",
        "sources": [],
        "meta": {"route": "sql:samples_today"}
    }
    
    result = await assistant.process_query(
        message="samples today",
        messages=[{"role": "user", "content": "samples today"}]
    )
    
    assert result["meta"]["route"] == "sql:samples_today"
    assert "5 samples" in result["answer"]
    # Ensure SimpleAssistant WAS called
    mock_simple_assistant.process_query.assert_called_once()

@pytest.mark.asyncio
async def test_help_fast_path(assistant, mock_ollama):
    """Verify that 'what can you do' uses the IntermediateAssistant conversational path."""
    mock_ollama.generate.return_value = "I can help with lab data."
    
    result = await assistant.process_query(
        message="what can you do?",
        messages=[{"role": "user", "content": "what can you do?"}]
    )
    
    assert result["meta"]["route"] == "conversational_fast"

@pytest.mark.asyncio
async def test_error_fallback(assistant, mock_ollama, mock_simple_assistant):
    """Verify that an exception in SimpleAssistant returns the Safe Fallback."""
    mock_simple_assistant.process_query.side_effect = Exception("DB Connection Error")
    
    result = await assistant.process_query(
        message="samples today",
        messages=[{"role": "user", "content": "samples today"}]
    )
    
    assert result["meta"]["route"] == "safe_fallback"
    assert "trouble connecting" in result["answer"]
    assert "DB Connection Error" in result["meta"]["error"]

@pytest.mark.asyncio
async def test_streaming_generator(assistant, mock_ollama):
    """Verify the streaming generator yields expected status and data events for conversational path."""
    
    # Mock chat_stream to yield tokens
    async def mock_stream(*args, **kwargs):
        yield {"message": {"content": "I am a lab assistant."}}
    
    mock_ollama.chat_stream.side_effect = mock_stream
    
    events = []
    # Use a prompt that hits self.fast_path_patterns ('what can you do')
    async for event in assistant.process_query_stream(
        message="what can you do?",
        messages=[{"role": "user", "content": "what can you do?"}]
    ):
        events.append(event)
        
    # Check sequence of events
    assert events[0]["kind"] == "status" # Analyzing
    assert events[1]["kind"] == "status" # Ready
    assert events[2]["kind"] == "token"
    assert events[2]["token"] == "I am a lab assistant."
    assert events[3]["kind"] == "done"
