import json
import logging
import uuid
import asyncio
from typing import List, Dict, Any, Optional
from fastapi import APIRouter, HTTPException, Depends
from fastapi.responses import StreamingResponse
from ai_service.schemas.chat import ChatRequest, ChatResponse, ChatStreamRequest
from ai_service.services.ollama_service import OllamaService
from ai_service.services.retrieval_service import RetrievalService
from ai_service.services.live_data_service import LiveDataService
from ai_service.services.visualization_service import visualization_service
from ai_service.core.simple_assistant import SimpleAssistant
from ai_service.core.intermediate_assistant import IntermediateAssistant
from ai_service.config.settings import settings

router = APIRouter(tags=["Chat"], prefix="/v1")
logger = logging.getLogger(__name__)

# Singletons/Components
ollama_service = OllamaService()
retrieval_service = RetrievalService()
live_data_service = LiveDataService()
simple_assistant = SimpleAssistant(ollama_service, live_data_service, retrieval_service, visualization_service)
intermediate_assistant = IntermediateAssistant(ollama_service, simple_assistant)

# Dependency Helpers
def get_ollama_service():
    return ollama_service

def get_retrieval_service():
    return retrieval_service

@router.post("/chat")
async def chat(
    request: ChatRequest,
    ollama: OllamaService = Depends(get_ollama_service),
):
    """Simplified Operational Assistant (JSON)."""
    last_message = request.messages[-1]["content"] if request.messages else ""
    if not last_message:
        raise HTTPException(status_code=400, detail="Empty message")

    # Call the intermediate assistant which orchestrates between FastPath and Operational RAG
    result = await intermediate_assistant.process_query(
        last_message, 
        request.messages,
        request.company_id,
        request.use_visuals,
        request.model,
        trace_id=request.trace_id,
        user_id=request.user_id,
        session_id=request.session_id,
        module_context=request.module_context,
    )

    return ChatResponse(
        reply=result["answer"],
        sources=result.get("sources", []),
        model_version=ollama.model,
        retrieval_count=len(result.get("sources", [])),
        decision=None, 
        decision_trace={
            "route": result.get("meta", {}).get("route", "unknown"),
            "meta": result.get("meta", {})
        }
    )

@router.post("/chat/stream")
async def chat_stream(
    request: ChatStreamRequest,
):
    """Real-time streaming assistant (Token-by-token)."""
    trace_id = request.trace_id or str(uuid.uuid4())
    last_message = request.messages[-1]["content"] if request.messages else ""

    async def event_generator():
        yield f"data: {json.dumps({'kind': 'upstream_connected', 'trace_id': trace_id})}\n\n"
        
        # Use the intermediate assistant for real streaming
        async for chunk in intermediate_assistant.process_query_stream(
            last_message,
            request.messages,
            request.company_id,
            request.use_visuals,
            request.model,
            trace_id=request.trace_id or trace_id,
            user_id=request.user_id,
            session_id=request.session_id,
            module_context=request.module_context,
        ):
            # Output chunks compatible with frontend expectations
            # Most frontends expect 'token' or 'kind'
            yield f"data: {json.dumps(chunk)}\n\n"
        
        yield "data: [DONE]\n\n"

    return StreamingResponse(
        event_generator(),
        media_type="text/event-stream",
        headers={
            "Cache-Control": "no-cache",
            "Connection": "keep-alive",
            "X-Accel-Buffering": "no",
        }
    )
