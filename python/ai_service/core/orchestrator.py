"""
orchestrator.py — IMARA AI Orchestrator

The top-level brain that replaces IntermediateAssistant.
Coordinates four specialized workers:

  1. ManifestWorker     — Pre-approved SQL manifest (fast, deterministic)
  2. DynamicSqlWorker  — Free-hand SQL generation (flexible analytics)
  3. RagWorker         — Knowledge base retrieval (documents / SOPs)
  4. ChatWorker        — General conversational fallback

Routing strategy per query:
  ┌──────────────────────────────────────────────────────┐
  │  Fast-paths (< 1ms):                                 │
  │    health ping → greeting → capabilities             │
  ├──────────────────────────────────────────────────────┤
  │  Analytical path (mode-aware):                       │
  │    ManifestWorker  →  if None → DynamicSqlWorker     │
  │                       if None → RagWorker            │
  │                                 if None → ChatWorker │
  ├──────────────────────────────────────────────────────┤
  │  Mode Governance:                                     │
  │    DynamicSqlWorker disabled in 'support' mode       │
  │    (Customer Portal must not generate free-hand SQL) │
  └──────────────────────────────────────────────────────┘

All behaviour is governed by ModeRegistry.
"""

import logging
import asyncio
import inspect
import re
import time
import uuid
from typing import List, Dict, Any, Optional, AsyncGenerator

from python.ai_service.core import mode_registry
from python.ai_service.core.manifest_intent_router import GREETING_PATTERNS
from python.ai_service.core.request_logger import request_logger
from python.ai_service.workers.manifest_worker import ManifestWorker
from python.ai_service.workers.dynamic_sql_worker import DynamicSqlWorker
from python.ai_service.workers.rag_worker import RagWorker
from python.ai_service.workers.chat_worker import ChatWorker

logger = logging.getLogger(__name__)

# Modes where DynamicSqlWorker is disabled (external-facing portals)
_DYNAMIC_SQL_BLOCKED_MODES = {"support"}

# Operational keywords — queries containing these go to the data pipeline, not chat
_LIMS_KEYWORDS = {
    "sample", "batch", "inventory", "stock", "equipment", "machine", "instrument",
    "sop", "audit", "tat", "qc", "analyte", "result", "test", "calibration",
    "verific", "status", "count", "lab", "laboratory", "client", "customer",
    "submission", "submiss", "rejected", "pending", "approved", "capa", "analytic",
    "complaint", "ticket", "maintenance", "overdue", "trend", "compared",
    "versus", "how many", "show me", "breakdown", "report", "summary",
}

_PORTAL_LAST_SUBMISSION_PATTERN = re.compile(
    r"(last|latest|recent|status|when|where).{0,40}(submi+s+i?on|sample|batch)|"
    r"(submi+s+i?on|sample|batch).{0,40}(last|latest|recent|status|when|where)",
    re.IGNORECASE,
)

# Analytics-indicator phrases that always go to DynamicSqlWorker if manifest misses
_ANALYTICS_PHRASES = re.compile(
    r"(compar(e|ed|ing)|trend(s)?|versus|vs\.?|breakdown|correlation|"
    r"over time|month[- ]on[- ]month|year[- ]on[- ]year|top \d+|"
    r"grouped? by|by client|by analyte|by department|aggregate|average|mean|sum)",
    re.IGNORECASE,
)

_CAPABILITIES_PATTERN = re.compile(
    r"(w+hat\s+can\s+you\s+d[oi]|help|how\s+to\s+use|commands|capabilit(?:y|ies)|features)",
    re.IGNORECASE,
)

_MODE_MENTION_ALIASES = {
    "general": ("general", "all modules", "everything"),
    "support": ("support", "portal", "customer portal"),
    "lab": ("lab", "laboratory", "samples", "sample"),
    "inventory": ("inventory", "stock", "reagent", "reagents", "procurement"),
    "audit": ("audit", "quality", "qc", "capa", "compliance"),
    "crm": ("crm", "client", "customer", "clients", "customers"),
}


