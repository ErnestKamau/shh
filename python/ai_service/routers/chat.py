import json
import logging
import uuid
import time
import asyncio
from functools import partial
from typing import List, Dict, Any, Optional
from fastapi import APIRouter, HTTPException, Request, Depends
from fastapi.responses import StreamingResponse
from python.ai_service.schemas.chat import ChatRequest, ChatResponse, ChatStreamRequest
from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.core.query_classifier import QueryClassifier

from python.ai_service.core.retrieval_planner import RetrievalPlanner
from python.ai_service.core.abstention_policy import AbstentionPolicy
from python.ai_service.core.context_assembler import ContextAssembler
from python.ai_service.core.query_decomposer import QueryDecomposer
from python.ai_service.core.grounding_verifier import GroundingVerifier
from python.ai_service.services.live_data_service import LiveDataService
from python.ai_service.config.settings import settings

router = APIRouter(tags=["Chat"], prefix="/v1")
logger = logging.getLogger(__name__)

# Singletons/Components
ollama_service = OllamaService()
query_decomposer = QueryDecomposer(ollama_service)
retrieval_planner = RetrievalPlanner()
abstention_policy = AbstentionPolicy()
context_assembler = ContextAssembler()
grounding_verifier = GroundingVerifier()
live_data_service = LiveDataService()

# Dependency Helpers
def get_ollama_service():
    from python.ai_service.services.ollama_service import OllamaService
    return OllamaService()

def get_retrieval_service():
    return RetrievalService()

@router.post("/chat", response_model=ChatResponse)
async def chat(
    request: ChatRequest,
    ollama: OllamaService = Depends(get_ollama_service),
    retrieval: RetrievalService = Depends(get_retrieval_service)
):
    """Industrialized Multi-hop RAG orchestration."""
    logger.info(f"Stage 3 Orchestration: company={request.company_id}")
    
    last_message = request.messages[-1]["content"] if request.messages else ""
    all_chunks = []
    data_summaries = []
    active_mode = settings.orchestration_mode
    
    if last_message:
        # 1. Decompose (Cognitive Dispatcher)
        graph = query_decomposer.decompose(last_message, mode=active_mode)
        logger.info(f"Orchestration: class={graph.query_class} mode={active_mode}")
        
        # 2. Execution Loop
        current_context_chunks = []
        for step in graph.steps:
            # Mode Enforcement & Policy Routing
            if step.type == "sql":
                if active_mode == "rag_only":
                    logger.warning(f"Blocking SQL step {step.id} due to rag_only mode.")
                    data_summaries.append("Note: I found that this query requires authoritative database counts, but I am currently restricted to RAG-only retrieval. I can only provide qualitative evidence from documents.")
                    continue
                
                # Execute SQL Tool (Python-side ported templates)
                sql_result = live_data_service.execute_step(intent=step.sub_query, params=step.sql_params)
                if sql_result.get("success"):
                    data_summaries.append(sql_result["summary"])
                else:
                    logger.error(f"SQL Step {step.id} failed: {sql_result.get('error')}")

            elif step.type == "rag":
                if active_mode == "live_data_only":
                    logger.warning(f"Blocking RAG step {step.id} due to live_data_only mode.")
                    continue

                # Standard RAG Flow
                search_plan = retrieval_planner.plan_step(step, current_context_chunks)
                step_chunks = retrieval.search(
                    query=search_plan.query,
                    limit=5,
                    collections=search_plan.collections,
                    metadata_filters={"company_id": request.company_id} if request.company_id else None,
                    mode=search_plan.mode,
                    candidate_limit=search_plan.candidate_limit
                )
                
                if abstention_policy.evaluate(step.sub_query, step_chunks, search_plan.abstention_logic, search_plan.exact_id):
                    all_chunks.extend(step_chunks)
                    current_context_chunks = step_chunks 
                else:
                    logger.warning(f"Hop {step.id} failed abstention policy.")
                    if not graph.is_multihop: break

    # 3. Assemble Context
    context_str = context_assembler.assemble(all_chunks)
    if data_summaries:
        data_header = "AUTHORITATIVE SYSTEM DATA (DETERMINISTIC):\n"
        data_body = "\n\n".join(data_summaries)
        context_str = f"{data_header}{data_body}\n\nRELEVANT DOCUMENT SEARCH (SEMANTIC):\n{context_str}"
    
    system_prompt = (
        "You are Imarachat AI, a LIMS assistant. "
        "Base conclusions ONLY on the provided evidence. "
        "Prioritize AUTHORITATIVE SYSTEM DATA for counts and totals. "
        "Explain your synthesis concisely and CITE entity IDs for documented claims. "
        "If evidence is incomplete or conflicts, mention it explicitly."
    )
    
    if context_str:
        request.messages.insert(0, {"role": "system", "content": f"{system_prompt}\n\n{context_str}"})
    else:
        # Improved Fallback: Clarify that NO semantic or deterministic evidence was found.
        fallback_msg = (
            "No specific database records or related documents were found for this query. "
            "If this is about real-time metrics, the data may not have been synced recently or the criteria yielded no matches. "
            "Please state that no matching records were found in the reporting system."
        )
        request.messages.insert(0, {"role": "system", "content": f"{system_prompt}\n\n{fallback_msg}"})

    # 4. Generate
    response = ollama.chat(request.messages)
    answer = response['message']['content']
    
    # 5. Verify Grounding
    verification = grounding_verifier.verify(answer, all_chunks)
    if not verification["is_grounded"]:
        logger.warning(f"Grounding Violations Detected: {verification['violations']}")
    
    return ChatResponse(
        reply=answer,
        sources=context_assembler.get_sources_metadata(all_chunks),
        model_version=response.get("model", "qwen2.5:3b"),
        retrieval_count=len(all_chunks)
    )

