import json
import logging
import uuid
import asyncio
from typing import List, Dict, Any, Optional
from fastapi import APIRouter, HTTPException, Depends
from fastapi.responses import StreamingResponse
from python.ai_service.schemas.chat import ChatRequest, ChatResponse, ChatStreamRequest
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.services.live_data_service import LiveDataService
from python.ai_service.services.visualization_service import visualization_service
from python.ai_service.core.orchestrator import AIOrchestrator
from python.ai_service.workers.manifest_worker import ManifestWorker
from python.ai_service.workers.dynamic_sql_worker import DynamicSqlWorker
from python.ai_service.workers.rag_worker import RagWorker
from python.ai_service.workers.chat_worker import ChatWorker
from python.ai_service.config.settings import settings

router = APIRouter(tags=["Chat"], prefix="/v1")
logger = logging.getLogger(__name__)

# Singletons/Components
ollama_service     = OllamaService()
retrieval_service  = RetrievalService()
live_data_service  = LiveDataService()

# ── Workers ───────────────────────────────────────────────────────────────────
manifest_worker    = ManifestWorker(live_data_service, visualization_service, ollama_service)
dynamic_sql_worker = DynamicSqlWorker(ollama_service, visualization_service)
rag_worker         = RagWorker(retrieval_service, ollama_service)
chat_worker        = ChatWorker(ollama_service)

# ── Orchestrator (single entry point for all queries) ─────────────────────────
orchestrator = AIOrchestrator(
    manifest_worker=manifest_worker,
    dynamic_sql_worker=dynamic_sql_worker,
    rag_worker=rag_worker,
    chat_worker=chat_worker,
)

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
    """AI Orchestrator — JSON response endpoint."""
    last_message = request.messages[-1]["content"] if request.messages else ""
    if not last_message:
        raise HTTPException(status_code=400, detail="Empty message")

    result = await orchestrator.route_and_process(
        last_message,
        request.messages,
        request.company_id,
        request.use_visuals,
        request.model,
        trace_id=request.trace_id,
        user_id=request.user_id,
        session_id=request.session_id,
        module_context=request.module_context,
        mode=request.mode,
        portal_user_id=request.portal_user_id,
        crm_customer_id=request.crm_customer_id,
        user_data_snapshot=request.user_data_snapshot,
    )

    return ChatResponse(
        reply=result["answer"],
        sources=result["sources"],
        model_version=ollama.model,
        retrieval_count=len(result["sources"]),
        decision=None, 
        decision_trace={
            "route": result["meta"]["route"],
            "orchestration_path": result["meta"].get("orchestration_path", []),
            "meta": result["meta"]
        }
    )

@router.post("/chat/stream")
async def chat_stream(
    request: ChatStreamRequest,
):
    """AI Orchestrator — Server-Sent Events streaming endpoint."""
    logger.info(f"CHAT_STREAM: Received request trace_id={request.trace_id}")
    trace_id = request.trace_id or str(uuid.uuid4())

    async def event_generator():
        yield f"data: {json.dumps({'kind': 'upstream_connected', 'trace_id': trace_id})}\n\n"
        
        async for chunk in orchestrator.route_and_stream(
            request.messages[-1]["content"] if request.messages else "",
            request.messages,
            request.company_id,
            request.use_visuals,
            request.model,
            trace_id=trace_id,
            user_id=request.user_id,
            session_id=request.session_id,
            module_context=request.module_context,
            mode=request.mode,
            portal_user_id=request.portal_user_id,
            crm_customer_id=request.crm_customer_id,
            user_data_snapshot=request.user_data_snapshot,
        ):
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