class AIOrchestrator:
    """
    IMARA AI Orchestrator — coordinates specialized workers to answer queries.
    Replaces IntermediateAssistant as the top-level routing brain.
    Called directly by the FastAPI chat router.
    """

    def __init__(
        self,
        manifest_worker: ManifestWorker,
        dynamic_sql_worker: DynamicSqlWorker,
        rag_worker: RagWorker,
        chat_worker: ChatWorker,
    ):
        self.manifest = manifest_worker
        self.dynamic_sql = dynamic_sql_worker
        self.rag = rag_worker
        self.chat = chat_worker

    # ─────────────────────────────────────────────────────────────────────────
    # Public API — Non-streaming (JSON)
    # ─────────────────────────────────────────────────────────────────────────

    async def route_and_process(
        self,
        message: str,
        messages: List[Dict[str, str]],
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        **kwargs,
    ) -> Dict[str, Any]:
        trace_id = kwargs.get("trace_id") or str(uuid.uuid4())
        start = time.time()
        mode = kwargs.get("mode")
        module_context = kwargs.get("module_context")
        
        # Context Promotion: If mode is general but module_context exists, use module_context
        if (not mode or mode == "general") and module_context:
            mode = module_context

        m = message.lower().strip()

        logger.info(
            f"AIOrchestrator [{trace_id[:8]}]: route_and_process "
            f"mode='{mode}' query='{m[:60]}'"
        )

        result = self._fast_path(m, messages, mode, trace_id, start)
        if result:
            self._log(trace_id, message, result, company_id, kwargs)
            return result

        result = await asyncio.to_thread(
            self._run_data_pipeline,
            message, company_id, use_visuals, mode, module_context, trace_id, kwargs
        )

        if result is None:
            # Pure conversational fallback
            result = self.chat.run(message, messages, mode=mode, model=model, trace_id=trace_id)

        result["meta"].setdefault("trace_id", trace_id)
        result["meta"].setdefault("mode", mode)
        result["meta"]["latency_ms"] = round((time.time() - start) * 1000)

        self._log(trace_id, message, result, company_id, kwargs)
        return result

    # ─────────────────────────────────────────────────────────────────────────
    # Public API — Streaming (SSE)
    # ─────────────────────────────────────────────────────────────────────────

    async def route_and_stream(
        self,
        message: str,
        messages: List[Dict[str, str]],
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        **kwargs,
    ) -> AsyncGenerator[Dict[str, Any], None]:
        trace_id = kwargs.get("trace_id") or str(uuid.uuid4())
        start = time.time()
        mode = kwargs.get("mode")
        module_context = kwargs.get("module_context")
        
        # Context Promotion: If mode is general but module_context exists, use module_context
        if (not mode or mode == "general") and module_context:
            mode = module_context

        m = message.lower().strip()

        logger.info(
            f"AIOrchestrator [{trace_id[:8]}]: route_and_stream "
            f"mode='{mode}' query='{m[:60]}'"
        )

        # ── Fast-paths ────────────────────────────────────────────────────────
        fast = self._fast_path(m, messages, mode, trace_id, start)
        if fast:
            self._log(trace_id, message, fast, company_id, kwargs)
            yield {"kind": "token", "token": fast["answer"]}
            yield {"kind": "done", "meta": fast["meta"]}
            return

        # ── Data pipeline (run in thread) ─────────────────────────────────────
        result = await asyncio.to_thread(
            self._run_data_pipeline,
            message, company_id, use_visuals, mode, module_context, trace_id, kwargs
        )

        if result is not None:
            result["meta"].setdefault("trace_id", trace_id)
            result["meta"].setdefault("mode", mode)
            result["meta"]["latency_ms"] = round((time.time() - start) * 1000)
            yield {"kind": "token", "token": result["answer"]}
            yield {
                "kind": "done",
                "sources": result.get("sources", []),
                "meta": result["meta"],
            }
            return

        # ── Streaming conversational fallback ──────────────────────────────────
        system_prompt = mode_registry.get_persona(mode)
        stream = self.chat.run_stream(messages, mode=mode, model=model)

        async for chunk in self._wrap_stream(stream):
            if "message" in chunk and "content" in chunk["message"]:
                yield {"kind": "token", "token": chunk["message"]["content"]}
            elif "error" in chunk:
                yield {"kind": "error", "content": f"AI unavailable: {chunk['error']}"}

        yield {
            "kind": "done",
            "meta": {
                "route": "conversational_stream",
                "mode": mode,
                "trace_id": trace_id,
                "latency_ms": round((time.time() - start) * 1000),
            },
        }

    # ─────────────────────────────────────────────────────────────────────────
    # Core Pipeline
    # ─────────────────────────────────────────────────────────────────────────

    def _run_data_pipeline(
        self,
        message: str,
        company_id: int,
        use_visuals: bool,
        mode: Optional[str],
        module_context: Optional[str],
        trace_id: str,
        kwargs: Optional[Dict[str, Any]] = None,
    ) -> Optional[Dict[str, Any]]:
        """
        Tries workers in priority order. Returns the first successful result,
        or None if the query is purely conversational.
        """
        kwargs = kwargs or {}
        m = message.lower()

        if mode == "support":
            snapshot_result = self._answer_from_portal_snapshot(m, kwargs.get("user_data_snapshot"), trace_id)
            if snapshot_result is not None:
                return snapshot_result

            if kwargs.get("crm_customer_id"):
                company_id = int(kwargs["crm_customer_id"])

        # If no LIMS context and no analytics phrases → skip data pipeline
        if not self._is_data_query(m):
            logger.info(f"AIOrchestrator [{trace_id[:8]}]: No data context → conversational")
            return None

        # ── Step 1: Manifest Worker (always first, fastest) ───────────────────
        result = self.manifest.run(
            message=message,
            company_id=company_id,
            use_visuals=use_visuals,
            mode=mode,
            module_context=module_context,
            trace_id=trace_id,
        )
        if result is not None:
            logger.info(f"AIOrchestrator [{trace_id[:8]}]: Resolved by ManifestWorker")
            result["meta"]["orchestration_path"] = ["manifest"]
            return result

        # ── Step 2: Dynamic SQL Worker (if mode allows) ───────────────────────
        if mode not in _DYNAMIC_SQL_BLOCKED_MODES:
            result = self.dynamic_sql.run(
                message=message,
                company_id=company_id,
                use_visuals=use_visuals,
                mode=mode,
                trace_id=trace_id,
            )
            if result is not None:
                logger.info(f"AIOrchestrator [{trace_id[:8]}]: Resolved by DynamicSqlWorker")
                result["meta"]["orchestration_path"] = ["manifest→miss", "dynamic_sql"]
                return result
        else:
            logger.info(
                f"AIOrchestrator [{trace_id[:8]}]: DynamicSqlWorker disabled for mode='{mode}'"
            )

        # ── Step 3: RAG Worker ────────────────────────────────────────────────
        result = self.rag.run(
            message=message,
            company_id=company_id,
            mode=mode,
            trace_id=trace_id,
        )
        if result is not None:
            logger.info(f"AIOrchestrator [{trace_id[:8]}]: Resolved by RagWorker")
            result["meta"]["orchestration_path"] = ["manifest→miss", "dynamic_sql→miss_or_blocked", "rag"]
            return result

        logger.info(f"AIOrchestrator [{trace_id[:8]}]: All data workers missed → conversational")
        return None

    def _answer_from_portal_snapshot(
        self,
        message: str,
        snapshot: Optional[Dict[str, Any]],
        trace_id: str,
    ) -> Optional[Dict[str, Any]]:
        if not snapshot or not _PORTAL_LAST_SUBMISSION_PATTERN.search(message):
            return None

        submissions = (
            snapshot.get("recent_activity", {})
            .get("submissions", [])
        )

        if not submissions:
            answer = "I could not find any recent submissions on your portal account."
        else:
            latest = submissions[0]
            batches = latest.get("batches") or []

            batch_code = None
            batch_status = None
            if batches:
                batch = batches[0]
                batch_code = batch.get("batch_code")
                batch_status = batch.get("status")

            identifier = (
                latest.get("sample_name")
                or latest.get("sample_code")
                or latest.get("product_name")
                or latest.get("sample_reference_no")
                or latest.get("file_reference_no")
                or latest.get("scf_no")
                or batch_code
            )
            status = latest.get("overall_status") or batch_status
            submitted_at = latest.get("submitted_at")

            details = []
            if submitted_at:
                details.append(f"submitted on {submitted_at}")
            if status:
                details.append(f"currently {status}")
            if batch_code:
                batch_detail = f"batch {batch_code}"
                if batch_status and batch_status != status:
                    batch_detail += f" ({batch_status})"
                details.append(batch_detail)

            if identifier and details:
                answer = f"Your latest submission is {identifier}, " + ", ".join(details) + "."
            elif identifier:
                answer = f"Your latest submission is {identifier}."
            elif details:
                answer = "Your latest submission is " + ", ".join(details) + "."
            else:
                answer = "I found a recent submission on your portal account, but its details are not available yet."

        logger.info(f"AIOrchestrator [{trace_id[:8]}]: Resolved by portal snapshot")
        return {
            "answer": answer,
            "sources": [],
            "meta": {
                "route": "portal_snapshot:latest_submission",
                "routing_tier": "portal_snapshot",
                "confidence": 1.0,
                "orchestration_path": ["portal_snapshot"],
            },
        }

    # ─────────────────────────────────────────────────────────────────────────
    # Fast-paths (< 1ms)
    # ─────────────────────────────────────────────────────────────────────────

    def _fast_path(
        self,
        m: str,
        messages: List[Dict[str, str]],
        mode: Optional[str],
        trace_id: str,
        start: float,
    ) -> Optional[Dict[str, Any]]:
        """Handles health, capabilities, and greeting fast-paths. Returns None if not applicable."""

        # Health ping
        if m in {"ping", "health", "status", "alive"}:
            return self._make_fast_result(
                "IMARA AI is online and connected to the database. Ask a question when ready.",
                "health_fast", mode, trace_id, start
            )

        # Capabilities
        if _CAPABILITIES_PATTERN.search(m):
            target_mode = self._capabilities_mode(m, mode)
            return self._make_fast_result(
                mode_registry.get_capabilities(target_mode),
                "capabilities_fast", target_mode, trace_id, start
            )

        # Greeting (only on first message)
        if len(messages) <= 1 and GREETING_PATTERNS.search(m):
            return self._make_fast_result(
                mode_registry.get_greeting(mode),
                "greeting_fast", mode, trace_id, start
            )

        return None

    def _make_fast_result(
        self, answer: str, route: str, mode: Optional[str], trace_id: str, start: float
    ) -> Dict[str, Any]:
        return {
            "answer": answer,
            "sources": [],
            "meta": {
                "route": route,
                "routing_tier": "fast_path",
                "trace_id": trace_id,
                "mode": mode,
                "latency_ms": round((time.time() - start) * 1000),
                "confidence": 1.0,
                "orchestration_path": [route],
            },
        }

    # ─────────────────────────────────────────────────────────────────────────
    # Helpers
    # ─────────────────────────────────────────────────────────────────────────

    def _is_data_query(self, m: str) -> bool:
        """True if the query has any LIMS context or analytics phrasing."""
        if any(kw in m for kw in _LIMS_KEYWORDS):
            return True
        if _ANALYTICS_PHRASES.search(m):
            return True
        return False

    def _capabilities_mode(self, m: str, current_mode: Optional[str]) -> Optional[str]:
        """
        Return the mode the user is asking about in a capabilities query.
        Example: active general mode + "what can you do in lab" should explain lab mode,
        not general mode. Aliases are intentionally small and domain-specific.
        """
        for mode_key, aliases in _MODE_MENTION_ALIASES.items():
            for alias in aliases:
                pattern = r"\b" + re.escape(alias).replace(r"\ ", r"\s+") + r"\b"
                if re.search(pattern, m, re.IGNORECASE):
                    return mode_key
        return current_mode

    async def _wrap_stream(self, stream):
        """Yield chunks from either an async stream or a synchronous generator."""
        if inspect.isasyncgen(stream):
            async for chunk in stream:
                yield chunk
            return

        if hasattr(stream, "__aiter__"):
            async for chunk in stream:
                yield chunk
            return

        for chunk in stream:
            yield chunk
            await asyncio.sleep(0)

    async def _wrap_sync_stream(self, sync_gen):
        """Backward-compatible wrapper for callers using the old helper name."""
        async for chunk in self._wrap_stream(sync_gen):
            yield chunk

    def _log(
        self,
        trace_id: str,
        message: str,
        result: Dict[str, Any],
        company_id: int,
        kwargs: dict,
    ):
        meta = result.get("meta", {})
        request_logger.log(
            trace_id=trace_id,
            query=message,
            mode=meta.get("route", "unknown"),
            route_name=meta.get("route", "unknown"),
            routing_tier=meta.get("routing_tier", "unknown"),
            latency_ms=meta.get("latency_ms", 0),
            confidence=meta.get("confidence", 0.0),
            success="error" not in meta,
            error_message=meta.get("error"),
            company_id=company_id,
            user_id=kwargs.get("user_id"),
            session_id=kwargs.get("session_id"),
            response_preview=result.get("answer", "")[:500],
            source_count=len(result.get("sources", [])),
            cache_hit=False,
        )
