"""
SimpleAssistant — Stage 2: Production-Hardened Operational AI

Routing flow:
    1. Greeting detection → conversational (skip SQL + RAG entirely)
    2. Exact identifier extraction (QueryClassifier) → domain hints
    3. Keyword/rule-based manifest routing (ManifestIntentRouter)
    4. LLM classifier fallback (only when rules miss)
    5. RAG knowledge base search
    6. Conversational fallback

Every request is logged to ai.ai_request_logs via RequestLogger.
"""

import logging
import time
import json
import uuid
import pandas as pd
from typing import List, Dict, Any, Optional

from python.ai_service.services.ollama_service import OllamaService
from python.ai_service.services.visualization_service import VisualizationService
from python.ai_service.services.live_data_service import LiveDataService
from python.ai_service.services.retrieval_service import RetrievalService
from python.ai_service.core.manifest_intent_router import ManifestIntentRouter
from python.ai_service.core.query_classifier import QueryClassifier
from python.ai_service.core.query_filter_extractor import QueryFilterExtractor
from python.ai_service.core.request_logger import request_logger

logger = logging.getLogger(__name__)

# ── Error messages (user-facing, controlled) ─────────────────────────────
_ERR_NO_SQL_MATCH = (
    "I couldn't find a matching operational query for that request. "
    "Try rephrasing your question or ask about a specific metric like "
    "sample counts, inventory levels, or equipment status."
)
_ERR_NO_RAG_SOURCES = (
    "I couldn't find relevant knowledge-base content for that question. "
    "The knowledge base may not cover that topic yet."
)
_ERR_DB_UNAVAILABLE = (
    "The operational database is temporarily unavailable. "
    "Please try again shortly."
)
_ERR_AI_UNAVAILABLE = (
    "The AI service is temporarily busy. "
    "Please try again in a moment."
)
_ERR_ZERO_ROWS = (
    "No matching records were found for the filters used. "
    "This may indicate the data hasn't been synced yet, "
    "or no records match the current criteria."
)

# Timeouts for operations (seconds)
_LLM_ROUTE_TIMEOUT = 3.0
_SQL_TIMEOUT = 5.0
_RAG_TIMEOUT = 7.0

_ERR_TIMEOUT = (
    "That request is taking longer than expected. "
    "Try narrowing your query or asking for a specific subset of data."
)