@router.post("/chat/stream")
async def chat_stream(
    raw_request: Request,
    request: ChatStreamRequest,
    ollama: OllamaService = Depends(get_ollama_service),
    retrieval: RetrievalService = Depends(get_retrieval_service)
):
    """Streaming industrialized Multi-hop RAG orchestration."""
    trace_id = request.trace_id or str(uuid.uuid4())
    start_time = time.time()
    logger.info(f"Stage 3 Streaming Orchestration: company={request.company_id}, trace_id={trace_id}")

    async def event_generator():
        try:
            # Emit an early event so upstream clients do not time out while
            # decomposition, live-data SQL, and retrieval are still running.
            yield f"data: {json.dumps({'kind': 'upstream_connected', 'trace_id': trace_id})}\n\n"
            await asyncio.sleep(0)

            last_message = request.messages[-1]["content"] if request.messages else ""
            all_chunks = []
            data_summaries = []
            active_mode = settings.orchestration_mode

            if last_message and not request.sources:
                yield f"data: {json.dumps({'kind': 'planning_started', 'trace_id': trace_id})}\n\n"
                await asyncio.sleep(0)
                graph = await asyncio.to_thread(query_decomposer.decompose, last_message, active_mode)

                current_context_chunks = []
                for step in graph.steps:
                    if await raw_request.is_disconnected():
                        logger.info(f"Client disconnected during orchestration: trace_id={trace_id}")
                        return

                    yield f"data: {json.dumps({'kind': 'step_started', 'trace_id': trace_id, 'step_id': step.id, 'step_type': step.type})}\n\n"
                    await asyncio.sleep(0)

                    if step.type == "sql":
                        if active_mode == "rag_only":
                            data_summaries.append("Note: Retrieval restricted to documents only. Exact totals not verified.")
                            continue

                        sql_result = await asyncio.to_thread(
                            live_data_service.execute_step,
                            step.sub_query,
                            step.sql_params,
                        )
                        if sql_result.get("success"):
                            data_summaries.append(sql_result["summary"])

                    elif step.type == "rag":
                        if active_mode == "live_data_only":
                            continue

                        search_plan = retrieval_planner.plan_step(step, current_context_chunks)
                        step_chunks = await asyncio.to_thread(
                            partial(
                                retrieval.search,
                                query=search_plan.query,
                                limit=5,
                                collections=search_plan.collections,
                                metadata_filters={"company_id": request.company_id} if request.company_id else None,
                                mode=search_plan.mode,
                                candidate_limit=search_plan.candidate_limit,
                            )
                        )

                        if abstention_policy.evaluate(step.sub_query, step_chunks, search_plan.abstention_logic, search_plan.exact_id):
                            all_chunks.extend(step_chunks)
                            current_context_chunks = step_chunks
                        else:
                            if not graph.is_multihop:
                                break

            context_str = context_assembler.assemble(all_chunks)
            if data_summaries:
                data_body = "\n\n".join(data_summaries)
                context_str = f"AUTHORITATIVE SYSTEM DATA:\n{data_body}\n\nRELEVANT DOCUMENT SEARCH:\n{context_str}"

            system_prompt = (
                "You are Imarachat AI, a LIMS assistant. "
                "Base conclusions ONLY on the provided evidence. "
                "Prioritize AUTHORITATIVE SYSTEM DATA for counts and totals. "
                "Explain your synthesis concisely and CITE entity IDs for documented claims. "
                "If evidence is incomplete or conflicts, mention it explicitly."
            )

            messages = list(request.messages)
            if context_str:
                messages.insert(0, {"role": "system", "content": f"{system_prompt}\n\n{context_str}"})
            else:
                # Improved Fallback for streaming
                fallback_msg = (
                    "No specific database records or related documents were found for this query. "
                    "If this is about real-time metrics, the data may not have been synced recently or the criteria yielded no matches. "
                    "Please state that no matching records were found in the reporting system."
                )
                messages.insert(0, {"role": "system", "content": f"{system_prompt}\n\n{fallback_msg}"})

            yield f"data: {json.dumps({'kind': 'generation_started', 'trace_id': trace_id})}\n\n"
            await asyncio.sleep(0)

            async for chunk in ollama.chat_stream(messages):
                if await raw_request.is_disconnected():
                    logger.info(f"Client disconnected: trace_id={trace_id}")
                    break
                
                content = chunk.get("message", {}).get("content", "")
                if content:
                    yield f"data: {json.dumps({'token': content})}\n\n"

            yield "data: [DONE]\n\n"
            logger.info(f"Stream completed: trace_id={trace_id}, duration={time.time()-start_time:.2f}s")
        except asyncio.CancelledError:
            logger.info(f"Stream cancelled: trace_id={trace_id}")
        except Exception as e:
            logger.error(f"Stream error: {e}")
            yield f"data: {json.dumps({'error': str(e)})}\n\n"

    return StreamingResponse(
        event_generator(),
        media_type="text/event-stream",
        headers={
            "Cache-Control": "no-cache",
            "Connection": "keep-alive",
            "X-Accel-Buffering": "no",
        }
    )
