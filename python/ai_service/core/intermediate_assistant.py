import logging
import asyncio
import time
import uuid
import re
from typing import List, Dict, Any, Optional, AsyncGenerator

from ai_service.core.simple_assistant import SimpleAssistant
from ai_service.services.ollama_service import OllamaService
from ai_service.core.manifest_intent_router import GREETING_PATTERNS

logger = logging.getLogger(__name__)

class IntermediateAssistant:
    """
    IntermediateAssistant — Stage 3: Low-Latency Conversational Orchestrator
    
    This assistant acts as a fast-path router between simple conversational 
    queries and complex operational/RAG tasks. It prioritizes user experience 
    by delivering real-time streaming and avoiding operational timeouts 
    for general inquiries.
    """

    def __init__(self, ollama: OllamaService, operational: SimpleAssistant):
        self.ollama = ollama
        self.operational = operational
        
        # Broad patterns for fast-path conversational routing
        self.fast_path_patterns = re.compile(
            r"(what can you do|who are you|help|how to use|commands|capabilities|greeting|about you|tell me more)",
            re.IGNORECASE
        )

    def _is_obviously_conversational(self, message: str) -> bool:
        """Determine if a query should bypass the operational pipeline."""
        m = message.lower()
        
        # 1. Check standard greetings
        if GREETING_PATTERNS.search(m):
            return True
            
        # 2. Check help/capabilities and other light inquiries
        if self.fast_path_patterns.search(m):
            return True
            
        # 3. Short message heuristic - with LIMS keyword exclusion
        words = m.split()
        if len(words) <= 2:
            # Operational keywords that should NEVER be routed to conversational fast-path
            lims_keywords = {
                "sample", "batch", "inventory", "stock", "equipment", "machine", 
                "instrument", "sop", "audit", "tat", "qc", "analyte", "result", 
                "test", "calibration", "verific", "status", "count"
            }
            # If any word is a LIMS keyword or matches an ID pattern, it's NOT conversational
            if any(kw in m for kw in lims_keywords):
                return False
            if any(re.search(r'[A-Z]{2,3}-\d+', w.upper()) for w in words):
                return False
                
            return True
        return False

    async def process_query(
        self,
        message: str,
        messages: List[Dict[str, str]],
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        **kwargs
    ) -> Dict[str, Any]:
        """
        Stateless entry point for non-streaming calls.
        
        Priority:
        1. Greetings (SimpleAssistant)
        2. Operational (SimpleAssistant - SQL/RAG)
        3. General Conversation (IntermediateAssistant)
        """
        trace_id = kwargs.get("trace_id") or str(uuid.uuid4())
        start_time = time.time()
        m = message.lower()

        # 0. Fast health checks should never depend on LLM availability.
        if m.strip() in {"ping", "health", "status", "alive"}:
            return {
                "answer": "Imara AI is online. Ask a lab or inventory question when ready.",
                "sources": [],
                "meta": {
                    "route": "health_fast",
                    "latency_ms": int((time.time() - start_time) * 1000),
                    "trace_id": trace_id,
                },
            }

        # 1. Check for simple greetings - priority for SimpleAssistant
        if self.operational.intent_router.is_greeting(m):
            logger.info(f"IntermediateAssistant [{trace_id[:8]}]: Greeting detected, delegating to SimpleAssistant")
            return await asyncio.to_thread(
                self.operational.process_query, message, company_id, use_visuals, model, **kwargs
            )

        # 2. Check for Operational Intent (LIMS keywords/IDs)
        if not self._is_obviously_conversational(message):
            logger.info(f"IntermediateAssistant [{trace_id[:8]}]: Operational query, delegating to SimpleAssistant")
            try:
                result = await asyncio.to_thread(
                    self.operational.process_query, message, company_id, use_visuals, model, **kwargs
                )
                return result
            except Exception as e:
                logger.error(f"IntermediateAssistant delegation failed: {e}")
                return self._get_safe_fallback(trace_id, start_time, error=str(e))

        # 3. General Conversation - Handled by IntermediateAssistant
        logger.info(f"IntermediateAssistant [{trace_id[:8]}]: Routed to Conversational path")
        try:
            system_prompt = self._get_system_prompt(kwargs.get("module_context"))
            answer = await asyncio.to_thread(
                self.ollama.generate, 
                prompt=message, 
                system=system_prompt,
                model=model or "gemma3:1b"
            )
            
            # Check for service errors
            if "AI service error" in answer or "AI service offline" in answer:
                return self._get_safe_fallback(trace_id, start_time, error=answer)

            return {
                "answer": answer,
                "sources": [],
                "meta": {
                    "route": "conversational_fast",
                    "latency_ms": int((time.time() - start_time) * 1000),
                    "trace_id": trace_id
                }
            }
        except Exception as e:
            logger.error(f"IntermediateAssistant conversational path failed: {e}")
            return self._get_safe_fallback(trace_id, start_time, error=str(e))

    async def process_query_stream(
        self,
        message: str,
        messages: List[Dict[str, str]],
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        **kwargs
    ) -> AsyncGenerator[Dict[str, Any], None]:
        """
        Stateless streaming entry point.
        Provides real-time tokens for conversational queries.
        """
        trace_id = kwargs.get("trace_id") or str(uuid.uuid4())
        m = message.lower()

        if m.strip() in {"ping", "health", "status", "alive"}:
            yield {"kind": "token", "token": "Imara AI is online. Ask a lab or inventory question when ready."}
            yield {"kind": "done", "meta": {"route": "health_fast", "trace_id": trace_id}}
            return

        # 1. Check for simple greetings - Priority: SimpleAssistant
        if self.operational.intent_router.is_greeting(m):
            yield {"kind": "status", "content": "Greeting detected", "step": "greeting"}
            result = await asyncio.to_thread(
                self.operational.process_query, message, company_id, use_visuals, model, **kwargs
            )
            yield {"kind": "token", "token": result["answer"]}
            yield {"kind": "done", "meta": result.get("meta", {})}
            return

        # 2. Check for Operational Intent (LIMS keywords/IDs)
        if not self._is_obviously_conversational(message):
            yield {"kind": "status", "content": "Querying operational database...", "step": "operational"}
            try:
                result = await asyncio.to_thread(
                    self.operational.process_query, message, company_id, use_visuals, model, **kwargs
                )
                yield {"kind": "token", "token": result["answer"]}
                yield {"kind": "done", "sources": result.get("sources", []), "meta": result.get("meta", {})}
            except Exception as e:
                logger.error(f"Streaming delegation failed: {e}")
                yield {"kind": "error", "content": "An error occurred while processing your request."}
            return

        # 3. General Conversation - Handled by IntermediateAssistant
        yield {"kind": "status", "content": "Analyzing query...", "step": "routing"}
        yield {"kind": "status", "content": "Ready", "step": "conversational"}
        
        system_prompt = self._get_system_prompt(kwargs.get("module_context"))
        # We use the full messages array for history in the stream
        stream = self.ollama.chat_stream(
            messages=[{"role": "system", "content": system_prompt}] + messages,
            model=model or "gemma3:1b"
        )
        
        async for chunk in stream:
            if "message" in chunk and "content" in chunk["message"]:
                yield {"kind": "token", "token": chunk["message"]["content"]}
            elif "error" in chunk:
                yield {"kind": "error", "content": f"AI unavailable: {chunk['error']}"}
        
        yield {"kind": "done", "meta": {"route": "conversational_stream"}}

    def _get_system_prompt(self, context: Optional[str]) -> str:
        if context == 'lab':
            return (
                "You are the Laboratory AI Assistant for Imara LIMS. "
                "You help with sample status, equipment verification, and lab SOPs. "
                "For general chat, be professional and concise. "
                "If asked what you can do, explain that you can track samples, check equipment health, and query lab metrics."
            )
        return (
            "You are Imara AI, a professional LIMS assistant. "
            "Helpful, concise, and accurate. Use clear formatting."
        )

    def _get_safe_fallback(self, trace_id: str, start_time: float, error: Optional[str] = None) -> Dict[str, Any]:
        return {
            "answer": "I'm having trouble connecting to the analytics engine right now. I can still help with general questions, or you can try your query again in a moment.",
            "sources": [],
            "meta": {
                "route": "safe_fallback",
                "latency_ms": int((time.time() - start_time) * 1000),
                "trace_id": trace_id,
                "error": error
            }
        }