class SimpleAssistant:
    def __init__(
        self,
        ollama: OllamaService,
        live_data: LiveDataService,
        retrieval: RetrievalService,
        visualizer: VisualizationService,
    ):
        self.ollama = ollama
        self.live_data = live_data
        self.retrieval = retrieval
        self.visualizer = visualizer
        self.intent_router = ManifestIntentRouter()
        self.query_classifier = QueryClassifier()
        self.filter_extractor = QueryFilterExtractor()

    def process_query(
        self,
        message: str,
        company_id: int = 1,
        use_visuals: bool = True,
        model: Optional[str] = None,
        *,
        trace_id: Optional[str] = None,
        user_id: Optional[int] = None,
        session_id: Optional[str] = None,
    ) -> Dict[str, Any]:
        """
        Main entry point for processing an operational query.
        Tiered adaptive flow: Greeting → Rules → LLM → RAG → Chat.
        """
        start_time = time.time()
        trace_id = trace_id or str(uuid.uuid4())
        routing_tier = "fallback"
        route_name = "conversational"
        error_message = None
        success = True
        source_count = 0
        cache_hit = False

        logger.info(
            f"SimpleAssistant [{trace_id[:8]}]: "
            f"query='{message[:60]}...' flow_started"
        )

        result = {
            "answer": "",
            "sources": [],
            "meta": {
                "route": "conversational",
                "latency_ms": 0,
                "trace_id": trace_id,
                "routing_tier": "fallback",
            },
        }

        try:
            # ── Step 0: Greeting short-circuit ────────────────────────
            if self.intent_router.is_greeting(message):
                logger.info(f"SimpleAssistant [{trace_id[:8]}]: Greeting detected")
                routing_tier = "greeting"
                route_name = "conversational"
                result["answer"] = self._generate_conversational(message)
                result["meta"]["route"] = "conversational"
                result["meta"]["routing_tier"] = routing_tier
                result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
                return result

            # ── Step 1: Feature Extraction (Classifier + Filters) ─────
            classification = self.query_classifier.classify(message)
            domain_hints = classification.get("domains", ["all"])
            extracted_filters = self.filter_extractor.extract(message)

            # ── Step 2: Rule-based manifest routing ───────────────────
            sql_intent, tier = self.intent_router.match(message)

            if sql_intent:
                routing_tier = tier  # 'keyword' or 'keyword_loose'
                route_name = sql_intent
                return self._execute_sql_route(
                    message, sql_intent, company_id, use_visuals,
                    start_time, trace_id, routing_tier, result,
                    filters=extracted_filters
                )

            # ── Step 3: LLM classifier fallback ──────────────────────
            llm_intent = self._llm_classify_intent(message)
            if llm_intent:
                routing_tier = "llm"
                route_name = llm_intent
                return self._execute_sql_route(
                    message, llm_intent, company_id, use_visuals,
                    start_time, trace_id, routing_tier, result,
                    filters=extracted_filters
                )

            # ── Step 4: RAG knowledge base search ─────────────────────
            try:
                import concurrent.futures
                with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                    future = executor.submit(
                        self.retrieval.search,
                        query=message,
                        limit=5,
                        metadata_filters=(
                            {"company_id": company_id} if company_id else None
                        ),
                    )
                    chunks = future.result(timeout=_RAG_TIMEOUT)
            except concurrent.futures.TimeoutError:
                logger.warning(f"SimpleAssistant [{trace_id[:8]}]: RAG search timed out")
                chunks = []
                result["answer"] = _ERR_TIMEOUT
                # Fall through to conversational or error depending on severity
            except Exception as rag_exc:
                logger.error(f"SimpleAssistant [{trace_id[:8]}]: RAG search failed: {rag_exc}")
                chunks = []

            if chunks:
                logger.info(
                    f"SimpleAssistant [{trace_id[:8]}]: "
                    f"RAG matched {len(chunks)} chunks"
                )
                routing_tier = "rag"
                route_name = "rag"
                source_count = len(chunks)
                result["sources"] = self._format_sources(chunks)
                result["answer"] = self._synthesize_rag_answer(message, chunks)

                if not result["answer"]:
                    result["answer"] = _ERR_NO_RAG_SOURCES
                    success = False
                    error_message = "RAG synthesis returned empty"

                result["meta"]["route"] = "rag"
                result["meta"]["routing_tier"] = routing_tier
                result["meta"]["source_count"] = source_count
                result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
                return result
            else:
                logger.info(
                    f"SimpleAssistant [{trace_id[:8]}]: "
                    "No RAG sources, falling back to conversational"
                )

            # ── Step 5: Conversational fallback ───────────────────────
            routing_tier = "fallback"
            route_name = "conversational"
            result["answer"] = self._generate_conversational(message)
            result["meta"]["route"] = "conversational"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)

        except Exception as e:
            logger.error(f"SimpleAssistant [{trace_id[:8]}] unhandled error: {e}")
            success = False
            error_message = str(e)
            routing_tier = "error"
            route_name = "error"
            result["answer"] = _ERR_AI_UNAVAILABLE
            result["meta"]["route"] = "error"
            result["meta"]["error_type"] = type(e).__name__
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)

        finally:
            # ── Always log the request ────────────────────────────────
            latency = self._elapsed_ms(start_time)
            result["meta"]["latency_ms"] = latency

            request_logger.log(
                trace_id=trace_id,
                query=message,
                mode=result["meta"].get("route", "unknown"),
                route_name=route_name,
                routing_tier=routing_tier,
                latency_ms=latency,
                success=success,
                error_message=error_message,
                company_id=company_id,
                user_id=user_id,
                session_id=session_id,
                response_preview=result.get("answer", "")[:500],
                source_count=source_count,
                cache_hit=cache_hit,
            )

        return result

    # ── SQL execution helper ──────────────────────────────────────────────

    def _execute_sql_route(
        self,
        message: str,
        intent: str,
        company_id: int,
        use_visuals: bool,
        start_time: float,
        trace_id: str,
        routing_tier: str,
        result: Dict[str, Any],
        filters: Optional[Dict[str, Any]] = None
    ) -> Dict[str, Any]:
        """Execute a matched SQL intent and build the response."""
        logger.info(
            f"SimpleAssistant [{trace_id[:8]}]: "
            f"Executing SQL intent '{intent}' (tier={routing_tier})"
        )

        params = {"company_id": company_id}
        if filters:
            params.update(filters)

        try:
            import concurrent.futures
            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                future = executor.submit(
                    self.live_data.execute_step,
                    intent, params=params
                )
                sql_res = future.result(timeout=_SQL_TIMEOUT)
        except concurrent.futures.TimeoutError:
            logger.warning(f"SimpleAssistant [{trace_id[:8]}]: SQL execution timed out")
            result["answer"] = _ERR_TIMEOUT
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "timeout"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result
        except Exception as db_exc:
            logger.error(
                f"SimpleAssistant [{trace_id[:8]}]: "
                f"DB execution failed for '{intent}': {db_exc}"
            )
            result["answer"] = _ERR_DB_UNAVAILABLE
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "db_connection"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result

        if not sql_res.get("success", False):
            error = sql_res.get("error", "")
            # Circuit breaker or template not found
            if "Circuit" in str(error):
                result["answer"] = _ERR_DB_UNAVAILABLE
            elif "not found in manifest" in str(error):
                result["answer"] = _ERR_NO_SQL_MATCH
            else:
                result["answer"] = sql_res.get(
                    "summary", _ERR_DB_UNAVAILABLE
                )
            result["meta"]["route"] = f"sql:{intent}"
            result["meta"]["routing_tier"] = routing_tier
            result["meta"]["error_type"] = "sql_execution"
            result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
            return result

        answer = sql_res.get("summary", _ERR_ZERO_ROWS)

        # Deterministic visualizations
        if use_visuals:
            template = self.live_data._resolve_template(intent)
            if template and "visualize" in template:
                df = pd.DataFrame(sql_res.get("data", []))
                chart_block = self.visualizer.generate_chart_block(
                    df, template["visualize"]
                )
                if chart_block:
                    answer = chart_block + "\n\n" + answer

        result["answer"] = answer
        result["meta"]["route"] = f"sql:{intent}"
        result["meta"]["routing_tier"] = routing_tier
        result["meta"]["latency_ms"] = self._elapsed_ms(start_time)
        result["meta"]["sql_value"] = sql_res.get("value")
        result["meta"]["sql_execution_ms"] = sql_res.get("execution_latency_ms")
        return result

    # ── LLM-based intent classification (Tier 2 fallback) ─────────────────

    def _llm_classify_intent(self, message: str) -> Optional[str]:
        """
        Use the LLM to classify a query into a manifest intent.
        Has a timeout to avoid blocking on slow LLM responses.
        """
        intents = []
        for domain in self.live_data.templates.values():
            intents.extend(domain.keys())

        prompt = f"""Map the user query to the most appropriate data report name.
Available reports: {", ".join(intents)}

Query: "{message}"

If no report is a good match, return 'none'.
Otherwise, return ONLY the report name, nothing else."""

        try:
            import concurrent.futures

            with concurrent.futures.ThreadPoolExecutor(max_workers=1) as executor:
                future = executor.submit(
                    self.ollama.generate, prompt=prompt
                )
                mapped = future.result(timeout=_LLM_ROUTE_TIMEOUT)
                mapped = mapped.strip().lower().replace("'", "").replace('"', "")

                if mapped in intents:
                    logger.info(
                        f"SimpleAssistant: LLM classified intent '{mapped}'"
                    )
                    return mapped

                logger.info(
                    f"SimpleAssistant: LLM returned '{mapped}' — no manifest match"
                )
                return None

        except concurrent.futures.TimeoutError:
            logger.warning(
                "SimpleAssistant: LLM routing timed out after "
                f"{_LLM_ROUTE_TIMEOUT}s, skipping to RAG/chat"
            )
            return None
        except Exception as e:
            logger.warning(f"SimpleAssistant: LLM routing failed: {e}")
            return None

    # ── RAG synthesis ─────────────────────────────────────────────────────

    def _synthesize_rag_answer(
        self, message: str, chunks: List[Dict[str, Any]]
    ) -> str:
        """Build an answer from retrieved knowledge base chunks."""
        context = "\n\n".join(
            [
                f"Source: {c.get('collection_name')}\nContent: {c['content']}"
                for c in chunks
            ]
        )
        prompt = f"""You are a lab assistant. Use the following context to answer the user's question concisely.
Context:
{context}

Question: "{message}"

Rules:
1. Answer in 2-3 sentences max.
2. Be direct and professional.
3. If not in context, say: "I couldn't find a matching knowledge document for that request. Please contact the lab supervisor."
"""
        try:
            answer = self.ollama.generate(prompt=prompt)
            # Ensure consistent appending of sources if needed
            if answer and not answer.startswith("I couldn't find"):
                answer += "\n\n---\n**Sources:**\n"
                seen_sources = set()
                for c in chunks:
                    source_key = f"{c.get('collection_name')} ({c.get('entity_type')})"
                    if source_key not in seen_sources:
                        answer += f"- {source_key}\n"
                        seen_sources.add(source_key)
            return answer
        except Exception as e:
            logger.error(f"SimpleAssistant: RAG synthesis failed: {e}")
            return _ERR_AI_UNAVAILABLE

    # ── Conversational generation ─────────────────────────────────────────

    def _generate_conversational(self, message: str) -> str:
        """Generate a conversational response (greetings, general chat)."""
        try:
            return self.ollama.generate(
                prompt=message,
                system=(
                    "You are Imarachat AI, a professional LIMS assistant. "
                    "Be concise and helpful. You have access to lab data and SOPs. "
                    "If the user greets you, respond warmly and briefly."
                ),
            )
        except Exception as e:
            logger.error(f"SimpleAssistant: Conversational generation failed: {e}")
            return _ERR_AI_UNAVAILABLE

    # ── Source formatting ─────────────────────────────────────────────────

    def _format_sources(
        self, chunks: List[Dict[str, Any]]
    ) -> List[Dict[str, Any]]:
        sources = []
        for c in chunks:
            sources.append(
                {
                    "label": f"{c.get('collection_name')} · {c.get('entity_type')}",
                    "entity_id": c.get("entity_id"),
                    "collection_name": c.get("collection_name"),
                    "entity_type": c.get("entity_type"),
                }
            )
        return sources

    # ── Utilities ─────────────────────────────────────────────────────────

    @staticmethod
    def _elapsed_ms(start: float) -> int:
        return int((time.time() - start) * 1000)
